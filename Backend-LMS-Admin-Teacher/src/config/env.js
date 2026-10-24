const { z } = require('zod');

const schema = z.object({
  NODE_ENV: z.enum(['development', 'test', 'production']).default('development'),
  PORT: z.coerce.number().int().min(1).max(65535).default(3001),
  FRONTEND_ORIGIN: z.string().url(),
  JWT_SECRET: z.string().min(32),
  JWT_ISSUER: z.string().min(1),
  JWT_AUDIENCE: z.string().min(1),
  JWT_TTL: z.string().default('15m'),
  SSO_COOKIE_NAME: z.string().regex(/^[A-Za-z0-9_-]{3,64}$/).default('dls_sso'),
  SSO_COOKIE_DOMAIN: z.string().default(''),
  STUDENT_APP_URL: z.string().url().default('http://127.0.0.1:8082'),
  STUDENT_AUTH_ENABLED: z.enum(['true', 'false']).default('false'),
  DB_SERVER: z.string().min(1),
  DB_PORT: z.coerce.number().int().min(1).max(65535).default(1433),
  DB_NAME: z.string().min(1),
  DB_USER: z.string().min(1),
  DB_PASSWORD: z.string(),
  DB_ENCRYPT: z.enum(['true', 'false']).default('false'),
  DB_TRUST_SERVER_CERTIFICATE: z.enum(['true', 'false']).default('false'),
  STUDENT_SYNC_ENABLED: z.enum(['true', 'false']).default('false'),
  STUDENT_DB_HOST: z.string().default('127.0.0.1'),
  STUDENT_DB_PORT: z.coerce.number().int().min(1).max(65535).default(3306),
  STUDENT_DB_NAME: z.string().default('dls_student'),
  STUDENT_DB_USER: z.string().default('dls_sync_app'),
  STUDENT_DB_PASSWORD: z.string().default(''),
  STUDENT_PUBLIC_API_URL: z.string().url().default('http://127.0.0.1:3001/api/v1'),
  SMTP_HOST: z.string().default(''),
  SMTP_PORT: z.coerce.number().int().min(1).max(65535).default(465),
  SMTP_SECURE: z.enum(['true', 'false']).default('true'),
  SMTP_USER: z.string().default(''),
  SMTP_PASSWORD: z.string().default(''),
  SMTP_FROM: z.string().email().default('lms@example.invalid'),
  REVIEW_PORTAL_URL: z.string().url().default('http://127.0.0.1:5173'),
  MATERIAL_UPLOAD_DIR: z.string().default(''),
  MATERIAL_MAX_UPLOAD_BYTES: z.coerce.number().int().min(1024).max(536870912).default(262144000),
  STUDENT_SYNC_INTERVAL_MS: z.coerce.number().int().min(15000).default(60000)
});

const parsed = schema.safeParse(process.env);
if (!parsed.success) {
  console.error('Invalid environment configuration:', parsed.error.issues.map(({ path }) => path.join('.')).join(', '));
  process.exit(1);
}

if (parsed.data.NODE_ENV === 'production') {
  const productionErrors = [];
  if (!parsed.data.DB_PASSWORD || parsed.data.DB_PASSWORD.length < 20) productionErrors.push('DB_PASSWORD (minimal 20 karakter)');
  if (parsed.data.DB_ENCRYPT !== 'true') productionErrors.push('DB_ENCRYPT=true');
  if (parsed.data.DB_TRUST_SERVER_CERTIFICATE !== 'false') productionErrors.push('DB_TRUST_SERVER_CERTIFICATE=false');
  if (new URL(parsed.data.FRONTEND_ORIGIN).protocol !== 'https:') productionErrors.push('FRONTEND_ORIGIN (HTTPS)');
  if (new URL(parsed.data.REVIEW_PORTAL_URL).protocol !== 'https:') productionErrors.push('REVIEW_PORTAL_URL (HTTPS)');
  if (new URL(parsed.data.STUDENT_APP_URL).protocol !== 'https:') productionErrors.push('STUDENT_APP_URL (HTTPS)');
  if (parsed.data.STUDENT_AUTH_ENABLED === 'true' && parsed.data.STUDENT_DB_PASSWORD.length < 20) {
    productionErrors.push('STUDENT_DB_PASSWORD (minimal 20 karakter saat student auth aktif)');
  }
  if (parsed.data.SSO_COOKIE_DOMAIN && !/^\.?[A-Za-z0-9.-]+$/.test(parsed.data.SSO_COOKIE_DOMAIN)) {
    productionErrors.push('SSO_COOKIE_DOMAIN (hostname yang valid)');
  }
  if (parsed.data.STUDENT_SYNC_ENABLED === 'true' && parsed.data.STUDENT_DB_PASSWORD.length < 20) {
    productionErrors.push('STUDENT_DB_PASSWORD (minimal 20 karakter saat sync aktif)');
  }
  if (productionErrors.length) {
    console.error(`Production configuration is insecure or incomplete: ${productionErrors.join(', ')}`);
    process.exit(1);
  }
}

module.exports = parsed.data;
