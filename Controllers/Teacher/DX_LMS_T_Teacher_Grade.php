<?php
/**
 * Kelas Pengontrol Penilaian Pengajar (Teacher Grade Controller).
 *
 * Mengelola fungsionalitas bagi pengajar untuk melihat dan memberikan nilai
 * kelulusan kepada murid yang mengikuti course mereka.
 */
class DX_LMS_T_Teacher_Grade extends DX_LMS_M_Controller
{
    /**
     * Menampilkan daftar murid dan nilai mereka.
     *
     * @return void
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user']) || $_SESSION['active_role'] !== 'teacher') {
            header('Location: ?route=/login');
            exit();
        }

        require_once 'includes/DX_LMS_M_Teacher_MockData.php';

        $managedCourses = DX_LMS_M_Teacher_MockData::getManagedCourses();
        $selectedCourseId = $_GET['course_id'] ?? (string)($managedCourses[0]['id'] ?? '1');
        $filterStatus = $_GET['status'] ?? 'all';

        // Cari course aktif
        $activeCourse = null;
        foreach ($managedCourses as $c) {
            if ((string)$c['id'] === (string)$selectedCourseId) {
                $activeCourse = $c;
                break;
            }
        }
        if (!$activeCourse && !empty($managedCourses)) {
            $activeCourse = $managedCourses[0];
            $selectedCourseId = (string)$activeCourse['id'];
        }

        // Ambil data nilai dan peserta untuk course terpilih
        $allStudentsInCourse = DX_LMS_M_Teacher_MockData::getCourseGrades($selectedCourseId, 'all');
        $students = DX_LMS_M_Teacher_MockData::getCourseGrades($selectedCourseId, $filterStatus);

        // Hitung statistik capaian
        $totalStudents = count($allStudentsInCourse);
        $passedCount = 0;
        $pendingCount = 0;
        $failedCount = 0;
        $totalScore = 0;

        foreach ($allStudentsInCourse as $s) {
            $totalScore += (int)$s['final_assessment'];
            if ($s['status'] === 'Lulus') {
                $passedCount++;
            } elseif ($s['status'] === 'Menunggu Validasi') {
                $pendingCount++;
            } elseif ($s['status'] === 'Tidak Lulus') {
                $failedCount++;
            }
        }

        $avgScore = $totalStudents > 0 ? round($totalScore / $totalStudents) : 0;

        $stats = [
            'total_students' => $totalStudents,
            'passed_count' => $passedCount,
            'pending_count' => $pendingCount,
            'failed_count' => $failedCount,
            'avg_score' => $avgScore,
        ];

        $data = [
            'active_tab' => 'grade',
            'base_path' => '',
            'courses' => $managedCourses,
            'selectedCourseId' => $selectedCourseId,
            'activeCourse' => $activeCourse,
            'filterStatus' => $filterStatus,
            'students' => $students,
            'stats' => $stats,
        ];

        $this->view('DX_LMS_T_Teacher_Grade', $data);
    }
}
