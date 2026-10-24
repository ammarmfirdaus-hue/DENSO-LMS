const { z } = require('zod');

const reviewerSchema = z.object({
  npk: z.string().trim().min(1).max(50),
  fullname: z.string().trim().min(2).max(150),
  email: z.string().trim().email().max(254).transform(value => value.toLowerCase()),
  password: z.string().min(12, 'Kata sandi minimal 12 karakter.').max(200),
  department: z.string().trim().max(100).optional().default(''),
  position: z.string().trim().max(100).optional().default(''),
  phoneNumber: z.string().trim().max(30).optional().default('')
});

const reviewerUpdateSchema = z.object({
  fullname: z.string().trim().min(2).max(150),
  email: z.string().trim().email().max(254).transform(value => value.toLowerCase()),
  department: z.string().trim().max(100).optional().default(''),
  position: z.string().trim().max(100).optional().default(''),
  phoneNumber: z.string().trim().max(30).optional().default(''),
  status: z.enum(['active', 'inactive']).default('active'),
  password: z.string().min(12, 'Kata sandi baru minimal 12 karakter.').max(200).optional().or(z.literal(''))
});

module.exports = { reviewerSchema, reviewerUpdateSchema };
