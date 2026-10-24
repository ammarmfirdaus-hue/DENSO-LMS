#!/usr/bin/env node
'use strict';

// Versioned migrations for the SQL Server staff database and MySQL student database.
// Run from the repository root: node database/migrate.js up|status [all|admin|student]
const fs = require('node:fs');
const path = require('node:path');
const { createRequire } = require('node:module');
const root = __dirname;
const backend = path.join(root, '..', 'Backend-LMS-Admin-Teacher');
require(path.join(backend, 'node_modules', 'dotenv')).config({ path: path.join(backend, '.env') });
const localRequire = createRequire(path.join(backend, 'package.json'));

const command = process.argv[2] || 'status';
const targetArg = process.argv[3] || 'all';
if (!['up', 'status'].includes(command) || !['all', 'admin', 'student'].includes(targetArg)) {
  console.error('Usage: node database/migrate.js <up|status> [all|admin|student]');
  process.exit(2);
}

function migrations(engine) {
  const folder = path.join(root, 'migrations', engine);
  return fs.readdirSync(folder).filter(name => /^\d{3}_[a-z0-9_-]+\.sql$/i.test(name)).sort()
    .map(name => ({ name, sql: fs.readFileSync(path.join(folder, name), 'utf8') }));
}

function sqlServerBatches(source) {
  return source.split(/^\s*GO\s*(?:--.*)?$/im).map(part => part.trim()).filter(Boolean);
}

async function runAdmin() {
  const sql = localRequire('mssql');
  const env = require(path.join(backend, 'src', 'config', 'env'));
  const pool = new sql.ConnectionPool({
    server: env.DB_SERVER,
    port: env.DB_PORT,
    database: env.DB_NAME,
    user: process.env.MIGRATION_DB_USER || env.DB_USER,
    password: process.env.MIGRATION_DB_PASSWORD ?? env.DB_PASSWORD,
    options: {
      encrypt: env.DB_ENCRYPT === 'true',
      trustServerCertificate: env.DB_TRUST_SERVER_CERTIFICATE === 'true',
      enableArithAbort: true
    },
    connectionTimeout: 10000,
    requestTimeout: 15000
  });
  try {
    await pool.connect();
    await pool.request().query(`IF OBJECT_ID(N'dbo.schema_migrations',N'U') IS NULL
      CREATE TABLE dbo.schema_migrations (migration_name NVARCHAR(255) NOT NULL PRIMARY KEY, applied_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME());`);
    const applied = new Set((await pool.request().query('SELECT migration_name FROM dbo.schema_migrations')).recordset.map(row => row.migration_name));
    const files = migrations('sqlserver');
    if (command === 'status') {
      for (const file of files) console.log(`${applied.has(file.name) ? 'DONE' : 'PENDING'}  admin  ${file.name}`);
      return;
    }
    for (const file of files) {
      if (applied.has(file.name)) continue;
      const tx = new sql.Transaction(pool);
      await tx.begin();
      try {
        for (const batch of sqlServerBatches(file.sql)) await tx.request().batch(batch);
        await tx.request().input('name', sql.NVarChar(255), file.name)
          .query('INSERT INTO dbo.schema_migrations(migration_name) VALUES(@name)');
        await tx.commit();
        console.log(`APPLIED  admin  ${file.name}`);
      } catch (error) {
        try { await tx.rollback(); } catch { /* Preserve the SQL error that caused the rollback. */ }
        throw new Error(`admin migration ${file.name} failed: ${error.message}`, { cause: error });
      }
    }
  } finally { await pool.close(); }
}

async function runStudent() {
  const mysql = localRequire('mysql2/promise');
  const connection = await mysql.createConnection({
    host: process.env.STUDENT_DB_HOST || '127.0.0.1',
    port: Number(process.env.STUDENT_DB_PORT || 3306),
    database: process.env.STUDENT_DB_NAME || 'dls_student',
    user: process.env.MIGRATION_STUDENT_DB_USER || process.env.STUDENT_DB_USER,
    password: process.env.MIGRATION_STUDENT_DB_PASSWORD ?? process.env.STUDENT_DB_PASSWORD,
    multipleStatements: true,
    timezone: 'Z'
  });
  try {
    await connection.query(`CREATE TABLE IF NOT EXISTS schema_migrations (
      migration_name VARCHAR(255) NOT NULL PRIMARY KEY,
      applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB`);
    const [rows] = await connection.query('SELECT migration_name FROM schema_migrations');
    const applied = new Set(rows.map(row => row.migration_name));
    const files = migrations('mysql');
    if (command === 'status') {
      for (const file of files) console.log(`${applied.has(file.name) ? 'DONE' : 'PENDING'}  student  ${file.name}`);
      return;
    }
    for (const file of files) {
      if (applied.has(file.name)) continue;
      try {
        await connection.query(file.sql);
        await connection.query('INSERT INTO schema_migrations(migration_name) VALUES(?)', [file.name]);
        console.log(`APPLIED  student  ${file.name}`);
      } catch (error) { throw new Error(`student migration ${file.name} failed: ${error.message}`, { cause: error }); }
    }
  } finally { await connection.end(); }
}

(async () => {
  if (targetArg === 'all' || targetArg === 'admin') await runAdmin();
  if (targetArg === 'all' || targetArg === 'student') await runStudent();
})().catch(error => { console.error(error.message); process.exitCode = 1; });
