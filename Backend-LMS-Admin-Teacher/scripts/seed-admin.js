require('dotenv').config();
const bcrypt = require('bcryptjs');
const { sql, pool } = require('../src/config/database');

async function main() {
  const npk = process.env.SEED_NPK;
  const name = process.env.SEED_NAME;
  const email = process.env.SEED_EMAIL;
  const password = process.env.SEED_PASSWORD;
  const role = process.env.SEED_ROLE || 'admin';
  if (!npk || !name || !email || !password || !['admin', 'teacher'].includes(role)) throw new Error('Set SEED_NPK, SEED_NAME, SEED_EMAIL, SEED_PASSWORD, and optional SEED_ROLE first.');
  await pool.connect();
  const hash = await bcrypt.hash(password, 12);
  await pool.request().input('npk', sql.VarChar(50), npk).input('name', sql.NVarChar(150), name)
    .input('email', sql.NVarChar(254), email).input('hash', sql.VarChar(255), hash).input('role', sql.VarChar(20), role)
    .query(`IF EXISTS (SELECT 1 FROM dbo.employee_accounts WHERE employee_npk=@npk)
      UPDATE dbo.employee_accounts SET fullname=@name,email=@email,password_hash=@hash,role=@role,status='active' WHERE employee_npk=@npk
      ELSE INSERT INTO dbo.employee_accounts(employee_npk,fullname,email,password_hash,role) VALUES(@npk,@name,@email,@hash,@role)`);
  await pool.close();
  console.log(`Account provisioned for ${npk} (${role}).`);
}

main().catch(async error => { console.error(error.message); try { await pool.close(); } catch {} process.exit(1); });
