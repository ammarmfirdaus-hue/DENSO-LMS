<?php
require_once 'DX_LMS_M_Database.php';

/**
 * Base ORM Model
 * Menyediakan fungsi-fungsi dasar ORM seperti find, all, where
 */
abstract class DX_LMS_M_Model {
    protected $table;
    protected $primaryKey = 'id';
    protected $db;

    /**
     * Metode __construct
     * Didokumentasikan sesuai Panduan Program Standar.
     */
    public function __construct() {
        $this->db = DX_LMS_M_Database::getInstance()->getConnection();
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

    // Fungsi insert, update, delete dapat ditambahkan di sini
}
