require('dotenv').config({ path: require('path').resolve(__dirname, '../.env') });
const fs = require('node:fs/promises');
const path = require('node:path');
const sharp = require('sharp');
const { pool, sql } = require('../src/config/database');
const { isConvertibleImage } = require('../src/services/image-converter');

async function convertFolderImages(folderPath) {
  try {
    const entries = await fs.readdir(folderPath, { withFileTypes: true });
    const converted = [];
    for (const entry of entries) {
      if (entry.isFile()) {
        const ext = path.extname(entry.name).toLowerCase();
        if (['.jpg', '.jpeg', '.png', '.gif', '.bmp', '.tiff'].includes(ext)) {
          const oldPath = path.join(folderPath, entry.name);
          const baseName = path.basename(entry.name, ext);
          const newName = `${baseName}.webp`;
          const newPath = path.join(folderPath, newName);

          console.log(`Mengonversi: ${entry.name} -> ${newName}`);
          const rawBuffer = await fs.readFile(oldPath);
          await sharp(rawBuffer, { animated: true }).rotate().webp({ quality: 85 }).toFile(newPath);

          // Hapus file lama
          await fs.unlink(oldPath).catch(() => {});
          converted.push({ oldName: entry.name, newName, ext });
        }
      }
    }
    return converted;
  } catch (err) {
    if (err.code !== 'ENOENT') console.warn(`Peringatan direktori ${folderPath}:`, err.message);
    return [];
  }
}

async function run() {
  console.log('=== Memulai Migrasi Gambar Uploads ke Format WebP ===\n');
  const baseUploadsDir = path.resolve(__dirname, '../uploads');

  const coursesDir = path.join(baseUploadsDir, 'courses');
  const forumDir = path.join(baseUploadsDir, 'forum');
  const materialsDir = path.join(baseUploadsDir, 'materials');

  const convertedCourses = await convertFolderImages(coursesDir);
  const convertedForum = await convertFolderImages(forumDir);
  const convertedMaterials = await convertFolderImages(materialsDir);

  console.log(`\nSelesai konversi file fisik:`);
  console.log(`- Courses: ${convertedCourses.length} file`);
  console.log(`- Forum: ${convertedForum.length} file`);
  console.log(`- Materials: ${convertedMaterials.length} file\n`);

  try {
    await pool.connect();
    console.log('Terhubung ke database. Memperbarui referensi path...');

    // 1. Update courses thumbnail_path
    for (const item of convertedCourses) {
      const oldSub = item.oldName;
      const newSub = item.newName;
      await pool.request()
        .input('oldSub', sql.NVarChar(500), `%${oldSub}%`)
        .input('newFilename', sql.NVarChar(500), `/courses/thumbnails/${newSub}`)
        .query(`UPDATE dbo.courses SET thumbnail_path = @newFilename WHERE thumbnail_path LIKE @oldSub`);
    }

    // 2. Update forum_threads attachment_path
    for (const item of convertedForum) {
      const oldSub = item.oldName;
      const newSub = item.newName;
      await pool.request()
        .input('oldSub', sql.NVarChar(500), `%${oldSub}%`)
        .input('newFilename', sql.NVarChar(500), `/forum/uploads/${newSub}`)
        .query(`UPDATE dbo.forum_threads SET attachment_path = @newFilename WHERE attachment_path LIKE @oldSub`);
    }

    // 3. Update material_submissions & course_materials file_path
    for (const item of convertedMaterials) {
      const oldSub = item.oldName;
      const newSub = item.newName;
      await pool.request()
        .input('oldFile', sql.NVarChar(500), oldSub)
        .input('newFile', sql.NVarChar(500), newSub)
        .query(`
          UPDATE dbo.material_submissions SET file_path = @newFile WHERE file_path = @oldFile;
          UPDATE dbo.course_materials SET file_path = @newFile WHERE file_path = @oldFile;
        `);
    }

    console.log('Semua referensi path di database berhasil disinkronkan ke .webp!');
  } catch (dbErr) {
    console.warn('Catatan database saat sinkronisasi:', dbErr.message);
  } finally {
    try { await pool.close(); } catch {}
  }

  console.log('\n=== Migrasi WebP Selesai ===');
}

run().catch(err => {
  console.error('Fatal error saat migrasi WebP:', err);
  process.exit(1);
});
