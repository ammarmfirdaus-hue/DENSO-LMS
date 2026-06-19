<?php
/**
 * Kelas Pengontrol Pembelajaran Interaktif (Student Learning Controller).
 * 
 * Merupakan mesin utama (core engine) saat siswa sedang berada dalam mode "Belajar" 
 * (menonton video materi atau membaca modul). Kelas ini secara cerdas mengatur 
 * rute (routing) ke materi berikutnya (next) atau materi sebelumnya (prev),
 * serta memastikan urutan materi diakses dengan benar sebelum melakukan Post-Test.
 */
class DX_LMS_T_Student_Learning extends DX_LMS_Controller {
    
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
    public function index() {
        session_start();

        $id = $_GET['id'] ?? '';
        $sessionId = $_GET['session_id'] ?? '';
        $materialIndex = isset($_GET['material_index']) ? (int)$_GET['material_index'] : 0;

        $course = DX_StudentData::getCourseById($id);

        if (!$course) {
            header('Location: ?route=/student/courses');
            exit;
        }

        // Cari session
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
            header('Location: ?route=/student/course/detail&id=' . urlencode($id));
            exit;
        }

        $currentMaterial = $currentSession['materials'][$materialIndex];

        // Tentukan prev URL
        $prevUrl = '';
        if ($materialIndex > 0) {
            $prevUrl = "?route=/student/learning&id={$id}&session_id={$sessionId}&material_index=" . ($materialIndex - 1);
        } else {
            // Balik ke Pre-Test
            $prevUrl = "?route=/student/quiz&id={$id}&session_id={$sessionId}&type=pre_test";
        }

        // Tentukan next URL
        $nextUrl = '';
        if (isset($currentSession['materials'][$materialIndex + 1])) {
            $nextUrl = "?route=/student/learning&id={$id}&session_id={$sessionId}&material_index=" . ($materialIndex + 1);
        } else {
            // Lanjut ke Post-Test
            $nextUrl = "?route=/student/quiz&id={$id}&session_id={$sessionId}&type=post_test";
        }

        $data = [
            'course' => $course,
            'session' => $currentSession,
            'material' => $currentMaterial,
            'materialIndex' => $materialIndex,
            'totalMaterials' => count($currentSession['materials']),
            'prevUrl' => $prevUrl,
            'nextUrl' => $nextUrl
        ];

        $this->view('DX_LMS_T_Student_Learning', $data);
    }
}
