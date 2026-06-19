<?php
/**
 * Pembungkus Koneksi Basis Data (Database Connection Wrapper).
 * 
 * Menyediakan kumpulan fungsi prosedural untuk mengakses dan memanipulasi basis data.
 * Memanfaatkan PDO dengan ekstensi `sqlsrv` untuk koneksi yang aman ke Microsoft SQL Server.
 */

require_once __DIR__ . '/config.php';

/**
 * Mendapatkan Objek Koneksi PDO (Singleton Database Connection).
 * 
 * Membuat dan mengembalikan instans koneksi basis data menggunakan metode Singleton
 * agar tidak terjadi pembukaan koneksi berulang yang membebani peladen.
 * 
 * @return PDO|null Objek koneksi PDO jika berhasil, atau null jika gagal.
 */
function getDBConnection(): ?PDO
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = "sqlsrv:Server=" . DB_HOST . "," . DB_PORT . ";Database=" . DB_NAME;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::SQLSRV_ATTR_ENCODING    => PDO::SQLSRV_ENCODING_UTF8,
            ];

            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Catat error, jangan tampilkan di production
            if (defined('E_ALL') && error_reporting() === E_ALL) {
                die("Database connection failed: " . $e->getMessage());
            }
            return null;
        }
    }

    return $pdo;
}

/**
 * Menjalankan Kueri `SELECT` Jamak (Execute Multiple Rows SELECT Query).
 * 
 * Mengeksekusi pernyataan SQL yang diberikan beserta dengan parameternya.
 * Mengembalikan semua baris hasil dalam bentuk larik asosiatif (associative array).
 * 
 * @param string $sql Kueri SQL yang akan dieksekusi (disarankan menggunakan Parameterized Query).
 * @param array $params Kumpulan nilai parameter untuk mengikat (`bind`) data pada kueri.
 * @return array Larik hasil temuan. Kosong jika tidak ada data atau koneksi gagal.
 */
function dbQuery(string $sql, array $params = []): array
{
    $pdo = getDBConnection();
    if (!$pdo) return [];

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Menjalankan Kueri `SELECT` Tunggal (Execute Single Row SELECT Query).
 * 
 * Serupa dengan `dbQuery()`, tetapi hanya mengembalikan baris pertama yang ditemukan.
 * Berguna saat mencari data berdasarkan kunci primer (Primary Key) yang unik.
 * 
 * @param string $sql Kueri SQL yang akan dieksekusi.
 * @param array $params Kumpulan parameter kueri.
 * @return array|null Mengembalikan satu baris data sebagai larik asosiatif, atau `null` jika tidak ditemukan.
 */
function dbQueryOne(string $sql, array $params = []): ?array
{
    $pdo = getDBConnection();
    if (!$pdo) return null;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch();
    return $result ?: null;
}

/**
 * Menjalankan Kueri Eksekusi (Execute INSERT/UPDATE/DELETE).
 * 
 * Berfungsi untuk operasi yang mengubah status data dalam basis data,
 * seperti penambahan, pembaruan, atau penghapusan catatan.
 * 
 * @param string $sql Kueri SQL yang akan dieksekusi (INSERT/UPDATE/DELETE).
 * @param array $params Parameter untuk kueri terkait.
 * @return bool Menghasilkan nilai `true` jika eksekusi berhasil, dan `false` jika sebaliknya.
 */
function dbExecute(string $sql, array $params = []): bool
{
    $pdo = getDBConnection();
    if (!$pdo) return false;

    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}
