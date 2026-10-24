<?php
/**
 * Kelas Pengontrol Evaluasi dan Kuis (Student Quiz Controller).
 *
 * Pusat logika untuk merender berbagai jenis evaluasi pembelajaran, termasuk
 * Pre-Test, Post-Test, kuis reguler (Kuis Modul), dan Ujian Akhir (Final Assessment).
 */
class DX_LMS_T_Student_Quiz extends DX_LMS_M_Controller
{
    /**
     * Menyiapkan dan Menampilkan Antarmuka Kuis/Ujian.
     *
     * Mendeteksi jenis evaluasi (type) dari parameter URL, kemudian mencari
     * dan merender kumpulan soal yang relevan dari sesi atau pelatihan terkait.
     * Mencegah akses ke evaluasi yang statusnya masih 'terkunci'.
     *
     * @return void Merender tampilan kuis, atau mengalihkan kembali ke rincian kursus jika tidak valid.
     */
    public function index()
    {
        startAppSession();

        $id = $_GET['id'] ?? '';
        $sessionId = $_GET['session_id'] ?? '';
        $type = $_GET['type'] ?? 'quiz'; /** Jenis evaluasi: evaluasi awal, evaluasi akhir, kuis, dan penilaian akhir. */
        $attemptRaw = $_GET['attempt'] ?? null;
        if (!is_string($id) || strlen($id) > 80 || !is_string($sessionId) || strlen($sessionId) > 80
            || !is_string($type) || !in_array($type, ['pre_test', 'post_test', 'quiz', 'final_assessment'], true)) {
            header('Location: ?route=/student/courses');
            exit();
        }
        $attemptId = is_string($attemptRaw) && ctype_digit($attemptRaw) ? (int) $attemptRaw : null;

        $course = DX_LMS_M_Student_Data::getCourseById($id);

        if (!$course) {
            header('Location: ?route=/student/courses');
            exit();
        }

        $quizData = null;
        $session = null;

        if ($type === 'pre_test' || $type === 'post_test') {
            /** Melakukan pencarian data sesi pembelajaran. */
            foreach ($course['sessions'] as $s) {
                if ($s['id'] === $sessionId) {
                    $session = $s;
                    break;
                }
            }
            if (!$session) {
                header('Location: ?route=/student/course/detail&id=' . urlencode($id));
                exit();
            }
            $quizData = $session[$type];
        } elseif ($type === 'quiz') {
            $quizData = $course['quiz'];
        } elseif ($type === 'final_assessment') {
            $quizData = $course['final_assessment'];
        }

        /** Memeriksa kelengkapan sesi guna membuka akses penilaian akhir secara dinamis. */
        $all_sessions_completed = true;
        if (!empty($course['sessions'])) {
            foreach ($course['sessions'] as $s) {
                if ($s['status'] !== 'selesai') {
                    $all_sessions_completed = false;
                    break;
                }
            }
        } else {
            $all_sessions_completed = false;
        }

        /** Membuka akses penilaian akhir jika seluruh prasyarat (sesi dan kuis) telah diselesaikan. */
        if ($type === 'final_assessment' && $quizData && $quizData['status'] === 'terkunci') {
            $isQuizCompleted = empty($course['quiz']) || $course['quiz']['status'] === 'selesai';
            if ($all_sessions_completed && $isQuizCompleted) {
                $quizData['status'] = 'belum_dimulai';
            }
        }

        if (!$quizData || $quizData['status'] === 'terkunci') {
            header('Location: ?route=/student/course/detail&id=' . urlencode($id));
            exit();
        }

        /** Mengambil data lengkap soal dari bank soal berdasarkan ID yang diberikan. */
        $bank = DX_LMS_M_Student_Data::getQuestionBank();
        $fullQuestions = [];
        if (!empty($quizData['questions'])) {
            foreach ($quizData['questions'] as $qId) {
                if (isset($bank[$qId])) {
                    $q = $bank[$qId];
                    /** Menerapkan aturan bisnis: evaluasi awal dan akhir hanya menggunakan format Pilihan Ganda. */
                    if (
                        ($type === 'pre_test' || $type === 'post_test') &&
                        in_array($q['type'], ['essay', 'essay_image'])
                    ) {
                        continue;
                    }
                    $fullQuestions[] = $q;
                }
            }
        }

        /** Mengurutkan daftar soal: format Pilihan Ganda ditampilkan sebelum soal uraian (Essay). */
        usort($fullQuestions, function ($a, $b) {
            $isEssayA = in_array($a['type'], ['essay', 'essay_image']) ? 1 : 0;
            $isEssayB = in_array($b['type'], ['essay', 'essay_image']) ? 1 : 0;
            /** Mengurutkan daftar berdasarkan identitas (ID) jika format soal sama. */
            if ($isEssayA === $isEssayB) {
                return $a['id'] <=> $b['id'];
            }
            return $isEssayA <=> $isEssayB;
        });

        $quizData['full_questions'] = $fullQuestions;

        /** Mengaktifkan mode riwayat (hanya-baca). */
        $isHistoryMode = false;
        $historyAttempt = null;
        if ($attemptId !== null && !empty($quizData['history'])) {
            foreach ($quizData['history'] as $hist) {
                if ($hist['attempt'] === $attemptId) {
                    $isHistoryMode = true;
                    $historyAttempt = $hist;
                    /** Memasukkan data jawaban sementara jika belum terdapat data jawaban pengguna. */
                    if (!isset($historyAttempt['user_answers'])) {
                        $userAnswers = [];
                        foreach ($fullQuestions as $q) {
                            if ($q['type'] === 'pg' || $q['type'] === 'pg_image') {
                                /** Konfigurasi data sementara: mengatur probabilitas kebenaran sebesar 80%. */
                                $userAnswers[$q['id']] = rand(1, 10) > 2 ? $q['correct_answer'] : 'A';
                            } else {
                                $userAnswers[$q['id']] = 'Ini adalah jawaban essay dari student.';
                            }
                        }
                        $historyAttempt['user_answers'] = $userAnswers;
                        $historyAttempt['review_status'] = 'under_review'; /** Konfigurasi status sementara untuk soal uraian. */
                    }
                    break;
                }
            }
        }

        $data = [
            'course' => $course,
            'session' => $session,
            'quizData' => $quizData,
            'type' => $type,
            'isHistoryMode' => $isHistoryMode,
            'historyAttempt' => $historyAttempt,
        ];

        $this->view('DX_LMS_T_Student_Quiz', $data);
    }
}
