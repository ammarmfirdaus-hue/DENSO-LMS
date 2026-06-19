<?php
/**
 * Kelas Pengontrol Evaluasi dan Kuis (Student Quiz Controller).
 * 
 * Pusat logika untuk merender berbagai jenis evaluasi pembelajaran, termasuk 
 * Pre-Test, Post-Test, kuis reguler (Kuis Modul), dan Ujian Akhir (Final Assessment).
 */
class DX_LMS_T_Student_Quiz extends DX_LMS_Controller {
    
    /**
     * Menyiapkan dan Menampilkan Antarmuka Kuis/Ujian.
     * 
     * Mendeteksi jenis evaluasi (type) dari parameter URL, kemudian mencari
     * dan merender kumpulan soal yang relevan dari sesi atau pelatihan terkait.
     * Mencegah akses ke evaluasi yang statusnya masih 'terkunci'.
     * 
     * @return void Merender tampilan kuis, atau mengalihkan kembali ke rincian kursus jika tidak valid.
     */
    public function index() {
        session_start();

        $id = $_GET['id'] ?? '';
        $sessionId = $_GET['session_id'] ?? '';
        $type = $_GET['type'] ?? 'quiz'; // pre_test, post_test, quiz, final_assessment

        $course = DX_StudentData::getCourseById($id);

        if (!$course) {
            header('Location: ?route=/student/courses');
            exit;
        }

        $quizData = null;
        $session = null;

        if ($type === 'pre_test' || $type === 'post_test') {
            // Cari session
            foreach ($course['sessions'] as $s) {
                if ($s['id'] === $sessionId) {
                    $session = $s;
                    break;
                }
            }
            if (!$session) {
                header('Location: ?route=/student/course/detail&id=' . urlencode($id));
                exit;
            }
            $quizData = $session[$type];
        } elseif ($type === 'quiz') {
            $quizData = $course['quiz'];
        } elseif ($type === 'final_assessment') {
            $quizData = $course['final_assessment'];
        }

        if (!$quizData || $quizData['status'] === 'terkunci') {
            header('Location: ?route=/student/course/detail&id=' . urlencode($id));
            exit;
        }

        $data = [
            'course' => $course,
            'session' => $session,
            'quizData' => $quizData,
            'type' => $type
        ];

        $this->view('DX_LMS_T_Student_Quiz', $data);
    }
}
