const mysql = require('mysql2/promise');
const env = require('../config/env');

let pool;

function getQuizStore() {
  // Support passwordless local MySQL development; config/env.js requires a
  // strong Student database password for production deployments.
  pool ||= mysql.createPool({
    host: env.STUDENT_DB_HOST,
    port: env.STUDENT_DB_PORT,
    database: env.STUDENT_DB_NAME,
    user: env.STUDENT_DB_USER,
    password: env.STUDENT_DB_PASSWORD,
    waitForConnections: true,
    connectionLimit: 3,
    queueLimit: 20,
    charset: 'utf8mb4',
    decimalNumbers: true,
    timezone: 'Z'
  });
  return pool;
}

async function closeQuizStore() {
  if (!pool) return;
  const activePool = pool;
  pool = undefined;
  await activePool.end();
}

module.exports = { getQuizStore, closeQuizStore };
