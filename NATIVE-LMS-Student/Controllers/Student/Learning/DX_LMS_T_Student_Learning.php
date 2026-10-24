<?php
/**
 * Kelas Pengontrol Pembelajaran Interaktif (Student Learning Controller).
 *
 * Merupakan mesin utama (core engine) saat siswa sedang berada dalam mode "Belajar"
 * (menonton video materi atau membaca modul). Kelas ini secara cerdas mengatur
 * rute (routing) ke materi berikutnya (next) atau materi sebelumnya (prev),
 * serta memastikan urutan materi diakses dengan benar sebelum melakukan Post-Test.
 */
class DX_LMS_T_Student_Learning extends DX_LMS_M_Controller
{
    /**
     * Menampilkan Antarmuka Mode Belajar (Learning Mode).
     *
     * Mengambil parameter dari URL (ID Pelatihan, ID Sesi, Indeks Materi),
     * memvalidasi ketersediaannya, dan secara dinamis menghitung rute (URL)
     * untuk tombol "Sebelumnya" dan "Selanjutnya". Jika ini materi terakhir,
     * tombol "Selanjutnya" akan otomatis diarahkan ke rute Post-Test.
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
                /** Mengarahkan sistem kembali ke halaman evaluasi awal (Pre-Test). */
                $prevUrl = "?route=/student/quiz&id={$encodedCourseId}&session_id={$encodedSessionId}&type=pre_test";
            }
        }

        /** Menentukan tautan (URL) halaman berikutnya. */
        $nextUrl = '';
        if (isset($currentSession['materials'][$materialIndex + 1])) {
            $nextUrl =
                "?route=/student/learning&id={$encodedCourseId}&session_id={$encodedSessionId}&material_index=" . ($materialIndex + 1);
        } else {
            if ($isCompleted) {
                /** Logika ketika kursus selesai: arahkan kembali ke halaman detail riwayat. */
                $nextUrl = "?route=/student/riwayat/detail&id={$encodedCourseId}&tab=materi";
            } else {
                /** Mengarahkan sistem ke halaman evaluasi akhir (Post-Test). */
                $nextUrl = "?route=/student/quiz&id={$encodedCourseId}&session_id={$encodedSessionId}&type=post_test";
            }
        }

        $data = [
            'course' => $course,
            'session' => $currentSession,
            'material' => $currentMaterial,
            'materialIndex' => $materialIndex,
            'totalMaterials' => count($currentSession['materials']),
            'prevUrl' => $prevUrl,
            'nextUrl' => $nextUrl,
        ];

        $this->view('DX_LMS_T_Student_Learning', $data);
    }
}
