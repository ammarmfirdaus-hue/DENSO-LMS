<?php
/**
 * Kelas Pengontrol Notifikasi Siswa (Student Notification Controller).
 * 
 * Bertanggung jawab dalam mengambil dan menampilkan daftar pemberitahuan 
 * terbaru untuk siswa, seperti kursus baru, hasil kuis, atau pembaruan materi.
 */
class DX_LMS_T_Student_Notifikasi extends DX_LMS_Controller {
    
    /**
     * Menampilkan Halaman Pusat Pemberitahuan (Notification Center).
     * 
     * Mengambil daftar seluruh notifikasi yang berkaitan dengan siswa dari basis data,
     * kemudian merendernya pada antarmuka daftar notifikasi.
     * 
     * @return void
     */
    public function index() {
        session_start();

        // Ambil notifikasi dari Single Source of Truth
        $notifications = DX_StudentData::getNotifications();
        $stats = DX_StudentData::getStats();

        $data = [
            'active_tab'    => 'notifikasi',
            'notifications' => $notifications,
            'stats'         => $stats
        ];

        $this->view('DX_LMS_T_Student_Notifikasi', $data);
    }
}
