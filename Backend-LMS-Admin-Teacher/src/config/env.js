const { z } = require('zod');

const schema = z.object({
  NODE_ENV: z.enum(['development', 'test', 'production']).default('development'),
  PORT: z.coerce.number().int().min(1).max(65535).default(3001),
  FRONTEND_ORIGIN: z.string().url(),
  JWT_SECRET: z.string().min(32),
  JWT_ISSUER: z.string().min(1),
  JWT_AUDIENCE: z.string().min(1),
  JWT_TTL: z.string().default('15m'),
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
  STUDENT_SYNC_INTERVAL_MS: z.coerce.number().int().min(15000).default(60000)
});

const parsed = schema.safeParse(process.env);
if (!parsed.success) {
  console.error('Invalid environment configuration:', parsed.error.issues.map(({ path }) => path.join('.')).join(', '));
  process.exit(1);
}

module.exports = parsed.data;
