<?php
/**
 * Kelas Pengontrol Rincian Pelatihan (Course Detail Controller).
 *
 * Bertanggung jawab untuk menyajikan antarmuka rincian dari sebuah pelatihan spesifik,
 * mengelola navigasi antar tab (Materi, Kuis, Forum), dan memastikan kelengkapan sesi
 * sebelum memperbolehkan akses ke fitur evaluasi (Kuis/Assessment).
 */
class DX_LMS_M_Student_Course_Detail extends DX_LMS_M_Controller
{
    /**
     * Menampilkan Halaman Rincian Pelatihan (Detail Course).
     *
     * Memuat objek data pelatihan secara spesifik berdasarkan parameter ID (course_id).
     * Metode ini juga mengevaluasi kemajuan (progress) dari seluruh sesi pembelajaran
     * dan meneruskan konfirmasi (flag) ke View untuk mengontrol visibilitas tab.
     *
     * @return void Merender antarmuka detail pelatihan lengkap dengan sesi dan materi.
     */
    public function index()
    {
        startAppSession();

        /** Mengambil ID course hanya dari parameter URL yang valid. */
        $courseId = $_GET['id'] ?? '';
        $tab = $_GET['tab'] ?? 'materi';
        if (!is_string($courseId) || strlen($courseId) > 80) $courseId = '';
        if (!is_string($tab) || !in_array($tab, ['materi', 'quiz', 'forum', 'riwayat', 'capaian'], true)) $tab = 'materi';
        $course = DX_LMS_M_Student_Data::getCourseById($courseId);

        if (!$course) {
            header('Location: ?route=/student/courses');
            exit();
        }

        if (empty($course['has_access'])) {
            $requestError = '';
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!verifyCsrf()) {
                    http_response_code(403);
                    exit('Sesi formulir tidak valid. Muat ulang halaman lalu coba kembali.');
                }
                $requestNote = $_POST['request_note'] ?? '';
                if (!is_string($requestNote) || strlen($requestNote) > 1000) {
                    $requestError = 'Catatan maksimal 1.000 karakter.';
                } else {
                    try {
                        require_once dirname(__DIR__, 3) . '/includes/StudentApi.php';
                        $requesterRole = ($_SESSION['sso_mode'] ?? '') === 'teacher_student' ? 'teacher' : 'student';
                        apiRequestStudentCourseAccess(
                            (string) $course['id'],
                            (int) ($_SESSION['student_id'] ?? 0),
                            (string) ($_SESSION['student_npk'] ?? ''),
                            trim($requestNote),
                            $requesterRole,
                        );
                        header('Location: ?route=/student/course/detail&id=' . rawurlencode((string) $course['id']), true, 303);
                        exit();
                    } catch (PDOException $error) {
                        error_log('Student course access request failed: ' . $error->getMessage());
                        $requestError = 'Permintaan belum dapat disimpan. Coba lagi sebentar lagi.';
                    } catch (RuntimeException $error) {
                        $requestError = $error->getMessage() ?: 'Permintaan akses belum dapat dikirim.';
                    } catch (Throwable $error) {
                        error_log('Student course access request failed: ' . $error->getMessage());
                        $requestError = 'Permintaan belum dapat disimpan. Coba lagi sebentar lagi.';
                    }
                }
            }
            $this->view('DX_LMS_M_Student_Course_Access', [
                'course' => $course,
                'stats' => DX_LMS_M_Student_Data::getStats(),
                'csrf_token' => csrfToken(),
                'request_error' => $requestError,
            ]);
            return;
        }

        // Normalize assessment data because live course records can omit fields
        // that the legacy course-detail view expects.
        $course['quiz'] = array_replace(
            ['status' => 'terkunci', 'questions' => [], 'history' => [], 'score' => null],
            is_array($course['quiz'] ?? null) ? $course['quiz'] : [],
        );
        $course['quiz']['questions'] = is_array($course['quiz']['questions'] ?? null) ? $course['quiz']['questions'] : [];
        $course['quiz']['history'] = is_array($course['quiz']['history'] ?? null) ? $course['quiz']['history'] : [];

        $course['final_assessment'] = array_replace(
            ['status' => 'terkunci', 'questions' => [], 'history' => [], 'nilai' => null],
            is_array($course['final_assessment'] ?? null) ? $course['final_assessment'] : [],
        );
        $course['final_assessment']['questions'] = is_array($course['final_assessment']['questions'] ?? null)
            ? $course['final_assessment']['questions']
            : [];
        $course['final_assessment']['history'] = is_array($course['final_assessment']['history'] ?? null)
            ? $course['final_assessment']['history']
            : [];

        $course['sessions'] = is_array($course['sessions'] ?? null) ? $course['sessions'] : [];
        foreach ($course['sessions'] as &$session) {
            if (!is_array($session)) {
                $session = ['status' => 'terkunci', 'materials' => []];
                continue;
            }
            $session['status'] = $session['status'] ?? 'terkunci';
            $session['materials'] = is_array($session['materials'] ?? null) ? $session['materials'] : [];
        }
        unset($session);

        /** Memeriksa apakah seluruh sesi pembelajaran telah diselesaikan. */
        $all_sessions_completed = true;
        if (!empty($course['sessions'])) {
            foreach ($course['sessions'] as $session) {
                if ($session['status'] !== 'selesai') {
                    $all_sessions_completed = false;
                    break;
                }
            }
        } else {
            $all_sessions_completed = false; /** Jika tidak terdapat sesi, sistem menganggap pembelajaran belum selesai sesuai logika bisnis. */
        }

        $stats = DX_LMS_M_Student_Data::getStats();

        $data = [
            'course' => $course,
            'tab' => $tab,
            'all_sessions_completed' => $all_sessions_completed,
            'stats' => $stats,
        ];

        $this->view('DX_LMS_M_Student_Course_Detail', $data);
    }
}
