const multer = require('multer');

const ALLOWED_IMAGE_MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

/**
 * Membuat middleware upload gambar berbasis memoryStorage dengan validasi MIME ketat.
 */
function createImageUpload({ errorMessage, fields = 4, parts = 6, fieldSize = 4000 } = {}) {
  return multer({
    storage: multer.memoryStorage(),
    limits: { fileSize: 5 * 1024 * 1024, files: 1, fields, parts, fieldSize },
    fileFilter: (_req, file, callback) => {
      if (!ALLOWED_IMAGE_MIME_TYPES.includes(file.mimetype)) {
        const error = new Error(errorMessage || 'Hanya menerima gambar JPG, PNG, GIF, atau WebP.');
        error.status = 415;
        return callback(error);
      }
      callback(null, true);
    }
  });
}

const forumImageUpload = createImageUpload({
  errorMessage: 'Lampiran forum hanya menerima gambar JPG, PNG, GIF, atau WebP.',
  fields: 4,
  parts: 6
});

const courseCoverUpload = createImageUpload({
  errorMessage: 'Sampul course hanya menerima gambar JPG, PNG, GIF, atau WebP.',
  fields: 2,
  parts: 4
});

module.exports = { createImageUpload, forumImageUpload, courseCoverUpload };
