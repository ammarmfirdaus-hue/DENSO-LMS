const { z } = require('zod');

const permittedRoles = ['student', 'teacher', 'admin'];

const notificationInputSchema = z.object({
  title: z.string().trim().min(3).max(180).regex(/^[^\r\n\0]+$/),
  message: z.string().trim().min(1).max(2000),
  category: z.enum(['announcement', 'course', 'assessment', 'forum', 'system']).default('announcement'),
  priority: z.enum(['normal', 'important']).default('normal'),
  targetMode: z.enum(['role', 'members']).optional().default('role'),
  targetRoles: z.array(z.enum(permittedRoles)).optional().default(['student']),
  targetMembers: z.array(
    z.object({
      npk: z.string().trim().min(1).max(50),
      name: z.string().trim().max(150).optional(),
      role: z.enum(permittedRoles).optional().default('student'),
      email: z.string().trim().max(254).optional()
    })
  ).optional().default([]),
  sendEmail: z.boolean().default(false),
  showPopup: z.boolean().default(false),
  imageUrl: z.string().trim().max(500).optional().nullable()
}).strict();

function canSendToRoles(senderRole, targetRoles) {
  if (!Array.isArray(targetRoles) || targetRoles.length === 0) return false;
  if (senderRole === 'admin') return targetRoles.every(role => permittedRoles.includes(role));
  return senderRole === 'teacher' && targetRoles.every(role => role === 'student');
}

function canSendToMembers(senderRole, targetMembers) {
  if (!Array.isArray(targetMembers) || targetMembers.length === 0) return false;
  if (senderRole === 'admin') return true;
  return senderRole === 'teacher' && targetMembers.every(m => (m.role || 'student') === 'student');
}

function audienceLabel(role) {
  return role === 'student' ? 'Student' : role === 'teacher' ? 'Teacher' : 'Admin';
}

function emailBatches(recipients, max = 5000) {
  const seen = new Set();
  const unique = [];
  for (const recipient of recipients) {
    const email = String(recipient.email || '').trim().toLowerCase();
    if (!email || email.length > 254 || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email) || seen.has(email)) continue;
    seen.add(email);
    unique.push({ ...recipient, email });
    if (unique.length > max) break;
  }
  return unique;
}

module.exports = { permittedRoles, notificationInputSchema, canSendToRoles, canSendToMembers, audienceLabel, emailBatches };
