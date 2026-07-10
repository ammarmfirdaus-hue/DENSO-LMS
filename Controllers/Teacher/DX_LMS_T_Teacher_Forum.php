<?php
/**
 * Kelas Pengontrol Forum Pengajar (Teacher Forum Controller).
 *
 * Mengelola interaksi pengajar di dalam forum diskusi (membalas pesan, dll).
 */
class DX_LMS_T_Teacher_Forum extends DX_LMS_M_Controller
{
    public function index()
    {
        session_start();
        if (!isset($_SESSION['user']) || $_SESSION['active_role'] !== 'teacher') {
            header('Location: ?route=/login');
            exit();
        }

        $sessionId = $_GET['session_id'] ?? '';

        $data = [
            'active_tab' => 'course',
            'base_path' => '',
            'sessionId' => $sessionId,
            'threads' => [
                [
                    'id' => 1,
                    'author' => 'Budi Santoso',
                    'avatar' => '',
                    'time' => '2 jam yang lalu',
                    'title' => 'Pertanyaan Modul 1',
                    'content' => 'Pak, saya kurang paham bagian kalkulasi OEE.',
                    'replies' => 0
                ]
            ]
        ];

        $this->view('DX_LMS_T_Teacher_Forum', $data);
    }
}
