<?php
/** Halaman rekap nilai course pribadi Student. */
class DX_LMS_T_Student_Nilai extends DX_LMS_M_Controller
{
    public function index(): void
    {
        startAppSession();
        requireStudent();
        require_once dirname(__DIR__, 3) . '/includes/StudentApi.php';
        $gradeError = '';
        try {
            $grades = apiStudentGrades((int) $_SESSION['student_id']);
        } catch (Throwable $error) {
            error_log('Student grades lookup failed: ' . $error->getMessage());
            $grades = [];
            $gradeError = 'Nilai belum dapat dimuat. Coba muat ulang halaman beberapa saat lagi.';
        }
        $this->view('DX_LMS_T_Student_Nilai', [
            'active_tab' => 'nilai',
            'grades' => $grades,
            'grade_error' => $gradeError,
            'stats' => DX_LMS_M_Student_Data::getStats(),
        ]);
    }
}
