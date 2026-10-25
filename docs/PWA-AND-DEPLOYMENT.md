# PWA dan Panduan Deployment DENSO LMS

**Tanggal analisis:** 25 Oktober 2026  
**Cakupan:** Frontend Admin/Teacher React/Vite dan Native Student PHP.

## Ringkasan

Kedua portal web dapat dipasang dari browser sebagai aplikasi mandiri. PWA menggunakan manifest, ikon, modal panduan instalasi, dan service worker dengan cakupan root pada origin masing-masing. Portal Student dan Admin/Teacher tetap menjadi aplikasi terpisah; instalasi PWA tidak menyatukan sesi, database, atau kewenangan.

| Portal | Manifest dan ikon | Prompt instalasi | Service worker |
|---|---|---|---|
| Admin/Teacher | `Frontend-LMS-Admin-Teacher/public/manifest.webmanifest`; ikon 180, 192, dan 512 px | `src/components/PwaInstallPrompt.jsx` dan `src/pwa/installPrompt.js`; menangani prompt Chrome/Edge dan petunjuk iOS | `public/sw.js`; terdaftar dari `src/pwa/registerServiceWorker.js` |
| Student | `NATIVE-LMS-Student/public/manifest.webmanifest`; ikon 180, 192, dan 512 px | `public/assets/js/DX_LMS_M_Pwa_Install.js`, dimuat dari komponen head bersama | `public/sw.js`; lingkup root aplikasi PHP |

## Perilaku instalasi

- **Chrome dan Edge:** browser mengirim `beforeinstallprompt` jika persyaratan instalasi terpenuhi. Portal menahannya dan menampilkan modal DENSO LMS; tombol **Pasang aplikasi** memanggil prompt instalasi native.
- **iPhone dan iPad:** portal menampilkan instruksi **Bagikan → Tambahkan ke Layar Utama**, karena alur instalasi Apple dilakukan melalui menu browser.
- **Nanti/tutup:** pilihan disimpan di `localStorage` agar ajakan tidak muncul di setiap kunjungan. Portal menyembunyikan modal ketika sudah terbuka sebagai aplikasi mandiri.
- Modal berjalan pada halaman login dan halaman portal, tetapi tidak mengubah validasi login, role, atau SSO.

## Cakupan offline dan privasi

Service worker sengaja menyediakan fallback offline sederhana. PWA **bukan** mode offline untuk mengelola LMS.

| Permintaan | Perilaku service worker |
|---|---|
| Navigasi halaman | Ambil dari jaringan. Jika jaringan gagal, tampilkan `offline.html`; halaman PHP/SPA yang memuat data pribadi tidak disimpan. |
| Aset `/assets/` | Cache lokal untuk ikon, CSS, JavaScript, font, dan gambar publik; permintaan jaringan memperbarui cache. |
| Manifest dan halaman offline | Disimpan saat service worker dipasang. Ikon offline utama juga diprecache. |
| `/api/`, `/uploads/`, form dan mutasi | Tidak masuk cache. Permintaan API dan upload hanya bekerja saat jaringan tersedia. |
| SSE, email dan job antrean | Tidak dikelola service worker. Backend tetap memproses job yang sudah dibuat; browser perlu online untuk melihat progress atau mengirim perintah stop/resume. |

Cache tidak memuat token, cookie, HTML halaman pengguna, nilai, isi forum, notifikasi, berkas materi, atau respons API. Tidak ada push notification, background sync, maupun penyimpanan operasi tulis offline yang ditambahkan.

## Persyaratan hosting

1. Gunakan **HTTPS** untuk host deployment. Browser menganggap `localhost` dan loopback (`127.0.0.1`) sebagai secure context untuk development.
2. Sajikan setiap proyek pada origin sendiri atau subdomain sendiri. Kedua worker saat ini memakai `/sw.js` dengan scope `/` dan URL aset absolut dari root. Jika dipasang di subpath atau origin yang sama, sesuaikan scope, base path, proxy API, dan aturan cookie SSO agar kedua worker tidak berebut scope.
3. Frontend Admin/Teacher harus menyajikan `/manifest.webmanifest`, `/sw.js`, dan `/offline.html` dari root. Untuk halaman React seperti `/grades`, `/courses`, dan `/review/:token`, host produksi harus mengarahkan navigasi aplikasi ke `index.html` (SPA fallback), tetapi tetap mengirim file PWA sebagai aset statis.
4. Backend tetap harus dapat dijangkau dari browser. Di development Vite mem-proxy `/api` ke API pada port 3001; production harus mengatur `VITE_API_BASE_URL`, CORS, kredensial cookie, dan origin SSO sesuai host HTTPS.
5. Student PHP menggunakan `APP_URL` HTTPS dan konfigurasi SSO/cookie domain yang cocok untuk domain production. Worker hanya dapat mengontrol halaman yang berada di origin dan scope-nya.
6. Jalankan `npm run build` pada `Frontend-LMS-Admin-Teacher`; Vite menyalin isi `public/` ke `dist/`. Untuk Student, jalankan server PHP dengan document root `public` seperti di README proyek.

Alamat `127.0.0.1` hanya dapat dipasang dari perangkat yang menjalankan server. Untuk memasang di ponsel, portal perlu tersedia melalui domain HTTPS yang dapat dijangkau ponsel.

## Pemeliharaan cache

Nama cache worker sekarang menggunakan prefix `dls-admin-teacher-static-` dan `dls-student-static-`, dengan versi `v1`. Jika strategi cache atau aset precache berubah secara tidak kompatibel, naikkan versi pada `CACHE_NAME` di worker terkait. Saat worker baru aktif, cache lama dengan prefix yang sama dibersihkan. Aset dinamis di `/assets/` memakai strategi stale-while-revalidate: cache langsung menjawab jika tersedia, sementara salinan jaringan memperbarui cache. Navigasi tetap network-first.

## Cara memeriksa instalasi di browser

1. Jalankan backend dan portal sesuai README, lalu buka portal pada origin secure (`127.0.0.1` dapat dipakai untuk development).
2. Di Chrome/Edge, periksa DevTools **Application → Manifest** dan **Service Workers**. Modal baru tersedia setelah browser menyatakan aplikasi dapat dipasang.
3. Klik **Pasang aplikasi** dan selesaikan prompt browser. Buka ulang aplikasi dari ikon perangkat untuk memastikan route awal mengarah ke login.
4. Di iPhone/iPad, buka menggunakan browser, pilih **Bagikan → Tambahkan ke Layar Utama**, lalu jalankan ikon baru.
5. Putuskan koneksi dan buka ulang URL untuk memastikan hanya halaman `Anda sedang offline` yang tampil; sambungkan kembali untuk melanjutkan penggunaan. Pastikan request `/api/` tidak dilayani dari cache.

Diagram alur instalasi/offline: [14-pwa-install-offline.drawio](diagrams/14-pwa-install-offline.drawio). Diagram hubungan browser, aplikasi, API, dan database: [15-pwa-system-architecture.drawio](diagrams/15-pwa-system-architecture.drawio).
