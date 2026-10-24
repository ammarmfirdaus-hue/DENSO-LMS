const multer = require('multer');

const forumImageUpload = multer({
  storage: multer.memoryStorage(),
  limits: { fileSize: 5 * 1024 * 1024, files: 1, fields: 4, parts: 6, fieldSize: 4000 },
  fileFilter: (_req, file, callback) => {
    if (!['image/jpeg', 'image/png', 'image/gif', 'image/webp'].includes(file.mimetype)) {
      const error = new Error('Lampiran forum hanya menerima gambar JPG, PNG, GIF, atau WebP.');
      error.status = 415;
      return callback(error);
    }
    callback(null, true);
  }
});

module.exports = { forumImageUpload };
