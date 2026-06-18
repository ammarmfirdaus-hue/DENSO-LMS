<?php
require_once 'DX_LMS_Database.php';

/**
 * Base ORM Model
 * Menyediakan fungsi-fungsi dasar ORM seperti find, all, where
 */
abstract class DX_LMS_Model {
    protected $table;
    protected $primaryKey = 'id';
    protected $db;

    /**
     * Inisialisasi Model dengan koneksi database.
     * Mengambil instance PDO dari DX_LMS_Database (Singleton).
     */
    public function __construct() {
        $this->db = DX_LMS_Database::getInstance()->getConnection();
    }

    /**
     * Mendapatkan semua data dari tabel
     */
    public function all() {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    /**
     * Mencari data berdasarkan Primary Key
     */
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Mencari data dengan kondisi tertentu
     * @param string $column Nama kolom
     * @param string $operator Operator (=, >, <, dll)
     * @param mixed $value Nilai yang dicari
     */
    public function where($column, $operator, $value) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$column} {$operator} :val");
        $stmt->bindParam(':val', $value);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Menyimpan data baru ke dalam tabel
     * @param array $data Associative array [column => value]
     * @return bool True jika insert berhasil
     */
    public function insert(array $data): bool {
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
     * Memperbarui data berdasarkan Primary Key
     * @param mixed $id Nilai Primary Key dari record yang akan diupdate
     * @param array $data Associative array [column => value] berisi kolom yang diubah
     * @return bool True jika update berhasil
     */
    public function update($id, array $data): bool {
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
     * Menghapus data berdasarkan Primary Key
     * @param mixed $id Nilai Primary Key dari record yang akan dihapus
     * @return bool True jika delete berhasil
     */
    public function delete($id): bool {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Menghitung jumlah total record dalam tabel
     * @return int Jumlah record
     */
    public function count(): int {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
        $result = $stmt->fetch();
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Mengambil satu record pertama dari tabel
     * @return array|null Data record atau null jika kosong
     */
    public function first(): ?array {
        $stmt = $this->db->query("SELECT TOP 1 * FROM {$this->table}");
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
