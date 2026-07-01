<?php
require_once 'DX_LMS_M_Database.php';

/**
 * Kelas Dasar (Base) ORM Model.
 *
 * Kelas abstrak ini berfungsi sebagai lapisan pemetaan objek-relasional (Object-Relational Mapping / ORM)
 * sederhana. Menyediakan metode dasar operasi basis data seperti pengambilan seluruh data (all),
 * pencarian spesifik (find, where), serta operasi modifikasi (insert, update, delete).
 * Kelas model spesifik (seperti DX_LMS_M_Employees) wajib mewarisi kelas ini.
 */
abstract class DX_LMS_M_Model
{
    protected $table;
    protected $primaryKey = 'id';
    protected $db;

    /**
     * Konstruktor Model.
     *
     * Saat instance model dibuat, ia akan secara otomatis mengambil koneksi PDO
     * aktif dari kelas DX_LMS_M_Database (Singleton), sehingga setiap model
     * menggunakan koneksi jaringan yang sama tanpa melakukan redundansi.
     */
    public function __construct()
    {
        $this->db = DX_LMS_M_Database::getInstance()->getConnection();
    }

    /**
     * Mengambil Seluruh Data dari Tabel.
     *
     * Menjalankan kueri SELECT * sederhana ke tabel yang didefinisikan oleh properti $table.
     * Tidak disarankan untuk tabel dengan jutaan baris data karena dapat membebani memori.
     *
     * @return array Kumpulan baris hasil kueri dalam bentuk array asosiatif.
     */
    public function all()
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    /**
     * Mencari Data Berdasarkan Kunci Utama (Primary Key).
     *
     * Secara bawaan mencari data yang cocok pada kolom yang didefinisikan oleh $primaryKey.
     * Menggunakan parameter binding untuk mencegah SQL Injection.
     *
     * @param mixed $id Nilai unik dari kunci utama yang ingin dicari.
     *
     * @return array|false Mengembalikan baris data jika ditemukan, atau 'false' jika tidak ada kecocokan.
     */
    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Mengambil Data Berdasarkan Kondisi Spesifik.
     *
     * @param string $column Nama kolom tabel yang menjadi parameter pencarian.
     * @param string $operator Operator perbandingan (misal: '=', '>', '<', 'LIKE', dll).
     * @param mixed $value Nilai yang ingin dicocokkan atau dioperasikan dengan kolom.
     *
     * @return array Kumpulan baris hasil kueri dalam bentuk array asosiatif.
     */
    public function where($column, $operator, $value)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$column} {$operator} :val");
        $stmt->bindParam(':val', $value);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Menyimpan Data Baru ke dalam Tabel (Insert).
     *
     * Fungsi dinamis yang akan membangun kueri INSERT secara otomatis berdasarkan
     * pasangan kunci-nilai (key-value) dari array yang diberikan.
     * Menggunakan parameter binding untuk keamanan optimal.
     *
     * @param array $data Array asosiatif berisi [nama_kolom => nilai_baru].
     *
     * @return bool Menghasilkan nilai 'true' jika eksekusi kueri berhasil.
     */
    public function insert(array $data): bool
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(fn($k) => ':' . $k, array_keys($data)));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        return $stmt->execute();
    }

    /**
     * Memperbarui Data Berdasarkan Kunci Utama (Update).
     *
     * Membangun kueri UPDATE secara dinamis dari array asosiatif. Akan mengubah record
     * yang memiliki kunci utama (primary key) cocok dengan $id yang diberikan.
     *
     * @param mixed $id Nilai kunci utama (primary key) dari record yang akan diubah.
     * @param array $data Array asosiatif berisi kolom yang akan diubah nilainya [nama_kolom => nilai_baru].
     *
     * @return bool Menghasilkan nilai 'true' jika eksekusi pembaruan berhasil.
     */
    public function update($id, array $data): bool
    {
        $setParts = array_map(fn($k) => "{$k} = :{$k}", array_keys($data));
        $setString = implode(', ', $setParts);

        $sql = "UPDATE {$this->table} SET {$setString} WHERE {$this->primaryKey} = :pk_id";
        $stmt = $this->db->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':pk_id', $id);

        return $stmt->execute();
    }

    /**
     * Menghapus Data Berdasarkan Kunci Utama (Delete).
     *
     * Membangun kueri DELETE untuk menghapus satu baris data (record)
     * secara permanen dari tabel berdasarkan nilai kunci utama (primary key).
     *
     * @param mixed $id Nilai kunci utama dari record yang akan dihapus.
     *
     * @return bool Menghasilkan nilai 'true' jika proses penghapusan berhasil.
     */
    public function delete($id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Menghitung Jumlah Keseluruhan Data dalam Tabel.
     *
     * Menjalankan fungsi agregasi SQL COUNT(*) untuk menghitung total baris.
     *
     * @return int Mengembalikan jumlah data (record) berupa bilangan bulat (integer).
     */
    public function count(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
        $result = $stmt->fetch();
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Mengambil Satu Data (Baris) Pertama dari Tabel.
     *
     * Menggunakan perintah 'SELECT TOP 1' yang kompatibel dengan SQL Server
     * untuk mengambil baris urutan pertama tanpa kriteria penyaringan (filtering).
     *
     * @return array|null Baris data pertama dalam bentuk array asosiatif, atau 'null' jika tabel kosong.
     */
    public function first(): ?array
    {
        $stmt = $this->db->query("SELECT TOP 1 * FROM {$this->table}");
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
