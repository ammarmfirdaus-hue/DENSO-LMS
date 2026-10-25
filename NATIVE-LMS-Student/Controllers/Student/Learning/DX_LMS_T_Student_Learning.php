<?php
/**
 * Kelas Pengontrol Pembelajaran Interaktif (Student Learning Controller).
 *
 * Merupakan mesin utama (core engine) saat siswa sedang berada dalam mode "Belajar"
 * (menonton video materi atau membaca modul). Kelas ini secara cerdas mengatur
 * rute (routing) ke materi berikutnya (next) atau materi sebelumnya (prev),
 * serta memastikan navigasi antar materi tetap berurutan.
 */
class DX_LMS_T_Student_Learning extends DX_LMS_M_Controller
{
    /**
     * Menampilkan Antarmuka Mode Belajar (Learning Mode).
     *
     * Mengambil parameter dari URL (ID Pelatihan, ID Sesi, Indeks Materi),
     * memvalidasi ketersediaannya, dan secara dinamis menghitung rute (URL)
     * untuk tombol "Sebelumnya" dan "Selanjutnya".
     *
     * @return void Merender antarmuka mode fokus belajar (Learning Viewer).
     */
    public function index()
    {
        startAppSession();

        $id = $_GET['id'] ?? '';
        $sessionId = $_GET['session_id'] ?? '';
        $materialIndexRaw = $_GET['material_index'] ?? '0';
        if (!is_string($id) || $id === '' || strlen($id) > 80 || !is_string($sessionId) || strlen($sessionId) > 80
            || !is_string($materialIndexRaw) || !ctype_digit($materialIndexRaw)) {
            header('Location: ?route=/student/courses');
            exit();
        }
        $materialIndex = (int) $materialIndexRaw;

        $course = DX_LMS_M_Student_Data::getCourseById($id);

        if (!$course) {
            header('Location: ?route=/student/courses');
            exit();
        }

        /** Melakukan pencarian data sesi pembelajaran. */
        $currentSession = null;
        $sessionIndex = -1;
        foreach ($course['sessions'] as $index => $session) {
            if ($session['id'] === $sessionId) {
                $currentSession = $session;
                $sessionIndex = $index;
                break;
            }
        }

        if (!$currentSession || !isset($currentSession['materials'][$materialIndex])) {
            if ($course['status'] === 'selesai') {
                header('Location: ?route=/student/riwayat/detail&id=' . urlencode($id));
            } else {
                header('Location: ?route=/student/course/detail&id=' . urlencode($id));
            }
            exit();
        }

        $currentMaterial = $currentSession['materials'][$materialIndex];
        $isCompleted = $course['status'] === 'selesai';
        $encodedCourseId = rawurlencode($id);
        $encodedSessionId = rawurlencode($sessionId);
        $isLiveCourse = !empty($course['is_live']);
        $materialIsCompleted = ($currentMaterial['status'] ?? '') === 'selesai';
        $completionError = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$isLiveCourse || !verifyCsrf()) {
                http_response_code(403);
                exit('Permintaan tidak valid. Muat ulang halaman lalu coba kembali.');
            }
            try {
                $saved = apiCompleteStudentMaterial($id, (int) ($currentMaterial['material_id'] ?? 0), (int) ($_SESSION['student_id'] ?? 0));
                if (!$saved) throw new RuntimeException('Materi ini tidak dapat ditandai selesai. Muat ulang course lalu coba lagi.');
                header('Location: ?route=/student/learning&id=' . $encodedCourseId . '&session_id=' . $encodedSessionId . '&material_index=' . $materialIndex . '&completed=1', true, 303);
                exit();
            } catch (Throwable $error) {
                error_log('Student material completion failed: ' . $error->getMessage());
                $completionError = 'Materi belum dapat disimpan. Coba lagi sebentar.';
            }
        }

        /** Menentukan tautan (URL) halaman sebelumnya. */
        $prevUrl = '';
        if ($materialIndex > 0) {
            $prevUrl =
                "?route=/student/learning&id={$encodedCourseId}&session_id={$encodedSessionId}&material_index=" . ($materialIndex - 1);
        } else {
            if ($isCompleted) {
                /** Logika ketika kursus selesai: arahkan kembali ke halaman detail riwayat. */
                $prevUrl = "?route=/student/riwayat/detail&id={$encodedCourseId}&tab=materi";
            } else {
                $prevUrl = '?route=/student/course/detail&id=' . $encodedCourseId;
            }
        }

        $courseSequence = [];
        $courseMaterialIndex = -1;
        if ($isLiveCourse) {
            foreach ($course['sessions'] as $sequenceSession) {
                foreach ($sequenceSession['materials'] as $sequenceMaterialIndex => $sequenceMaterial) {
                    $courseSequence[] = [
                        'session_id' => (string) $sequenceSession['id'],
                        'material_index' => $sequenceMaterialIndex,
                    ];
                    if ((string) $sequenceSession['id'] === (string) $sessionId && $sequenceMaterialIndex === $materialIndex) {
                        $courseMaterialIndex = count($courseSequence) - 1;
                    }
                }
            }
            if ($courseMaterialIndex >= 0) {
                if ($courseMaterialIndex === 0) {
                    $prevUrl = $isCompleted
                        ? '?route=/student/riwayat/detail&id=' . $encodedCourseId . '&tab=materi'
                        : '?route=/student/course/detail&id=' . $encodedCourseId;
                } else {
                    $previousMaterial = $courseSequence[$courseMaterialIndex - 1];
                    $prevUrl = '?route=/student/learning&id=' . $encodedCourseId . '&session_id=' . rawurlencode($previousMaterial['session_id']) . '&material_index=' . $previousMaterial['material_index'];
                }
            }
        }

        /** Menentukan tautan (URL) halaman berikutnya. */
        $nextUrl = '';
        if ($isLiveCourse && $courseMaterialIndex >= 0 && isset($courseSequence[$courseMaterialIndex + 1])) {
            $nextMaterial = $courseSequence[$courseMaterialIndex + 1];
            $nextUrl = '?route=/student/learning&id=' . $encodedCourseId . '&session_id=' . rawurlencode($nextMaterial['session_id']) . '&material_index=' . $nextMaterial['material_index'];
        } elseif ($isLiveCourse && $courseMaterialIndex >= 0) {
            $nextUrl = '?route=/student/quiz&id=' . $encodedCourseId . '&type=quiz';
        } elseif (isset($currentSession['materials'][$materialIndex + 1])) {
            $nextUrl =
                "?route=/student/learning&id={$encodedCourseId}&session_id={$encodedSessionId}&material_index=" . ($materialIndex + 1);
        } else {
            if ($isCompleted) {
                /** Logika ketika kursus selesai: arahkan kembali ke halaman detail riwayat. */
                $nextUrl = "?route=/student/riwayat/detail&id={$encodedCourseId}&tab=materi";
            } else {
                $nextUrl = '?route=/student/course/detail&id=' . $encodedCourseId;
            }
        }

        $data = [
            'course' => $course,
            'session' => $currentSession,
            'material' => $currentMaterial,
            'materialIndex' => $materialIndex,
            'totalMaterials' => count($currentSession['materials']),
            'displayMaterialIndex' => $isLiveCourse && $courseMaterialIndex >= 0 ? $courseMaterialIndex + 1 : $materialIndex + 1,
            'totalCourseMaterials' => $isLiveCourse ? count($courseSequence) : count($currentSession['materials']),
            'isFirstCourseMaterial' => $isLiveCourse && $courseMaterialIndex >= 0 ? $courseMaterialIndex === 0 : $materialIndex === 0,
            'isLastCourseMaterial' => $isLiveCourse && $courseMaterialIndex >= 0 ? $courseMaterialIndex === count($courseSequence) - 1 : $materialIndex === count($currentSession['materials']) - 1,
            'isLiveCourse' => $isLiveCourse,
            'materialIsCompleted' => $materialIsCompleted,
            'completionError' => $completionError,
            'prevUrl' => $prevUrl,
            'nextUrl' => $nextUrl,
        ];

        $this->view('DX_LMS_T_Student_Learning', $data);
    }
}
