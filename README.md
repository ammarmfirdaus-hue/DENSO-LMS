# DENSO Learning Management System (DLS)

## Ringkasan Proyek

DENSO Learning Management System (DLS) adalah platform manajemen pembelajaran skala korporat yang dikembangkan secara eksklusif untuk kebutuhan internal PT Denso Indonesia. Sistem ini dirancang secara khusus untuk memenuhi standar operasional di lingkungan manufaktur, dengan fokus pada penyediaan siklus pembelajaran yang terstruktur, efisien, dan memiliki kemampuan untuk beroperasi secara luring (offline) secara penuh tanpa memerlukan konektivitas ke layanan pihak ketiga.

Sistem ini sedang difokuskan pada tahap peluncuran awal (Minimum Viable Product) yang menitikberatkan pada interaksi siklus pembelajaran antara Pengajar (Teacher) dan Siswa (Student), mencakup proses dari manajemen materi, pelaksanaan kuis, hingga pelacakan kemajuan belajar yang terintegrasi.

## Teknologi yang Digunakan

Proyek ini dibangun menggunakan susunan teknologi berikut untuk memastikan stabilitas, efisiensi performa, dan kemandirian ekosistem aplikasi:

### Lingkungan Backend
- **PHP 8.3**: Menggunakan instalasi PHP portabel (berada di dalam direktori `php-8.3.31/`) untuk memastikan konsistensi versi di setiap lingkungan pengembangan. Hal ini mencegah terjadinya bentrok konfigurasi pada sistem operasi pengembang.
- **Microsoft SQL Server**: Basis data utama, diakses secara eksklusif melalui antarmuka PDO dengan menggunakan ekstensi `sqlsrv`.
- **PHP Built-in Server**: Server web bawaan PHP digunakan untuk memfasilitasi proses pengembangan lokal yang ringan dan cepat tanpa perlu melakukan instalasi Apache atau Nginx secara manual.

### Lingkungan Frontend
- **Tailwind CSS**: Kerangka kerja CSS utama yang digunakan di proyek ini. Dikompilasi menggunakan Node.js (npm). Penggunaan kerangka kerja antarmuka gaya lama seperti Bootstrap sangat dilarang.
- **SweetAlert2**: Pustaka dialog interaktif yang disimpan secara lokal untuk menggantikan standar alert dari peramban.
- **Vanilla JavaScript**: Seluruh logika interaksi antarmuka sisi klien ditulis menggunakan JavaScript murni (tanpa kerangka kerja modern seperti React atau Vue) untuk menekan tingkat kompleksitas.
- **Aset Lokal Terisolasi**: Seluruh aset yang bersifat statis berupa fon (Inter) maupun ikon (Material Symbols Outlined) disimpan secara lokal untuk menjamin fungsionalitas sistem ketika tidak terhubung ke jaringan internet publik. Dilarang mendistribusikan aset melalui jaringan pengiriman konten (*Content Delivery Network* / CDN).

## Persyaratan Sistem Pengembangan

Sebelum melakukan konfigurasi proyek, pastikan mesin pengembangan yang Anda gunakan memenuhi spesifikasi perangkat lunak berikut:
1. **Sistem Operasi Windows**: Diperlukan untuk mengeksekusi skrip *batch* (`.bat`) bawaan secara langsung serta memastikan dukungan penuh untuk instalasi portabel PHP yang disediakan.
2. **Microsoft SQL Server**: Instansi lokal atau server pengembangan yang dapat diakses, beserta *SQL Server Management Studio* (SSMS) untuk mempermudah operasional manajemen tabel basis data.
3. **Node.js dan NPM**: Secara wajib dibutuhkan untuk menjalankan proses instalasi dependensi *frontend* dan kompilasi modul Tailwind CSS (CLI).

## Panduan Instalasi dan Konfigurasi

Ikuti instruksi langkah demi langkah di bawah ini untuk mengatur lingkungan pengembangan lokal di perangkat Anda:

### 1. Konfigurasi Basis Data
1. Jalankan aplikasi Microsoft SQL Server Management Studio (SSMS).
2. Buat basis data baru yang benar-benar kosong dan berikan nama `DX_LMS`.
3. Buka direktori proyek Anda dan navigasikan ke dalam berkas `database/DX_LMS_W_Schema.sql`.
4. Salin dan eksekusi skrip SQL tersebut pada basis data `DX_LMS` yang baru saja dibuat. Skrip ini dirancang untuk menghasilkan seluruh skema tabel struktural, mendefinisikan kunci utama (Primary Key), serta memetakan batasan relasi antar tabel (Foreign Key Constraints) yang krusial bagi sistem.

### 2. Pengaturan Kredensial Aplikasi
1. Gunakan aplikasi editor teks atau *IDE* (Integrated Development Environment) untuk membuka berkas `includes/DX_LMS_M_Config.php`.
2. Temukan bagian pengaturan koneksi basis data yang diwakili oleh blok konstanta `DB_SERVER`, `DB_NAME`, `DB_USER`, dan `DB_PASS`.
3. Sesuaikan nilai konstanta tersebut agar koheren dengan informasi kredensial layanan SQL Server lokal Anda. Pastikan untuk menambahkan atribut `TrustServerCertificate => true` ke dalam konfigurasi opsi peladen Anda menggunakan sertifikat berlapis SSL sementara secara lokal.

### 3. Kompilasi Aset Frontend (Tailwind CSS)
Mengingat aplikasi wajib berjalan di lingkungan internal yang bisa jadi tidak memiliki sambungan web, seluruh aturan kerangka kerja CSS wajib dibangun ke dalam satu berkas statis.
1. Buka antarmuka baris perintah (Terminal atau Command Prompt) pada direktori *root* proyek.
2. Jalankan instruksi `npm install` untuk mengunduh modul-modul pengembang secara utuh (Tailwind CSS, Plugin tambahan, dan Prettier).
3. Jalankan perintah `npm run build` untuk menghasilkan berkas keluaran (*output*) CSS untuk pertama kalinya. 
4. Jika Anda terlibat aktif dalam merancang komponen antarmuka, disarankan untuk membiarkan perintah `npm run dev` berjalan di jendela konsol terpisah. Perintah ini mengaktifkan mode pemantauan (*watch mode*) yang akan mengompilasi setiap kali terjadi perubahan kode kelas (*classes*).

### 4. Menjalankan Server Pengembangan Lokal
1. Pastikan validasi bahwa direktori `php-8.3.31/` (yang berisi instrumen modul PHP portabel) telah tersemat di dalam direktori *root* proyek.
2. Buka jendela *File Explorer* dan eksekusi berkas `DX_LMS_W_Start_Server.bat` dengan cara melakukan klik ganda. Skrip bawaan Windows ini akan merutekan lalu lintas HTTP pada lingkungan *Built-in Web Server* milik eksekusi antarmuka PHP.
3. Buka peramban web (browser) favorit Anda dan navigasikan ke alamat `http://localhost:8080`.
4. Untuk menghentikan operasional peladen secara paksa, klik pada jendela konsol perintah terminal yang berjalan, kemudian eksekusi kombinasi tombol `CTRL+C` pada papan ketik.

## Arsitektur Sistem

Aplikasi dibangun berdasarkan pola arsitektur *Model-View-Controller* (MVC) konvensional dengan tingkat pemisahan tanggung jawab (*separation of concerns*) yang memadai, didesain tanpa lapisan repositori (*repository layer*) atau sistem pemetaan rumit demi kemudahan perawatan (*maintainability*).

- **Router** (`Core/DX_LMS_M_Router.php` & `DX_LMS_M_Index.php`): Komponen ini merupakan titik masuk lalu lintas aplikasi dan bertugas memetakan URL spesifik yang diminta ke kelas *Controller* serta metode (method) yang berkorespondensi dengannya (Sebagai misal: `/student/courses` -> `DX_LMS_M_Student_Course@index`).
- **Controller** (`Controllers/`): Unit bisnis pusat yang menerima parameter URL, melakukan otorisasi hak askses, memvalidasi input *payload*, mengatur aliran program, dan merangkum seluruh variabel menjadi bentuk susunan data sebelum dialirkan ke *View*. Seluruh *Controller* dipisahkan ke dalam folder berdasar batas kewenangan aktor aplikasi (seperti Auth, Student, Teacher, Admin).
- **Model** (`Models/`): Pustaka interaksi sistem basis data murni. Biasanya membungkus rangkaian skema fungsional atau perhitungan komputasi terhadap suatu tabel acuan entitas dominan.
- **View** (`Views/`): Blok fondasi HTML yang disematkan cuplikan kode PHP murni untuk menampilkan antarmuka. 
- **Includes & Helpers** (`includes/`): Modul atau pustaka global yang bisa digunakan ulang dan berisi fungsionalitas pendukung, contohnya manajemen siklus sesi pengguna (`DX_LMS_M_Session.php`), layanan abstraksi penyambung basis data sederhana (`DX_LMS_M_Db.php`), serta elemen-elemen potong-tampil parsial, seperti struktur tajuk (*header*) dan pijakan (*footer*).

## Standar Penamaan File dan Tabel (Konvensi Utama)

Seluruh anggota tim pengembang diwajibkan untuk menaati sistem penamaan yang konsisten guna mempertahankan tingkat kedisiplinan pengorganisasian kode internal:

### Klasifikasi Penamaan File
- `DX_LMS_M_*` (Master): Prefiks (awalan) ini diaplikasikan pada seluruh komponen atau kelas yang menangani data referensi, struktur stabil, modul otentikasi sentral, atau model tabel dasar (Contoh: `DX_LMS_M_Employees.php`, `DX_LMS_M_Auth.php`).
- `DX_LMS_T_*` (Transaction): Prefiks ini dirancang untuk berkas yang memanipulasi alur yang memicu dinamika transaksional yang frekuensinya tinggi seperti absensi, perhitungan skor, atau perekaman riwayat sesi aktivitas log. (Contoh: `DX_LMS_T_Student_Dashboard.php`).
- `DX_LMS_W_*` (Work): Identifikasi unik untuk skrip-skrip sementara yang tidak dilibatkan dalam proses komputasi aplikasi *production*. Umum dimanfaatkan untuk utilitas lingkungan pengembangan (Contoh: `DX_LMS_W_Start_Server.bat`, `DX_LMS_W_Schema.sql`).

### Regulasi Desain Basis Data
- **Struktur Tabel**: Sama halnya dengan file di atas, wajib menggunakan awalan master/transaksional yang relevan (misalnya tabel `DX_LMS_M_Courses` untuk kategori data Master, tabel `DX_LMS_T_Activity_Logs` untuk kategori data Transaksi).
- **Atribut Kolom**: Format konvensi abjad yang dibolehkan untuk properti kolom secara universal harus berupa *snake_case* dengan bentuk susunan fonetik huruf kecil seutuhnya.
- **Primary Key**: Harus diimplementasikan menggunakan tipe penginisialisasian data `BIGINT IDENTITY(1,1)`. Format penamaannya harus mengikuti standar struktur nomenklatur `{nama_tabel_tunggal}_id` (Contoh rasional: `employee_id`, bukan hanya `id`). Implementasi penanda *UUID* tidak diperkenankan di dalam proyek.
- **Keterlacakan Rekam Jejak (Audit)**: Semua pembuatan tabel fundamental sangat disarankan untuk memiliki kolom penanda temporal bawaan seperti `created_at` dan `updated_at` dengan standar penetapan tipe `DATETIME2`. Tabel yang lebih vital harus dilengkapi pencatatan entitas pencipta atau pemodifikasi: `created_by` dan `updated_by` yang merujuk pada profil otentikasi.
- **Aturan Hard Delete**: Penghancuran sebuah set relasional baris data dari memori fisik basis data secara absolut (*hard delete*) melanggar aturan. Sebagai subtitusi fungsionalitas tersebut, gunakan penyisipan indikator *Soft Delete* berwujud kolom bit penanda status atau penanggalan `deleted_at`.

## Pedoman Kualitas dan Keamanan Rekayasa Perangkat Lunak

1. **Akses Basis Data Tervalidasi**: Merangkai eksekusi log kueri SQL murni secara serampangan yang dikonjungsi secara literal dengan masukan pihak luar dianggap sebagai bentuk pelanggaran sistem operasi yang serius. Wajib mutlak mengadopsi fungsionalitas penyataan disiapkan (*prepared statements*) PDO pada seluruh modul komputasi (`dbQuery`, `dbQueryOne`, `dbExecute`).
2. **Kriptografi Pengelolaan Sandi**: Kata sandi rahasia seluruh hierarki peran profil selalu diverifikasi dan dihasilkan di belakang layar melalui algoritma komputasi `password_hash()` tipe Bcrypt. Validasi sandi di fase gerbang modul registrasi dijalankan menggunakan komputasi fungsi `password_verify()`.
3. **Pencegahan Injeksi Teks Skrip Bersilang (XSS)**: Karena arsitektur proyek ini secara mandiri bergantung kepada luaran dokumen langsung melalui instrumen tag pembuka PHP, seluruh keluaran layar aplikasi wajib didetoksifikasi dari injeksi skrip. Hal ini dapat direalisasikan dengan rutin memberdayakan perlindungan sanitasi (*escaping characters*) melalui inisiasi *shortcut* khusus `e()` (yang mentransformasikan `htmlspecialchars`).
4. **Prinsip Kebersihan Pengorganisasian (*Clean Code*)**: Modul sirkuit *Controller* sejatinya tidak dirancang untuk menangani manipulasi prosedur struktur hierarki logikal data yang kompleks. Hindari mengaburkan lapisan bisnis dengan operasi komputasional intensif. Serahkan kalkulasi yang memiliki duplikasi lebih dari dua area (*areas of separation*) kepada kumpulan rutinitas mandiri di direktori fungsional spesifik: `DX_LMS_M_Functions.php`.
5. **Kesepakatan Konsistensi Desain Antarmuka (UI/UX)**: Estetika seluruh aplikasi berkiblat sepenuhnya pada profil tata desain korporat profesional. Disiplin jarak hierarkis (spacing margin/padding system) bertopang di atas kelipatan skala 8px. Standarisasi varian modul harus mencerminkan komposisi kartu (*Card Layout Patterns*) sekaligus tunduk secara patuh pada pustaka palet visual dari token bawaan `DX_LMS_M_Dls_Tokens.css`. Validasi dialog peramban default (Window Alerts) dilarang dan wajib diganti dengan jembatan pembantu modifikasi *SweetAlert2* seperti rutinitas instruksi `DLS.confirm()` dan `DLS.success()`.

---
*Dokumen fundamental ini bertindak sebagai basis manifestasi kerangka kerja teknis DLS yang bersifat imperatif. Kepatuhan holistik terhadap poin-poin prosedural yang dideklarasikan di sini bersifat mengikat secara mutlak bagi seluruh elemen insinyur perangkat lunak pengembang di instansi operasional proyek terkait.*
