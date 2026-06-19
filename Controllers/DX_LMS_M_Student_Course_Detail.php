<?php
/**
 * Controller for Course Detail
 */
class DX_LMS_M_Student_Course_Detail extends DX_LMS_Controller {

    /**
     * Menampilkan halaman detail course beserta tab Materi, Quiz, dan Forum.
     * Memuat data course berdasarkan ID, mengevaluasi status sesi, dan menentukan tab aktif.
     */
    public function index() {
        session_start();

        // Ambil course_id dari query string, default CRS001
        $courseId = $_GET['id'] ?? 'CRS001';
        $tab = $_GET['tab'] ?? 'materi';
        $course = DX_StudentData::getCourseById($courseId);

        // Fallback ke CRS001 jika ID tidak ditemukan
        if (!$course) {
            $course = DX_StudentData::getCourseById('CRS001');
        }

        // Cek apakah semua sesi telah selesai
        $all_sessions_completed = true;
        if (!empty($course['sessions'])) {
            foreach ($course['sessions'] as $session) {
                if ($session['status'] !== 'selesai') {
                    $all_sessions_completed = false;
                    break;
                }
            }
        } else {
            $all_sessions_completed = false; // Jika tidak ada sesi, dianggap belum selesai (atau sesuai logic bisnis)
        }

        $data = [
            'course' => $course,
            'tab'    => $tab,
            'all_sessions_completed' => $all_sessions_completed,
        ];

        $this->view('DX_LMS_M_Student_Course_Detail', $data);
    }
}
