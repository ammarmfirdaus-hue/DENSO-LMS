const sharp = require('sharp');
const path = require('node:path');
const fs = require('node:fs/promises');
const crypto = require('node:crypto');

const IMAGE_EXTENSIONS = new Set(['.jpg', '.jpeg', '.png', '.gif', '.webp', '.bmp', '.tiff', '.avif']);
const IMAGE_MIME_TYPES = new Set([
  'image/jpeg',
  'image/png',
  'image/webp',
  'image/gif',
  'image/bmp',
  'image/tiff',
  'image/avif'
]);

/**
 * Memeriksa apakah file atau MIME type merupakan gambar yang didukung untuk konversi.
 */
function isConvertibleImage(mimeType, filename = '') {
  const mime = String(mimeType || '').toLowerCase();
  if (IMAGE_MIME_TYPES.has(mime)) return true;
  const ext = path.extname(filename || '').toLowerCase();
  return IMAGE_EXTENSIONS.has(ext);
}

/**
 * Mengonversi buffer gambar apa pun ke format WebP terkompresi optimal.
 * Mendukung animasi untuk GIF/WebP animasi jika ada.
 * 
 * @param {Buffer} inputBuffer Buffer gambar mentah
 * @param {object} options Opsi konversi (quality, maxEdge)
 * @returns {Promise<{ buffer: Buffer, mimeType: string, extension: string, width?: number, height?: number }>}
 */
async function convertImageBufferToWebP(inputBuffer, { quality = 85, maxEdge = 1920 } = {}) {
  if (!Buffer.isBuffer(inputBuffer) || inputBuffer.length === 0) {
    const error = new Error('Buffer gambar tidak valid atau kosong.');
    error.status = 400;
    throw error;
  }

  try {
    let pipeline = sharp(inputBuffer, { animated: true });
    const metadata = await pipeline.metadata();

    // Auto-orient berdasarkan EXIF orientation
    pipeline = pipeline.rotate();

    // Resize proporsional jika dimensi melebihi batas maksimal (maxEdge)
    if (maxEdge && (metadata.width > maxEdge || metadata.height > maxEdge)) {
      pipeline = pipeline.resize({
        width: metadata.width >= metadata.height ? maxEdge : undefined,
        height: metadata.height > metadata.width ? maxEdge : undefined,
        fit: 'inside',
        withoutEnlargement: true
      });
    }

    const webpBuffer = await pipeline.webp({ quality, effort: 4 }).toBuffer();

    return {
      buffer: webpBuffer,
      mimeType: 'image/webp',
      extension: '.webp',
      width: metadata.width,
      height: metadata.height,
      size: webpBuffer.length
    };
  } catch (err) {
    const error = new Error(`Gagal mengonversi gambar ke WebP: ${err.message}`);
    error.status = 415;
    throw error;
  }
}

/**
 * Mengonversi file gambar yang sudah tersimpan di disk (misal oleh Multer diskStorage) ke format WebP.
 * Menghapus file non-WebP asli dan menggantikannya dengan file .webp ber-UUID.
 *
 * @param {string} filePath Path absolut ke file di disk
 * @param {object} options Opsi konversi
 * @returns {Promise<{ filePath: string, filename: string, mimeType: string, size: number }>}
 */
async function convertDiskImageToWebP(filePath, options = {}) {
  const ext = path.extname(filePath).toLowerCase();
  if (!IMAGE_EXTENSIONS.has(ext)) {
    return null; // Bukan file gambar, biarkan apa adanya (misal PDF atau dokumen Office)
  }

  const rawBuffer = await fs.readFile(filePath);
  const { buffer } = await convertImageBufferToWebP(rawBuffer, options);

  const dir = path.dirname(filePath);
  const baseUuid = crypto.randomUUID();
  const newFilename = `${baseUuid}.webp`;
  const newFilePath = path.join(dir, newFilename);

  await fs.writeFile(newFilePath, buffer, { mode: 0o640 });

  // Hapus file lama jika path berbeda
  if (path.resolve(filePath) !== path.resolve(newFilePath)) {
    await fs.unlink(filePath).catch(() => {});
  }

  return {
    filePath: newFilePath,
    filename: newFilename,
    mimeType: 'image/webp',
    size: buffer.length
  };
}

module.exports = {
  isConvertibleImage,
  convertImageBufferToWebP,
  convertDiskImageToWebP,
  IMAGE_EXTENSIONS,
  IMAGE_MIME_TYPES
};
