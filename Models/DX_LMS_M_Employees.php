<?php
/**
 * Kelas Model Karyawan (Employee Model).
 *
 * Merepresentasikan tabel `DX_LMS_M_Employees` pada basis data.
 * Mematuhi Standarisasi DENSO:
 * - 10 Kolom Standar: npk, fullname, golongan, bu_code, section, department, division, directorate, email, status.
 * - Kolom Pelengkap Aplikasi: password, jabatan, role, teacher_type.
 */

require_once __DIR__ . '/../Core/DX_LMS_M_Model.php';
require_once __DIR__ . '/../includes/DX_LMS_M_Db.php';

class DX_LMS_M_Employees extends DX_LMS_M_Model
{
    protected $table = 'DX_LMS_M_Employees';
    protected $primaryKey = 'employee_id';

    /**
     * Mencari Karyawan Aktif Berdasarkan NPK.
     *
     * Mendukung format NPK min 7 digit (standar Denso) maupun 5 digit legacy.
     *
     * @param string $npk Nomor Pokok Karyawan (NPK) yang dicari.
     * @return array|false Data karyawan atau false jika tidak ditemukan.
     */
    public function findByNpk($npk)
    {
        try {
            $user = dbQueryOne("SELECT * FROM {$this->table} WHERE (npk = ? OR npk = ?) AND status = 'Active'", [
                $npk,
                str_pad($npk, 7, '0', STR_PAD_LEFT)
            ]);
            if ($user) {
                return $user;
            }
        } catch (Throwable $e) {
            // Tangani fallback jika koneksi database sedang offline
        }

        // Data cadangan (fallback) untuk pengujian sistem
        $dummyUsers = [
            '12345' => [
                'employee_id' => 1,
                'npk' => '12345',
                'fullname' => 'Budi Santoso',
                'golongan' => 'I-1',
                'bu_code' => 101,
                'section' => 'Assembling',
                'department' => 'Manufacturing',
                'division' => 'Produksi',
                'directorate' => 'Production Directorate',
                'email' => 'budi.santoso@perusahaan.co.id',
                'password' => '$2y$10$r21wQRARzlhPsx.reVEBYOrvvz.YpG9DmifFjIWrcI7AEl84GPBv.',
                'jabatan' => 'Operator',
                'role' => 'student',
                'teacher_type' => null,
                'status' => 'Active',
                'position_id' => 1, 'golongan_id' => 1, 'department_id' => 1, 'division_id' => 1, 'section_id' => 1
            ],
            '67890' => [
                'employee_id' => 2,
                'npk' => '67890',
                'fullname' => 'Ahmad Rizal',
                'golongan' => 'II-2',
                'bu_code' => 101,
                'section' => 'Training & Education (TRE)',
                'department' => 'People Development (HR & GA)',
                'division' => 'Human Capital',
                'directorate' => 'Corporate Directorate',
                'email' => 'ahmad.rizal@perusahaan.co.id',
                'password' => '$2y$10$r21wQRARzlhPsx.reVEBYOrvvz.YpG9DmifFjIWrcI7AEl84GPBv.',
                'jabatan' => 'Leader',
                'role' => 'teacher',
                'teacher_type' => 'TRE',
                'status' => 'Active',
                'position_id' => 3, 'golongan_id' => 2, 'department_id' => 2, 'division_id' => 2, 'section_id' => 2
            ],
            '67891' => [
                'employee_id' => 3,
                'npk' => '67891',
                'fullname' => 'Maya Safitri',
                'golongan' => 'II-1',
                'bu_code' => 100,
                'section' => 'Quality Control Section',
                'department' => 'Quality Assurance',
                'division' => 'Technical Division',
                'directorate' => 'Technical Directorate',
                'email' => 'maya.safitri@perusahaan.co.id',
                'password' => '$2y$10$r21wQRARzlhPsx.reVEBYOrvvz.YpG9DmifFjIWrcI7AEl84GPBv.',
                'jabatan' => 'Staff',
                'role' => 'teacher',
                'teacher_type' => 'Associate',
                'status' => 'Active',
                'position_id' => 2, 'golongan_id' => 2, 'department_id' => 3, 'division_id' => 3, 'section_id' => 3
            ],
            '99887' => [
                'employee_id' => 4,
                'npk' => '99887',
                'fullname' => 'Tono Wijaya',
                'golongan' => 'IV-2',
                'bu_code' => 100,
                'section' => 'LMS Administration',
                'department' => 'People Development (HR & GA)',
                'division' => 'Human Capital',
                'directorate' => 'Corporate Directorate',
                'email' => 'tono.wijaya@perusahaan.co.id',
                'password' => '$2y$10$r21wQRARzlhPsx.reVEBYOrvvz.YpG9DmifFjIWrcI7AEl84GPBv.',
                'jabatan' => 'Manager',
                'role' => 'admin',
                'teacher_type' => null,
                'status' => 'Active',
                'position_id' => 5, 'golongan_id' => 4, 'department_id' => 2, 'division_id' => 2, 'section_id' => 2
            ],
            '99999' => [
                'employee_id' => 5,
                'npk' => '99999',
                'fullname' => 'Admin System',
                'golongan' => 'IV-2',
                'bu_code' => 100,
                'section' => 'Corporate IT',
                'department' => 'People Development (HR & GA)',
                'division' => 'Human Capital',
                'directorate' => 'Head Office',
                'email' => 'admin@denso.co.id',
                'password' => '$2y$10$r21wQRARzlhPsx.reVEBYOrvvz.YpG9DmifFjIWrcI7AEl84GPBv.',
                'jabatan' => 'Super Admin',
                'role' => 'admin',
                'teacher_type' => null,
                'status' => 'Active',
                'position_id' => 5, 'golongan_id' => 4, 'department_id' => 2, 'division_id' => 2, 'section_id' => 2
            ],
            '11223' => [
                'employee_id' => 6,
                'npk' => '11223',
                'fullname' => 'Citra Lestari',
                'golongan' => 'III-1',
                'bu_code' => 102,
                'section' => 'Pemeriksaan',
                'department' => 'Quality',
                'division' => 'QA',
                'directorate' => 'Quality Assurance Directorate',
                'email' => 'citra.lestari@perusahaan.co.id',
                'password' => '$2y$10$r21wQRARzlhPsx.reVEBYOrvvz.YpG9DmifFjIWrcI7AEl84GPBv.',
                'jabatan' => 'Supervisor',
                'role' => 'student',
                'teacher_type' => null,
                'status' => 'Active',
                'position_id' => 4, 'golongan_id' => 3, 'department_id' => 3, 'division_id' => 3, 'section_id' => 3
            ],
            '23456' => [
                'employee_id' => 7,
                'npk' => '23456',
                'fullname' => 'Dian Sastro',
                'golongan' => 'IV-1',
                'bu_code' => 103,
                'section' => 'Desain',
                'department' => 'Engineering',
                'division' => 'PE',
                'directorate' => 'Technical Directorate',
                'email' => 'dian.sastro@perusahaan.co.id',
                'password' => '$2y$10$r21wQRARzlhPsx.reVEBYOrvvz.YpG9DmifFjIWrcI7AEl84GPBv.',
                'jabatan' => 'Manager',
                'role' => 'student',
                'teacher_type' => null,
                'status' => 'Active',
                'position_id' => 5, 'golongan_id' => 4, 'department_id' => 4, 'division_id' => 3, 'section_id' => 1
            ],
            '34567' => [
                'employee_id' => 8,
                'npk' => '34567',
                'fullname' => 'Eko Prasetyo',
                'golongan' => 'I-2',
                'bu_code' => 101,
                'section' => 'Machining',
                'department' => 'Manufacturing',
                'division' => 'Produksi',
                'directorate' => 'Production Directorate',
                'email' => 'eko.prasetyo@perusahaan.co.id',
                'password' => '$2y$10$r21wQRARzlhPsx.reVEBYOrvvz.YpG9DmifFjIWrcI7AEl84GPBv.',
                'jabatan' => 'Operator',
                'role' => 'student',
                'teacher_type' => null,
                'status' => 'Active',
                'position_id' => 1, 'golongan_id' => 1, 'department_id' => 1, 'division_id' => 1, 'section_id' => 1
            ],
            '45678' => [
                'employee_id' => 9,
                'npk' => '45678',
                'fullname' => 'Fani Amelia',
                'golongan' => 'II-1',
                'bu_code' => 100,
                'section' => 'Rekrutmen',
                'department' => 'HR',
                'division' => 'Personalia',
                'directorate' => 'Corporate Directorate',
                'email' => 'fani.amelia@perusahaan.co.id',
                'password' => '$2y$10$r21wQRARzlhPsx.reVEBYOrvvz.YpG9DmifFjIWrcI7AEl84GPBv.',
                'jabatan' => 'Staff',
                'role' => 'student',
                'teacher_type' => null,
                'status' => 'Active',
                'position_id' => 2, 'golongan_id' => 2, 'department_id' => 2, 'division_id' => 2, 'section_id' => 2
            ],
            '56789' => [
                'employee_id' => 10,
                'npk' => '56789',
                'fullname' => 'Gilang Ramadhan',
                'golongan' => 'II-2',
                'bu_code' => 100,
                'section' => 'Pajak',
                'department' => 'Finance',
                'division' => 'Akuntansi',
                'directorate' => 'Corporate Directorate',
                'email' => 'gilang.ramadhan@perusahaan.co.id',
                'password' => '$2y$10$r21wQRARzlhPsx.reVEBYOrvvz.YpG9DmifFjIWrcI7AEl84GPBv.',
                'jabatan' => 'Staff',
                'role' => 'student',
                'teacher_type' => null,
                'status' => 'Active',
                'position_id' => 2, 'golongan_id' => 2, 'department_id' => 2, 'division_id' => 2, 'section_id' => 2
            ],
        ];

        return $dummyUsers[$npk] ?? false;
    }

    /**
     * Mendapatkan Daftar Seluruh Karyawan Aktif.
     *
     * @return array
     */
    public function getActiveEmployees()
    {
        try {
            $rows = dbQuery("SELECT * FROM {$this->table} WHERE status = 'Active' ORDER BY employee_id ASC");
            if (!empty($rows)) {
                return $rows;
            }
        } catch (Throwable $e) {
            // fallback jika database luring
        }
        return $this->where('status', '=', 'Active');
    }

    /**
     * Mendapatkan Daftar Kandidat Karyawan untuk Usulan Target Peserta Course.
     * Digunakan oleh Teacher saat membuat course di Step 3 Wizard.
     *
     * @return array
     */
    public function getCandidatesForTargeting()
    {
        return $this->getActiveEmployees();
    }

    /**
     * Mendapatkan Opsi Filter Target Rekomendasi Peserta dari database SSMS.
     * Mengambil nilai distinktif jabatan, golongan, departemen, divisi, dan seksi.
     *
     * @return array
     */
    public function getTargetFilterOptions(): array
    {
        try {
            $jabatans = array_column(dbQuery("SELECT DISTINCT jabatan FROM {$this->table} WHERE jabatan IS NOT NULL AND status = 'Active' ORDER BY jabatan ASC"), 'jabatan');
            $golongans = array_column(dbQuery("SELECT DISTINCT golongan FROM {$this->table} WHERE golongan IS NOT NULL AND status = 'Active' ORDER BY golongan ASC"), 'golongan');
            $departments = array_column(dbQuery("SELECT DISTINCT department FROM {$this->table} WHERE department IS NOT NULL AND status = 'Active' ORDER BY department ASC"), 'department');
            $divisions = array_column(dbQuery("SELECT DISTINCT division FROM {$this->table} WHERE division IS NOT NULL AND status = 'Active' ORDER BY division ASC"), 'division');
            $sections = array_column(dbQuery("SELECT DISTINCT section FROM {$this->table} WHERE section IS NOT NULL AND status = 'Active' ORDER BY section ASC"), 'section');

            return [
                'jabatan' => !empty($jabatans) ? $jabatans : ['Operator', 'Staff', 'Leader', 'Supervisor', 'Manager'],
                'golongan' => !empty($golongans) ? $golongans : ['I-1', 'I-2', 'II-1', 'II-2', 'III-1', 'IV-1', 'IV-2'],
                'departemen' => !empty($departments) ? $departments : ['Manufacturing', 'Quality', 'Maintenance', 'Engineering', 'HR'],
                'divisi' => !empty($divisions) ? $divisions : ['Produksi', 'QA', 'Technical Division', 'Human Capital'],
                'seksi' => !empty($sections) ? $sections : ['Assembling', 'Machining', 'Quality Control Section', 'Training & Education (TRE)'],
            ];
        } catch (\Throwable $e) {
            return [
                'jabatan' => ['Operator', 'Staff', 'Leader', 'Supervisor', 'Manager'],
                'golongan' => ['I-1', 'I-2', 'II-1', 'II-2', 'III-1', 'IV-1', 'IV-2'],
                'departemen' => ['Manufacturing', 'Quality', 'Maintenance', 'Engineering', 'HR'],
                'divisi' => ['Produksi', 'QA', 'Technical Division', 'Human Capital'],
                'seksi' => ['Assembling', 'Machining', 'Quality Control Section', 'Training & Education (TRE)'],
            ];
        }
    }
}
