<?php
/**
 * Kelas Pengelola Koneksi Basis Data (Database Connection Helper).
 *
 * Kelas ini bertanggung jawab untuk membangun dan mengelola koneksi aman ke SQL Server
 * dengan memanfaatkan ekstensi PHP Data Objects (PDO). Diimplementasikan menggunakan
 * pola desain Singleton agar koneksi basis data hanya dibuat satu kali (single instance)
 * selama eksekusi aplikasi untuk menghemat sumber daya memori.
 */
class DX_LMS_M_Database
{
    private static $instance = null;
    private $conn;

    /**
     * Konstruktor Kelas Basis Data.
     *
     * Inisialisasi koneksi basis data ke SQL Server menggunakan PDO.
     * Visibilitas diatur ke 'private' untuk menerapkan pola desain Singleton,
     * mencegah pembuatan instance baru secara langsung dengan sintaks 'new'.
     *
     * @throws PDOException Jika koneksi gagal dilakukan (misal: kredensial salah atau server mati).
     */
    private function __construct()
    {
        try {
            /** Menggunakan string koneksi (DSN) untuk pengolahan SQL Server. */
            /** Memastikan ekstensi driver pdo_sqlsrv telah dipasang dan diaktifkan. */
            $dsn = 'sqlsrv:Server=' . DB_SERVER . ';Database=' . DB_NAME . ';TrustServerCertificate=1';

            $user = DB_USER === '' ? null : DB_USER;
            $pass = DB_PASS === '' ? null : DB_PASS;
            $this->conn = new PDO($dsn, $user, $pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            /** Langkah pencegahan kesalahan fatal jika driver tidak tersedia atau basis data luring. */
            /** Penanganan pengecualian disesuaikan untuk struktur kerangka kerja minimalis. */
            /** Menghentikan eksekusi aplikasi dan menampilkan pesan kesalahan yang informatif. */
            die(
                'Database Connection Error: ' .
                    $e->getMessage() .
                    ' | Pastikan SQL Server berjalan dan driver pdo_sqlsrv aktif.'
            );
        }
    }

    /**
     * Mendapatkan Instance (Objek) Tunggal dari Basis Data.
     *
     * Memeriksa apakah instance statis sudah terbentuk. Jika belum, fungsi ini
     * akan membuat instance baru. Jika sudah, ia akan mengembalikan instance yang ada,
     * sehingga memastikan bahwa hanya ada satu koneksi aktif ke basis data (Singleton Pattern).
     *
     * @return DX_LMS_M_Database Mengembalikan instance statis pengelola basis data.
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new DX_LMS_M_Database();
        }
        return self::$instance;
    }

    /**
     * Mengambil Objek Koneksi PDO yang Sedang Aktif.
     *
     * Digunakan secara ekstensif oleh kelas-kelas model (Model) untuk mengakses basis data,
     * mempersiapkan query (prepared statements), dan mengeksekusi operasi CRUD (Create, Read, Update, Delete).
     *
     * @return PDO Mengembalikan objek koneksi murni (raw PDO connection).
     */
    public function getConnection()
    {
        return $this->conn;
    }
}
