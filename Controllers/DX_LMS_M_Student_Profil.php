<?php
/**
 * Kelas Pengontrol Profil Pengguna (Student Profile Controller).
 * 
 * Bertanggung jawab dalam mengelola antarmuka profil siswa. Menyediakan akses
 * untuk melihat informasi detail akun (NPK, Nama, Golongan) dan pengaturan terkait profil.
 */
class DX_LMS_M_Student_Profil extends DX_LMS_Controller {
    
    /**
     * Menampilkan Halaman Profil Siswa.
     * 
     * Merender antarmuka yang berisi rincian informasi pengguna yang sedang login.
     * Memanfaatkan data yang ada di dalam variabel sesi (Session) pengguna.
     * 
     * @return void Merender tampilan halaman profil.
     */
    public function index() {
        session_start();
        $stats = DX_StudentData::getStats();
        $data = ['stats' => $stats];
        $this->view('DX_LMS_M_Student_Profil', $data);
    }
}
