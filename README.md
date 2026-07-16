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

Selamat datang di tim pengembangan DLS! Bagian ini disusun secara khusus untuk memandu Anda, sebagai anggota tim baru, agar dapat melakukan pengaturan lingkungan pengembangan di mesin lokal Anda dari nol hingga aplikasi siap berjalan. Silakan ikuti setiap instruksi secara runut dan teliti.

### Tahap 1: Persiapan Lingkungan dan Basis Data (Microsoft SQL Server)
Aplikasi ini beroperasi menggunakan Microsoft SQL Server. Oleh karena itu, pengaturan basis data merupakan langkah pertama yang paling krusial.
1. **Buka SQL Server Management Studio (SSMS)**: Pastikan layanan SQL Server sudah berjalan di latar belakang mesin Anda (biasanya bernama `MSSQLSERVER` atau `SQLEXPRESS` pada layanan sistem Windows). Buka aplikasi SSMS dan masuk menggunakan autentikasi Windows (*Windows Authentication*) atau autentikasi SQL Server (*SQL Server Authentication*).
2. **Pembuatan Basis Data**:
   - Pada panel *Object Explorer* di sebelah kiri, klik kanan pada folder **Databases**, lalu pilih **New Database...**
   - Pada kolom masukan *Database name*, masukkan nama persis: `DX_LMS`.
   - Biarkan pengaturan lainnya secara bawaan (*default*), lalu klik **OK**.
3. **Injeksi Skema Basis Data**:
   - Buka direktori proyek lokal Anda menggunakan *File Explorer*, lalu cari direktori `database/`.
   - Di dalamnya terdapat berkas bernama `DX_LMS_W_Schema.sql`. Seret (*drag*) berkas tersebut ke dalam jendela bidang kerja utama SSMS Anda.
   - Pastikan menu tarik-turun (*dropdown*) basis data di bilah alat SSMS (bagian atas) menunjukkan `DX_LMS`, bukan `master`.
   - Klik tombol **Execute** (atau tekan tombol `F5` pada papan ketik).
   - Pastikan tab *Messages* di bagian bawah menampilkan tulisan *Command(s) completed successfully*. Skrip ini akan secara otomatis mendirikan seluruh tabel beserta relasi (*Foreign Keys*) yang dibutuhkan aplikasi.

### Tahap 2: Pengaturan Kredensial Akses Aplikasi
Aplikasi perlu mengetahui cara untuk berkomunikasi dengan basis data lokal Anda.
1. **Lokasi File Konfigurasi**: Buka proyek menggunakan kode editor atau IDE andalan Anda (seperti Visual Studio Code atau PhpStorm). Navigasikan ke dalam direktori `includes/` dan buka berkas `DX_LMS_M_Config.php`.
2. **Penyelarasan Kredensial**: Temukan blok kode pendefinisian konstanta koneksi basis data. Anda perlu menyesuaikan parameter berikut sesuai konfigurasi SQL Server Anda:
   - `DB_SERVER`: Alamat server Anda (biasanya `localhost`, `127.0.0.1`, atau `.\SQLEXPRESS`).
   - `DB_NAME`: Pastikan nilainya tetap `'DX_LMS'`.
   - `DB_USER`: Nama pengguna SQL (contoh: `'sa'`). Jika Anda sepenuhnya menggunakan metode autentikasi Windows untuk server pengembangan, pengaturan ini mungkin berbeda bergantung implementasi pembungkus data.
   - `DB_PASS`: Kata sandi untuk pengguna SQL tersebut.
3. **Konfigurasi SSL Terpercaya (Opsional namun Penting)**: Apabila Anda mendapati pesan galat koneksi terkait SSL ketika aplikasi dijalankan, pastikan untaian (*string*) koneksi PDO (yang terletak di berkas `DX_LMS_M_Db.php`) memiliki parameter `TrustServerCertificate => true`.

### Tahap 3: Instalasi dan Kompilasi Aset Frontend (Tailwind CSS)
DLS tidak bergantung pada CDN (*Content Delivery Network*) eksternal demi memastikan ketersediaan aplikasi saat luring (*offline*) secara total. Seluruh kerangka kerja CSS wajib dibangun secara lokal.
1. **Membuka Terminal**: Buka antarmuka baris perintah (Terminal bawaan IDE, Command Prompt, atau PowerShell) dan pastikan Anda berada tepat di direktori *root* proyek (yaitu folder `DENSO-LMS/`).
2. **Instalasi Modul Node**:
   - Ketikkan perintah `npm install` dan tekan *Enter*. 
   - Sistem akan mengunduh Tailwind CSS, instrumen *PostCSS*, dan *Prettier* sesuai dengan definisi di berkas `package.json`. Tunggu hingga proses instalasi mencapai 100% dan menghasilkan direktori baru bernama `node_modules/`.
3. **Kompilasi Berkas CSS Awal**:
   - Eksekusi perintah `npm run build`.
   - Perintah ini akan memindai seluruh *file* berekstensi `.php` yang ada di proyek, mengekstrak kelas-kelas Tailwind yang dideklarasikan, dan menyatukannya ke dalam berkas statis `assets/css/DX_LMS_M_Tailwind_Output.css`.
4. **Mode Pengembangan Aktif (*Watch Mode*)**:
   - Jika penugasan harian Anda mengharuskan adanya modifikasi kode antarmuka (*UI styling*), sangat disarankan untuk menjalankan perintah `npm run dev` pada jendela terminal yang dibiarkan terbuka. Terminal ini akan memantau perubahan kelas Tailwind yang Anda ketik dan mengompilasinya secara waktu nyata (*real-time*).

### Tahap 4: Mengaktifkan Server Pengembangan Lokal
Proyek ini mengimplementasikan interpreter PHP secara portabel, mengeliminasi kebutuhan bagi anggota tim baru untuk mengonfigurasi Apache, Nginx, XAMPP, atau variabel lingkungan (*Environment Variables*) sistem operasi secara merepotkan.
1. **Verifikasi Modul PHP**: Periksa bahwa folder bernama `php-8.3.31/` telah tersedia di dalam direktori *root* proyek. Folder ini memuat biner PHP yang telah disesuaikan dan dikonfigurasi bersama ekstensi SQLSRV untuk lingkungan Windows.
2. **Peluncuran Server**:
   - Lewat *File Explorer*, cari dan eksekusi (klik ganda) berkas skrip *batch* bernama `DX_LMS_W_Start_Server.bat`.
   - Jendela konsol *Command Prompt* berlatar hitam akan langsung muncul. Jendela ini bertindak sebagai log proses peladen web. **Jangan tutup jendela ini** selama Anda masih berencana mengoperasikan aplikasi di browser.
3. **Akses Aplikasi**:
   - Buka peramban web (seperti Google Chrome atau Microsoft Edge).
   - Ketikkan alamat URL `http://localhost:8080` pada bilah alamat. 
   - Jika semua tahapan di atas berhasil, Anda seharusnya akan disambut oleh visualisasi halaman otentikasi (Login) DLS.
4. **Terminasi Server**: Untuk mematikan server secara aman saat jadwal pengembangan Anda usai, aktifkan fokus ke jendela hitam *Command Prompt* peladen tadi, lalu tekan kombinasi `CTRL + C` di papan ketik. Jika diminta persetujuan pemutusan tugas, ketik huruf `Y` dan tekan *Enter*.

### Tahap 5: Pemecahan Masalah Umum (Troubleshooting Lanjutan)
Sebagai anggota tim baru, mungkin saja Anda akan menemui rintangan saat konfigurasi pertama kali. Berikut ini adalah kompilasi masalah lazim dan jalan keluarnya:
- **Port 8080 Bentrok (Server Gagal Jalan)**: Jika jendela hitam `DX_LMS_W_Start_Server.bat` tertutup sekejap mata sesaat setelah dibuka, kemungkinan besar ada aplikasi lain di mesin Anda yang sedang memonopoli *port* `8080`. Sunting file *batch* tersebut dengan *Notepad* dan ubah angka `8080` menjadi angka lain (misal: `8000`), simpan, lalu coba jalankan kembali.
- **Kesalahan Pengandar PDO (Fatal Error PDO Exception)**: Ini mengindikasikan koneksi terputus. Periksa ulang bahwa variabel koneksi di `DX_LMS_M_Config.php` sudah merepresentasikan spesifikasi SQL Server Anda yang menyala aktif.
- **Tampilan Antarmuka Hancur Berantakan**: Pertanda mutlak bahwa kompilasi *stylesheet* utilitas CSS Anda gagal, belum pernah dijalankan, atau terminal `npm run dev` Anda mengalami kebuntuan fatal. Kembalilah mereferensi ke Tahap 3 dan pastikan Anda mengeksekusi ulang komando `npm run build`.

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
