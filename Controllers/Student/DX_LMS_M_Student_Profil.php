<?php
/**
 * Kelas Pengontrol Profil Pengguna (Student Profile Controller).
 *
 * Bertanggung jawab dalam mengelola antarmuka profil siswa, detail informasi diri,
 * pengaturan akun pengguna, riwayat masuk (login), serta halaman pusat bantuan.
 *
 * @package LMS
 * @subpackage Controllers\Student
 */
class DX_LMS_M_Student_Profil extends DX_LMS_M_Controller
{
    /**
     * Menampilkan Halaman Dasbor Profil Utama (Hub).
     *
     * Mengambil data statistik belajar, profil pengguna, dan memuat tampilan
     * utama untuk fitur profil yang mencakup gambaran umum akun siswa.
     *
     * @return void Merender antarmuka DX_LMS_M_Student_Profil.
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $studentUser = getPortalUser('student');
        if (!$studentUser) {
            header('Location: ?route=/login');
            exit();
        }
        $stats = DX_LMS_M_Student_Data::getStats();
        $profile = DX_LMS_M_Student_Data::getEmployeeProfile();
        $learningStats = DX_LMS_M_Student_Data::getLearningStats();
        $golonganMap = DX_LMS_M_Student_Data::getGolonganMap();
        $data = [
            'user' => $studentUser,
            'stats' => $stats,
            'profile' => $profile,
            'learningStats' => $learningStats,
            'golonganMap' => $golonganMap,
        ];
        $this->view('DX_LMS_M_Student_Profil', $data);
    }

    /**
     * Menampilkan Halaman Detail Profil Karyawan.
     *
     * Mengambil data lengkap profil karyawan, termasuk riwayat karier,
     * status golongan, dan ringkasan pembelajaran, kemudian merendernya
     * ke dalam antarmuka detail profil.
     *
     * @return void Merender antarmuka DX_LMS_M_Student_Profil_Detail.
     */
    public function detail()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $studentUser = getPortalUser('student');
        if (!$studentUser) {
            header('Location: ?route=/login');
            exit();
        }
        $stats = DX_LMS_M_Student_Data::getStats();
        $profile = DX_LMS_M_Student_Data::getEmployeeProfile();
        $careerHistory = DX_LMS_M_Student_Data::getCareerHistory();
        $golonganMap = DX_LMS_M_Student_Data::getGolonganMap();
        $learningStats = DX_LMS_M_Student_Data::getLearningStats();
        $data = [
            'user' => $studentUser,
            'stats' => $stats,
            'profile' => $profile,
            'careerHistory' => $careerHistory,
            'golonganMap' => $golonganMap,
            'learningStats' => $learningStats,
        ];
        $this->view('DX_LMS_M_Student_Profil_Detail', $data);
    }

    /**
     * Menampilkan Halaman Pengaturan Akun dan Keamanan.
     *
     * Mengambil riwayat akses (login history) pengguna serta pengaturan
     * preferensi akun lainnya, kemudian menampilkannya di halaman pengaturan.
     *
     * @return void Merender antarmuka DX_LMS_M_Student_Profil_Pengaturan.
     */
    public function pengaturan()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $studentUser = getPortalUser('student');
        if (!$studentUser) {
            header('Location: ?route=/login');
            exit();
        }
        $stats = DX_LMS_M_Student_Data::getStats();
        $profile = DX_LMS_M_Student_Data::getEmployeeProfile();
        $loginHistory = DX_LMS_M_Student_Data::getLoginHistory();
        $data = [
            'user' => $studentUser,
            'stats' => $stats,
            'profile' => $profile,
            'loginHistory' => $loginHistory,
        ];
        $this->view('DX_LMS_M_Student_Profil_Pengaturan', $data);
    }

    /**
     * Menampilkan Halaman Pusat Bantuan (Help Center).
     *
     * Memuat panduan pengguna, kontak dukungan (support), dan
     * informasi teknis aplikasi untuk membantu kendala pengguna.
     *
     * @return void Merender antarmuka DX_LMS_M_Student_Profil_Bantuan.
     */
    public function bantuan()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $studentUser = getPortalUser('student');
        if (!$studentUser) {
            header('Location: ?route=/login');
            exit();
        }
        $stats = DX_LMS_M_Student_Data::getStats();
        $data = [
            'user' => $studentUser,
            'stats' => $stats,
        ];
        $this->view('DX_LMS_M_Student_Profil_Bantuan', $data);
    }
}
