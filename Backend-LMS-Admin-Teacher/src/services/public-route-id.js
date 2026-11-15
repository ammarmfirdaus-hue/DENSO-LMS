const crypto = require('node:crypto');
const env = require('../config/env');

// Opaque route IDs are authenticated/encrypted references, not replacements
// for authorization. Every route still checks the current user's scope.
const key = crypto.createHash('sha256').update(`${env.JWT_SECRET}:public-route-id`).digest();

function encodePublicId(type, value) {
  const iv = crypto.randomBytes(12);
  const cipher = crypto.createCipheriv('aes-256-gcm', key, iv);
  const ciphertext = Buffer.concat([cipher.update(`${type}:${String(value)}`, 'utf8'), cipher.final()]);
  return `${iv.toString('base64url')}.${ciphertext.toString('base64url')}.${cipher.getAuthTag().toString('base64url')}`;
}

function decodePublicId(type, token) {
  if (typeof token !== 'string' || token.length < 20 || token.length > 180 || !/^[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+$/.test(token)) return null;
  try {
    const [iv, ciphertext, tag] = token.split('.').map(part => Buffer.from(part, 'base64url'));
    if (iv.length !== 12 || tag.length !== 16) return null;
    const decipher = crypto.createDecipheriv('aes-256-gcm', key, iv);
    decipher.setAuthTag(tag);
    const value = Buffer.concat([decipher.update(ciphertext), decipher.final()]).toString('utf8');
    const prefix = `${type}:`;
    if (!value.startsWith(prefix)) return null;
    const id = value.slice(prefix.length);
    return /^\d+$/.test(id) && Number.isSafeInteger(Number(id)) && Number(id) > 0 ? Number(id) : null;
  } catch {
    return null;
  }
}

module.exports = { encodePublicId, decodePublicId };
