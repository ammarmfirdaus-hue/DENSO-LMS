const extensionsByMime = new Map([
  ['image/jpeg', '.jpg'],
  ['image/png', '.png'],
  ['image/gif', '.gif'],
  ['image/webp', '.webp']
]);

function parseForumImageBuffer(mimeType, buffer) {
  if (!Buffer.isBuffer(buffer) || buffer.length === 0) return null;
  const mime = String(mimeType || '').toLowerCase();
  const extension = extensionsByMime.get(mime);
  if (!extension) return null;

  const valid = extension === '.jpg'
    ? buffer[0] === 0xff && buffer[1] === 0xd8 && buffer[2] === 0xff
    : extension === '.png'
      ? buffer.subarray(0, 8).equals(Buffer.from([0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a]))
      : extension === '.gif'
        ? ['GIF87a', 'GIF89a'].includes(buffer.subarray(0, 6).toString('ascii'))
        : buffer.subarray(0, 4).toString('ascii') === 'RIFF' && buffer.subarray(8, 12).toString('ascii') === 'WEBP';

  return valid ? { extension } : null;
}

module.exports = { parseForumImageBuffer };
