const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const multer = require('multer');
const env = require('../config/env');

const uploadDirectory = path.resolve(
  __dirname,
  '../../',
  env.MATERIAL_UPLOAD_DIR || 'uploads/materials'
);
fs.mkdirSync(uploadDirectory, { recursive: true });

const allowedExtensions = new Set([
  '.pdf', '.doc', '.docx', '.ppt', '.pptx', '.xls', '.xlsx', '.odt', '.ods', '.odp',
  '.mp4', '.webm', '.mov', '.ogv', '.mp3', '.wav', '.ogg', '.m4a',
  '.png', '.jpg', '.jpeg', '.gif', '.webp'
]);

const officeExtensions = new Set(['.doc', '.docx', '.ppt', '.pptx', '.xls', '.xlsx', '.odt', '.ods', '.odp']);

async function hasExpectedFileSignature(filePath, extension) {
  const handle = await fs.promises.open(filePath, 'r');
  try {
    const header = Buffer.alloc(16);
    const { bytesRead } = await handle.read(header, 0, header.length, 0);
    const bytes = header.subarray(0, bytesRead);
    const starts = (...signature) => signature.every((byte, index) => bytes[index] === byte);
    switch (extension) {
      case '.pdf': return bytes.subarray(0, 5).toString('ascii') === '%PDF-';
      case '.png': return starts(0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a);
      case '.jpg': case '.jpeg': return starts(0xff, 0xd8, 0xff);
      case '.gif': return ['GIF87a', 'GIF89a'].includes(bytes.subarray(0, 6).toString('ascii'));
      case '.webp': return bytes.subarray(0, 4).toString('ascii') === 'RIFF' && bytes.subarray(8, 12).toString('ascii') === 'WEBP';
      case '.mp4': case '.mov': case '.m4a': return bytes.subarray(4, 8).toString('ascii') === 'ftyp';
      case '.webm': return starts(0x1a, 0x45, 0xdf, 0xa3);
      case '.ogv': case '.ogg': return bytes.subarray(0, 4).toString('ascii') === 'OggS';
      case '.mp3': return bytes.subarray(0, 3).toString('ascii') === 'ID3' || (bytes[0] === 0xff && (bytes[1] & 0xe0) === 0xe0);
      case '.wav': return bytes.subarray(0, 4).toString('ascii') === 'RIFF' && bytes.subarray(8, 12).toString('ascii') === 'WAVE';
      case '.doc': case '.ppt': case '.xls': return starts(0xd0, 0xcf, 0x11, 0xe0, 0xa1, 0xb1, 0x1a, 0xe1);
      default:
        if (officeExtensions.has(extension)) return starts(0x50, 0x4b, 0x03, 0x04);
        return false;
    }
  } finally {
    await handle.close();
  }
}

const storage = multer.diskStorage({
  destination: (_req, _file, callback) => callback(null, uploadDirectory),
  filename: (_req, file, callback) => {
    const extension = path.extname(file.originalname).toLowerCase();
    callback(null, `${crypto.randomUUID()}${extension}`);
  }
});

const materialUpload = multer({
  storage,
  limits: { fileSize: env.MATERIAL_MAX_UPLOAD_BYTES, files: 1, fields: 8, parts: 10, fieldSize: 25000 },
  fileFilter: (_req, file, callback) => {
    const extension = path.extname(file.originalname).toLowerCase();
    if (!allowedExtensions.has(extension)) {
      const error = new Error('Format file tidak didukung. Gunakan PDF, dokumen Office, video, audio, atau gambar.');
      error.status = 415;
      return callback(error);
    }
    callback(null, true);
  }
});

module.exports = { materialUpload, uploadDirectory, hasExpectedFileSignature };
