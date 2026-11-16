const MAX_EDGE = 1920;
const SMALL_FILE_LIMIT = 360 * 1024;
const MAX_SOURCE_SIZE = 25 * 1024 * 1024;
const QUALITY_STEPS = [0.88, 0.84, 0.8];

/** Compress static forum images before upload; keep animated formats untouched. */
export async function compressForumImage(file) {
  return compressImageToWebP(file, { forceWebp: false });
}

/**
 * Mengompresi dan mengonversi file gambar ke format WebP di sisi browser.
 * 
 * @param {File} file File gambar yang dipilih
 * @param {object} options Opsi (forceWebp: boolean, maxEdge: number)
 * @returns {Promise<File>} File yang sudah terkonversi ke WebP
 */
export async function compressImageToWebP(file, { forceWebp = true, maxEdge = MAX_EDGE } = {}) {
  if (!(file instanceof File) || !['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > MAX_SOURCE_SIZE) return file;
  if (file.type === 'image/gif' || (file.type === 'image/webp' && await isAnimatedWebP(file))) return file;

  let image;
  let imageUrl = '';
  try {
    if (typeof createImageBitmap === 'function') {
      image = await createImageBitmap(file);
    } else {
      imageUrl = URL.createObjectURL(file);
      image = new Image();
      image.decoding = 'async';
      image.src = imageUrl;
      await image.decode();
    }
    const width = image.width || image.naturalWidth;
    const height = image.height || image.naturalHeight;
    if (!width || !height) return file;
    if (!forceWebp && file.size <= SMALL_FILE_LIMIT && Math.max(width, height) <= maxEdge) return file;

    const scale = Math.min(1, maxEdge / Math.max(width, height));
    const canvas = document.createElement('canvas');
    canvas.width = Math.max(1, Math.round(width * scale));
    canvas.height = Math.max(1, Math.round(height * scale));
    const context = canvas.getContext('2d', { alpha: true });
    if (!context) return file;
    context.drawImage(image, 0, 0, canvas.width, canvas.height);

    for (const quality of QUALITY_STEPS) {
      const blob = await canvasBlob(canvas, quality);
      if (!blob || blob.type !== 'image/webp') continue;
      if (!forceWebp && blob.size >= file.size) return file;
      if (blob.size <= 2 * 1024 * 1024 || quality === QUALITY_STEPS[QUALITY_STEPS.length - 1]) {
        const baseName = file.name.replace(/\.[^.]+$/, '') || 'image';
        return new File([blob], `${baseName}.webp`, { type: 'image/webp', lastModified: file.lastModified });
      }
    }
  } catch {
    // Keep image upload working if this browser cannot decode or encode the file.
  } finally {
    image?.close?.();
    if (imageUrl) URL.revokeObjectURL(imageUrl);
  }
  return file;
}

function canvasBlob(canvas, quality) {
  return new Promise(resolve => canvas.toBlob(resolve, 'image/webp', quality));
}

async function isAnimatedWebP(file) {
  try {
    const bytes = new Uint8Array(await file.slice(0, 64 * 1024).arrayBuffer());
    if (readAscii(bytes, 0, 4) !== 'RIFF' || readAscii(bytes, 8, 4) !== 'WEBP') return false;
    const view = new DataView(bytes.buffer, bytes.byteOffset, bytes.byteLength);
    let offset = 12;
    while (offset + 8 <= bytes.length) {
      const chunk = readAscii(bytes, offset, 4);
      if (chunk === 'ANIM' || chunk === 'ANMF') return true;
      const size = view.getUint32(offset + 4, true);
      offset += 8 + size + (size % 2);
    }
  } catch {
    // A malformed WebP will be rejected by the API's image signature validation.
  }
  return false;
}

function readAscii(bytes, offset, length) {
  return String.fromCharCode(...bytes.subarray(offset, offset + length));
}
