<?php
/**
 * Kelas Pengontrol Kategori Forum Diskusi (Forum Category Controller).
 *
 * Bertanggung jawab dalam mengelola dan menampilkan daftar diskusi forum
 * berdasarkan kategori tertentu (Materi Sesi, Kuis, atau Ujian Akhir).
 * Mendukung navigasi dari halaman pelatihan aktif maupun riwayat.
 */
class DX_LMS_T_Student_Forum_Category extends DX_LMS_M_Controller
{
    /**
     * Menampilkan Halaman Daftar Diskusi per Kategori Forum.
     *
     * Mengambil parameter kategori (materi/quiz/final_assessment) dari URL,
     * memvalidasi ketersediaan data, kemudian merender daftar thread forum
     * yang relevan dengan kategori dan sesi yang dipilih.
     *
     * @return void Merender antarmuka daftar diskusi forum, atau mengalihkan jika data tidak valid.
     */
    public function index()
    {
        $courseId = $_GET['course_id'] ?? '';
        $categoryType = $_GET['category_type'] ?? ''; /** Mengatur jenis modul: materi, kuis, atau penilaian akhir. */
        $sessionId = $_GET['session_id'] ?? '';
        $isRiwayat = isset($_GET['is_riwayat']) && $_GET['is_riwayat'] === 'true';

        /** Melakukan validasi awal pada data masukan. */
        if (!is_string($courseId) || strlen($courseId) > 80
            || !is_string($categoryType) || !in_array($categoryType, ['materi', 'quiz', 'final_assessment'], true)
            || !is_string($sessionId) || strlen($sessionId) > 80 || $courseId === '') {
            header('Location: ?route=' . ($isRiwayat ? '/student/riwayat' : '/student/courses'));
            exit();
        }

        /** Mengambil rincian informasi kursus. */
        $course = DX_LMS_M_Student_Data::getCourseById($courseId);

        if (!$course) {
            header('Location: ?route=' . ($isRiwayat ? '/student/riwayat' : '/student/courses'));
            exit();
        }

        /** Mengambil data diskusi forum berdasarkan tipe yang dipilih. */
        $forumData = null;
        $categoryTitle = '';
        $isLocked = false;
        $hasCreatedThread = false;

        if ($categoryType === 'materi') {
            if (empty($sessionId)) {
                header('Location: ?route=/student/course/detail&id=' . urlencode($courseId) . '&tab=forum');
                exit();
            }

            /** Melakukan pencarian data sesi. */
            $sessionData = null;
            foreach ($course['sessions'] as $sess) {
                if ($sess['id'] === $sessionId) {
                    $sessionData = $sess;
                    break;
                }
            }

            if (!$sessionData) {
                header('Location: ?route=/student/course/detail&id=' . urlencode($courseId) . '&tab=forum');
                exit();
            }

            $categoryTitle = $sessionData['title'];
            $isLocked = $sessionData['status'] !== 'selesai' && !$isRiwayat;

            $forumsMateri = $course['forums']['materi'] ?? [];
            $forumData = $forumsMateri[$sessionId] ?? ['has_created_thread' => false, 'threads' => []];
            $hasCreatedThread = $forumData['has_created_thread'] ?? false;
        } elseif ($categoryType === 'quiz') {
            $categoryTitle = 'Forum Quiz Course';
            $quizStatus = $course['quiz']['status'] ?? 'terkunci';
            $isLocked = $quizStatus !== 'selesai' && !$isRiwayat;
            $forumData = $course['forums']['quiz'] ?? ['threads' => [], 'has_created_thread' => false];
            $hasCreatedThread = $forumData['has_created_thread'] ?? false;
        } elseif ($categoryType === 'final_assessment') {
            $categoryTitle = 'Forum Final Assessment';
            $finalStatus = $course['final_assessment']['status'] ?? 'terkunci';
            $isLocked = $finalStatus !== 'selesai' && !$isRiwayat;
            $forumData = $course['forums']['final_assessment'] ?? ['threads' => [], 'has_created_thread' => false];
            $hasCreatedThread = $forumData['has_created_thread'] ?? false;
        } else {
            header('Location: ?route=/student/course/detail&id=' . urlencode($courseId) . '&tab=forum');
            exit();
        }

        $threads = $forumData['threads'] ?? [];

        $this->view('DX_LMS_T_Student_Forum_Category', [
            'courseId' => $courseId,
            'categoryType' => $categoryType,
            'sessionId' => $sessionId,
            'isRiwayat' => $isRiwayat,
            'course' => $course,
            'categoryTitle' => $categoryTitle,
            'isLocked' => $isLocked,
            'hasCreatedThread' => $hasCreatedThread,
            'threads' => $threads
        ]);
    }
}
