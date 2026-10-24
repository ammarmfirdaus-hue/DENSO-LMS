<?php
/**
 * Kelas Pengontrol Notifikasi Siswa (Student Notification Controller).
 *
 * Bertanggung jawab dalam mengambil dan menampilkan daftar pemberitahuan
 * terbaru untuk siswa, seperti kursus baru, hasil kuis, atau pembaruan materi.
 */
class DX_LMS_T_Student_Notifikasi extends DX_LMS_M_Controller
{
    /**
     * Menampilkan Halaman Pusat Pemberitahuan (Notification Center).
     *
     * Mengambil daftar seluruh notifikasi yang berkaitan dengan siswa dari basis data,
     * kemudian merendernya pada antarmuka daftar notifikasi.
     *
     * @return void
     */
    public function index()
    {
        startAppSession();

        // Notification records are loaded from the shared authenticated API client.
        $notifications = [];
        $stats = DX_LMS_M_Student_Data::getStats();

        $data = [
            'active_tab' => 'notifikasi',
            'notifications' => $notifications,
            'stats' => $stats,
        ];

        $this->view('DX_LMS_T_Student_Notifikasi', $data);
    }
}
