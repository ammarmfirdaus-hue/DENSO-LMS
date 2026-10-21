const express = require('express');
const { z } = require('zod');
const rateLimit = require('express-rate-limit');
const { login } = require('../services/auth');
const { authenticate } = require('../middleware/auth');

const router = express.Router();
const loginLimiter = rateLimit({ windowMs: 15 * 60 * 1000, limit: 8, standardHeaders: 'draft-8', legacyHeaders: false });
const loginSchema = z.object({ npk: z.string().trim().min(1).max(50), password: z.string().min(1).max(200) });

router.post('/login', loginLimiter, async (req, res, next) => {
  const parsed = loginSchema.safeParse(req.body);
  if (!parsed.success) return res.status(400).json({ error: { code: 'INVALID_INPUT', message: 'NPK dan kata sandi wajib diisi.' } });
  try {
    const result = await login(parsed.data.npk, parsed.data.password);
    if (!result) return res.status(401).json({ error: { code: 'INVALID_CREDENTIALS', message: 'NPK atau kata sandi salah.' } });
    res.json(result);
  } catch (error) { next(error); }
});

router.get('/me', authenticate, (req, res) => {
  res.json({ user: { npk: req.user.sub, name: req.user.name, role: req.user.role } });
});

module.exports = router;
