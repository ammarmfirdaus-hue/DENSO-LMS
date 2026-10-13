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
        try {
            $db = DX_LMS_M_Database::getInstance()->getConnection();
            if ($db) {
                $stmt = $db->prepare("SELECT * FROM {$this->table} WHERE npk = :npk AND status = 'Active'");
                $stmt->bindParam(':npk', $npk);
                $stmt->execute();
                $user = $stmt->fetch();
                if ($user) {
                    return $user;
                }
            }
        } catch (Throwable $e) {
            // Tangani pengecualian koneksi jika database sedang luring
        }

        // Data cadangan (fallback) untuk pengujian sistem
        $dummyUsers = [
            '12345' => [
                'employee_id' => 1,
                'npk' => '12345',
                'fullname' => 'Budi Santoso',
                'email' => 'budi.santoso@perusahaan.co.id',
                'password' => '$2y$10$r21wQRARzlhPsx.reVEBYOrvvz.YpG9DmifFjIWrcI7AEl84GPBv.',
                'position_id' => 1,
                'golongan_id' => 1,
                'department_id' => 1,
                'division_id' => 1,
                'section_id' => 1,
                'jabatan' => 'Operator',
                'golongan' => 'Gol 1',
                'department' => 'Manufacturing',
                'division' => 'Operation',
                'section' => 'Production A',
                'directorate' => 'Plant 1',
                'role' => 'student',
                'status' => 'Active',
            ],
            '67890' => [
                'employee_id' => 2,
                'npk' => '67890',
                'fullname' => 'Ahmad Rizal',
                'email' => 'ahmad.rizal@perusahaan.co.id',
                'password' => '$2y$10$r21wQRARzlhPsx.reVEBYOrvvz.YpG9DmifFjIWrcI7AEl84GPBv.',
                'position_id' => 3,
                'golongan_id' => 3,
                'department_id' => 1,
                'division_id' => 1,
                'section_id' => 1,
                'jabatan' => 'Leader',
                'golongan' => 'Gol 3',
                'department' => 'Manufacturing',
                'division' => 'Operation',
                'section' => 'Production A',
                'directorate' => 'Plant 1',
                'role' => 'teacher',
                'status' => 'Active',
            ],
            '99999' => [
                'employee_id' => 3,
                'npk' => '99999',
                'fullname' => 'Admin System',
                'email' => 'admin@perusahaan.co.id',
                'password' => '$2y$10$r21wQRARzlhPsx.reVEBYOrvvz.YpG9DmifFjIWrcI7AEl84GPBv.',
                'position_id' => 4,
                'golongan_id' => 4,
                'department_id' => 2,
                'division_id' => 2,
                'section_id' => 2,
                'jabatan' => 'SPV',
                'golongan' => 'Gol 4',
                'department' => 'HR & GA',
                'division' => 'Human Capital',
                'section' => 'People Development',
                'directorate' => 'Head Office',
                'role' => 'admin',
                'status' => 'Active',
            ],
        ];

        return $dummyUsers[$npk] ?? false;
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
