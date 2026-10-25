const { sql, pool } = require('../config/database');
const env = require('../config/env');

const API_BASE_KEY = 'admin_api_base_url';
let apiBaseUrl = null;

function normalizeApiBase(value) {
  const parsed = new URL(value);
  if (!['http:', 'https:'].includes(parsed.protocol) || parsed.username || parsed.password || parsed.search || parsed.hash) {
    throw new Error('URL API harus berupa alamat HTTP(S) tanpa kredensial, query, atau hash.');
  }
  if (env.NODE_ENV === 'production' && parsed.protocol !== 'https:') throw new Error('URL API harus memakai HTTPS di production.');
  const normalized = parsed.toString().replace(/\/+$/, '');
  return normalized.endsWith('/api/v1') ? normalized : `${normalized}/api/v1`;
}

async function loadSystemSettings() {
  const result = await pool.request().input('key', sql.NVarChar(100), API_BASE_KEY)
    .query('SELECT setting_value FROM dbo.lms_system_settings WHERE setting_key=@key');
  if (result.recordset[0]?.setting_value) apiBaseUrl = normalizeApiBase(result.recordset[0].setting_value);
  return { apiBaseUrl };
}

async function saveApiBaseUrl(value, updatedBy) {
  const normalized = normalizeApiBase(value);
  await pool.request().input('key', sql.NVarChar(100), API_BASE_KEY)
    .input('value', sql.NVarChar(2000), normalized).input('by', sql.VarChar(50), updatedBy)
    .query(`MERGE dbo.lms_system_settings WITH (HOLDLOCK) AS target
      USING (SELECT @key AS setting_key) AS source ON target.setting_key=source.setting_key
      WHEN MATCHED THEN UPDATE SET setting_value=@value,updated_by=@by,updated_at=SYSUTCDATETIME()
      WHEN NOT MATCHED THEN INSERT(setting_key,setting_value,updated_by) VALUES(@key,@value,@by);`);
  apiBaseUrl = normalized;
  return { apiBaseUrl };
}

function getApiBaseUrl() { return apiBaseUrl; }

module.exports = { loadSystemSettings, saveApiBaseUrl, getApiBaseUrl, normalizeApiBase };
