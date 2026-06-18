<?php
/**
 * Controller for Student Quiz View
 */
class DX_LMS_M_Student_Quiz extends DX_LMS_Controller {
    
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

        $this->view('DX_LMS_M_Student_Quiz', $data);
    }
}
