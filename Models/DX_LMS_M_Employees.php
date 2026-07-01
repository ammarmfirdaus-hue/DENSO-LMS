<?php
/**
 * Kelas Model Karyawan (Employee Model).
 *
 * Merepresentasikan tabel `DX_LMS_M_Employees` pada basis data.
 * Kelas ini bertanggung jawab atas semua operasi basis data yang berkaitan
 * dengan data induk karyawan (pengguna sistem).
 */
class DX_LMS_M_Employees extends DX_LMS_M_Model
{
    protected $table = 'DX_LMS_M_Employees';
    protected $primaryKey = 'npk';

    /**
     * Mencari Karyawan Aktif Berdasarkan NPK.
     *
     * Metode ini spesifik digunakan untuk proses autentikasi (login).
     * Hanya akan mengembalikan data karyawan yang memiliki status 'Active'.
     *
     * @param string $npk Nomor Pokok Karyawan (NPK) yang dicari.
     * @return array|false Mengembalikan *array* asosiatif berisi data karyawan jika ditemukan,
     *                     atau `false` jika data tidak ditemukan atau statusnya tidak aktif.
     */
    public function findByNpk($npk)
    {
        $db = DX_LMS_M_Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM {$this->table} WHERE npk = :npk AND status = 'Active'");
        $stmt->bindParam(':npk', $npk);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Mendapatkan Daftar Seluruh Karyawan Aktif.
     *
     * Mengambil kumpulan data karyawan yang saat ini berstatus 'Active'
     * di dalam perusahaan.
     *
     * @return array Mengembalikan array data berisi daftar karyawan aktif.
     */
    public function getActiveEmployees()
    {
        return $this->where('status', '=', 'Active');
    }
}
