<?php
/**
 * Kumpulan Fungsi Bantuan Global (Global Helper Functions).
 *
 * Berisi pustaka fungsi-fungsi yang dapat dipanggil dari mana saja
 * (Views, Controllers, atau Models) untuk mempermudah pemformatan data,
 * pengaturan waktu, antarmuka pengguna, dan penanganan logika umum.
 */

/**
 * Mendapatkan Sapaan Waktu.
 *
 * Menghasilkan kalimat sapaan ("Selamat pagi", "Selamat siang", dsb.)
 * berdasarkan jam pada peladen saat ini.
 *
 * @return string Teks sapaan.
 */
function getGreeting(): string
{
    $hour = (int) date('H');
    if ($hour >= 5 && $hour < 12) {
        return 'Selamat pagi';
    } elseif ($hour >= 12 && $hour < 15) {
        return 'Selamat siang';
    } elseif ($hour >= 15 && $hour < 18) {
        return 'Selamat sore';
    } else {
        return 'Selamat malam';
    }
}

/**
 * Memformat Waktu Relatif (Time Ago Format).
 *
 * Menerjemahkan rekam waktu absolut (timestamp) menjadi format waktu relatif
 * yang lebih ramah bagi pengguna ("Baru saja", "2 jam yang lalu", dsb.).
 *
 * @param string $datetime String waktu aktual yang valid (misalnya: 'YYYY-MM-DD HH:MM:SS').
 * @return string Teks representasi waktu relatif dalam bahasa Indonesia.
 */
function timeAgo(string $datetime): string
{
    $now = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);

    if ($diff->y > 0) {
        return $diff->y . ' tahun yang lalu';
    }
    if ($diff->m > 0) {
        return $diff->m . ' bulan yang lalu';
    }
    if ($diff->d > 0) {
        if ($diff->d === 1) {
            return 'Kemarin';
        }
        return $diff->d . ' hari yang lalu';
    }
    if ($diff->h > 0) {
        return $diff->h . ' jam yang lalu';
    }
    if ($diff->i > 0) {
        return $diff->i . ' menit yang lalu';
    }
    return 'Baru saja';
}

/**
 * Memformat Tanggal Berbahasa Indonesia.
 *
 * Mengubah format tanggal dasar menjadi penulisan bahasa Indonesia baku
 * lengkap dengan nama bulan tekstual (contoh: "17 Agustus 1945").
 *
 * @param string $date String tanggal aktual.
 * @return string Tanggal yang telah terformat dalam tata bahasa Indonesia.
 */
function formatDateID(string $date): string
{
    $months = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    $dt = new DateTime($date);
    $day = $dt->format('j');
    $month = $months[(int) $dt->format('n')];
    $year = $dt->format('Y');

    return "$day $month $year";
}

/**
 * Menyaring Data Masukan Pengguna (Sanitize HTML Output).
 *
 * Melindungi dari serangan Cross-Site Scripting (XSS) dengan mengubah karakter-karakter
 * khusus ke dalam entitas HTML yang aman untuk dicetak di tampilan web.
 *
 * @param string $str String atau teks mentah yang akan diproses.
 * @return string Teks yang telah dibersihkan.
 */
function e(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Menghitung Persentase Perkembangan (Calculate Progress Percentage).
 *
 * Menerima jumlah bagian yang telah rampung dan total keseluruhannya,
 * lalu mengkalkulasikan nilai bulat persentase progres (0 - 100).
 *
 * @param int $completed Angka kuantitatif target yang diselesaikan.
 * @param int $total Total nilai dari seluruh target.
 * @return int Nilai numerik bulat maksimal hingga 100%.
 */
function calcProgress(int $completed, int $total): int
{
    if ($total <= 0) {
        return 0;
    }
    return min(100, (int) round(($completed / $total) * 100));
}

/**
 * Mengambil Kelas Warna Lencana (Badge Color Class).
 *
 * Memetakan string status menjadi format CSS/kelas HTML untuk memberikan label visual
 * berwarna pada komponen lencana (badge) di antarmuka.
 *
 * @param string $status Teks yang menggambarkan status aktivitas/sistem.
 * @return string String kelas nama CSS yang sesuai standar komponen LMS.
 */
function getStatusBadgeClass(string $status): string
{
    return match (strtolower($status)) {
        'selesai', 'completed' => 'dls-badge-success',
        'aktif', 'active', 'tersedia', 'available' => 'dls-badge-secondary',
        'terkunci', 'locked' => 'dls-badge-surface',
        'gagal', 'failed' => 'dls-badge-primary',
        default => 'dls-badge-surface',
    };
}

/**
 * Mendapatkan Properti Ikon Notifikasi (Notification Icon Properties).
 *
 * Mencocokkan tipe pemberitahuan untuk menetapkan ikon Material Symbols
 * beserta gaya pewarnaan (kelas CSS) khusus bagi notifikasi terkait.
 *
 * @param string $type Jenis data/tipe dari notifikasi (misalnya: 'materi', 'quiz').
 * @return array Larik konfigurasi yang berisi: [nama_ikon, kelas_latar, kelas_teks].
 */
function getNotifIcon(string $type): array
{
    return match ($type) {
        'materi' => ['menu_book', 'bg-secondary-container-light', 'text-secondary'],
        'course' => ['school', 'bg-tertiary-container-light', 'text-tertiary'],
        'quiz' => ['assignment_turned_in', 'bg-primary-container-light', 'text-primary'],
        'video' => ['play_circle', 'bg-secondary-container-light', 'text-secondary'],
        default => ['notifications', 'bg-surface-container', 'text-on-surface-variant'],
    };
}

/**
 * Memeriksa Hak Akses (RBAC Permission Checker).
 *
 * Memeriksa apakah suatu role atau user saat ini memiliki kapabilitas/otoritas
 * tertentu dalam sistem DENSO-LMS berdasarkan matriks otorisasi.
 *
 * @param string $capability Nama kapabilitas atau kode otorisasi (misalnya: 'Full System Control', 'Kelola User & Akun').
 * @param string|null $roleName Nama role (opsional, secara default membaca dari session user).
 * @return bool True apabila diizinkan, False jika ditolak.
 */
function hasPermission(string $capability, ?string $roleName = null): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

    if ($roleName === null) {
        $user = $_SESSION['user'] ?? null;
        $roleName = $user['role'] ?? 'Student';
    }

    if (strcasecmp($roleName, 'Super Admin') === 0 || strcasecmp($roleName, 'System Root') === 0) {
        return true;
    }

    $permissionsMap = [
        'Admin' => [
            'Kelola User & Akun',
            'Manajemen Course & Materi',
            'Assignment & Target KPI',
            'Laporan & Activity Log',
            'Buat & Edit Course',
            'Upload Materi Video/PDF',
            'Buat Quiz & Final Test',
            'Pantau Progres Siswa',
            'Periksa & Grading Quiz',
            'Bantu Evaluasi Tugas',
            'Review Materi Course',
            'Rekap Nilai Kelas',
            'Akses Materi & Sesi Belajar',
            'Ikuti Kuis & Evaluasi',
            'Unduh Sertifikat K3/ISO',
            'Akses Forum Diskusi'
        ],
        'Teacher' => [
            'Buat & Edit Course',
            'Upload Materi Video/PDF',
            'Buat Quiz & Final Test',
            'Pantau Progres Siswa',
            'Periksa & Grading Quiz',
            'Bantu Evaluasi Tugas',
            'Review Materi Course',
            'Rekap Nilai Kelas',
            'Akses Materi & Sesi Belajar',
            'Ikuti Kuis & Evaluasi',
            'Unduh Sertifikat K3/ISO',
            'Akses Forum Diskusi'
        ],
        'Teacher Associate' => [
            'Periksa & Grading Quiz',
            'Bantu Evaluasi Tugas',
            'Review Materi Course',
            'Rekap Nilai Kelas',
            'Akses Materi & Sesi Belajar',
            'Ikuti Kuis & Evaluasi',
            'Unduh Sertifikat K3/ISO',
            'Akses Forum Diskusi'
        ],
        'Student' => [
            'Akses Materi & Sesi Belajar',
            'Ikuti Kuis & Evaluasi',
            'Unduh Sertifikat K3/ISO',
            'Akses Forum Diskusi'
        ],
    ];

    $allowedCapabilities = $permissionsMap[$roleName] ?? [];

    return in_array($capability, $allowedCapabilities, true);
}

/**
 * Mendapatkan data pengguna yang berwenang untuk portal/workspace tertentu ('student', 'teacher', 'admin').
 *
 * Mendukung pembukaan beberapa tab dengan peran berbeda (misal: Teacher di Tab 1, Admin di Tab 2)
 * pada peramban (browser) yang sama tanpa benturan (session collision) atau saling menimpa.
 *
 * @param string $portal Portal yang diakses ('student', 'teacher', atau 'admin').
 * @return array|null Mengembalikan data profil pengguna jika berhak, atau null jika tidak sah.
 */
function getPortalUser(string $portal = 'student'): ?array
{
    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
        session_start();
    }

    $portal = strtolower(trim($portal));

    $matchedUser = null;

    // 1. Prioritaskan data sesi akun asli yang tepat dengan portalnya
    if (!empty($_SESSION['roles'][$portal])) {
        $savedRole = strtolower($_SESSION['roles'][$portal]['role'] ?? '');
        if ($savedRole === $portal) {
            $matchedUser = $_SESSION['roles'][$portal];
        }
    }

    // 2. Jika di portal 'teacher', cari apakah ada akun ber-role 'teacher' asli di $_SESSION['roles']
    if (!$matchedUser && $portal === 'teacher') {
        if (!empty($_SESSION['roles']['teacher']) && strtolower($_SESSION['roles']['teacher']['role'] ?? '') === 'teacher') {
            $matchedUser = $_SESSION['roles']['teacher'];
        }
    }

    // 3. Jika di portal 'admin', cari apakah ada akun ber-role 'admin' asli di $_SESSION['roles']
    if (!$matchedUser && $portal === 'admin') {
        if (!empty($_SESSION['roles']['admin']) && strtolower($_SESSION['roles']['admin']['role'] ?? '') === 'admin') {
            $matchedUser = $_SESSION['roles']['admin'];
        }
    }

    // 4. Jika di portal 'student', cari apakah ada akun ber-role 'student' asli di $_SESSION['roles']
    if (!$matchedUser && $portal === 'student') {
        if (!empty($_SESSION['roles']['student']) && strtolower($_SESSION['roles']['student']['role'] ?? '') === 'student') {
            $matchedUser = $_SESSION['roles']['student'];
        }
    }

    // 5. Cek slot tersimpan pada portal tersebut (misal akun admin yang sedang berada di mode teacher/student)
    if (!$matchedUser && !empty($_SESSION['roles'][$portal])) {
        $matchedUser = $_SESSION['roles'][$portal];
    }

    // 6. Terakhir, evaluasi akun aktif ($_SESSION['user']) jika memiliki izin peran tersebut
    if (!$matchedUser && !empty($_SESSION['user'])) {
        $baseRole = strtolower($_SESSION['user']['role'] ?? 'student');

        if ($portal === 'student') {
            $matchedUser = $_SESSION['user'];
        } elseif ($portal === 'teacher') {
            if (in_array($baseRole, ['teacher', 'admin'], true)) {
                $matchedUser = $_SESSION['user'];
            }
        } elseif ($portal === 'admin') {
            if ($baseRole === 'admin') {
                $matchedUser = $_SESSION['user'];
            }
        }
    }

    // Keamanan & Integritas Data: Pastikan employee_id selalu ada dan bertipe integer valid
    if (!empty($matchedUser)) {
        if (empty($matchedUser['employee_id'])) {
            $npk = $matchedUser['npk'] ?? ($matchedUser['username'] ?? '');
            if (!empty($npk)) {
                $empRow = dbQueryOne("SELECT employee_id, role, jabatan, golongan FROM DX_LMS_M_Employees WHERE npk = ?", [$npk]);
                if ($empRow) {
                    $matchedUser['employee_id'] = (int) $empRow['employee_id'];
                    $matchedUser['id'] = (int) $empRow['employee_id'];
                } else {
                    $matchedUser['employee_id'] = 2; // Default fallback to teacher Ahmad Rizal
                    $matchedUser['id'] = 2;
                }
            } else {
                $matchedUser['employee_id'] = 2;
                $matchedUser['id'] = 2;
            }

            // Simpan kembali ke dalam sesi agar query tidak berulang
            $_SESSION['user']['employee_id'] = $matchedUser['employee_id'];
            $_SESSION['user']['id'] = $matchedUser['employee_id'];
            if (isset($_SESSION['roles'][$portal])) {
                $_SESSION['roles'][$portal]['employee_id'] = $matchedUser['employee_id'];
                $_SESSION['roles'][$portal]['id'] = $matchedUser['employee_id'];
            }
        }
        return $matchedUser;
    }

    return null;
}

/**
 * Mendapatkan Status Tampilan Course yang Konsisten (Course Display Status).
 *
 * Menerapkan standardisasi status tampilan course:
 * - 'draft', 'private' => 'Private'
 * - 'submitted', 'pending review' => 'Pending Review'
 * - 'rejected' => 'Rejected'
 * - 'completed' => 'Completed'
 * - 'archived' => 'Archived'
 * - 'running', 'active' => 'In Progress'
 * - 'approved':
 *    Bila disetujui dalam 1 jam (<= 3600 detik) terakhir berdasarkan timestamp $updatedAt,
 *    maka tampilkan 'Approved'. Setelah lewat 1 jam, utamanya beralih menjadi 'In Progress'.
 *
 * @param string|null $rawStatus Status mentah dari basis data.
 * @param string|null $updatedAt Waktu terakhir diperbarui / disetujui.
 * @return string Status tampilan standar.
 */
function getCourseDisplayStatus(?string $rawStatus, ?string $updatedAt = null): string
{
    $st = strtolower(trim((string) $rawStatus));

    if (in_array($st, ['draft', 'private'], true)) {
        return 'Private';
    }

    if (in_array($st, ['submitted', 'pending review', 'pending_review'], true)) {
        return 'Pending Review';
    }

    if ($st === 'rejected') {
        return 'Rejected';
    }

    if ($st === 'completed') {
        return 'Completed';
    }

    if ($st === 'archived') {
        return 'Archived';
    }

    if ($st === 'approved') {
        // Cek selisih waktu dari saat admin menyetujui (updated_at)
        if (!empty($updatedAt)) {
            $updateTime = strtotime($updatedAt);
            if ($updateTime !== false) {
                $diff = time() - $updateTime;
                // 'Approved' hanya terjadi 30 menit atau 1 jam (3600 detik) setelah disetujui
                if ($diff >= 0 && $diff <= 3600) {
                    return 'Approved';
                }
            }
        }
        return 'In Progress';
    }

    if (in_array($st, ['active', 'running', 'in progress', 'in_progress'], true)) {
        return 'In Progress';
    }

    return 'Private';
}

/**
 * Mendapatkan Informasi Tampilan Visual Lencana Status Course (Badge, Progress, Dot).
 *
 * Menjamin keseragaman palet warna status di semua menu (Dashboard, Kelola Course, Detail).
 *
 * @param string $displayStat Status tampilan hasil getCourseDisplayStatus().
 * @return array Konfigurasi kelas CSS dan warna hex.
 */
function getCourseStatusBadgeInfo(string $displayStat): array
{
    return match ($displayStat) {
        'In Progress' => [
            'badgeCls' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
            'progressBarHex' => '#10B981',
            'progressTextColor' => 'text-emerald-600',
            'badgeDot' => 'bg-emerald-500 animate-pulse',
            'badgeColor' => 'bg-emerald-100 text-emerald-800',
            'badgeBorder' => 'border-emerald-300',
        ],
        'Approved' => [
            'badgeCls' => 'text-teal-700 bg-teal-50 border-teal-200',
            'progressBarHex' => '#0D9488',
            'progressTextColor' => 'text-teal-600',
            'badgeDot' => 'bg-teal-500',
            'badgeColor' => 'bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-300',
            'badgeBorder' => 'border-teal-300 dark:border-teal-700',
        ],
        'Pending Review' => [
            'badgeCls' => 'text-amber-700 bg-amber-50 border-amber-200',
            'progressBarHex' => '#F59E0B',
            'progressTextColor' => 'text-amber-600',
            'badgeDot' => 'bg-amber-500 animate-pulse',
            'badgeColor' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
            'badgeBorder' => 'border-amber-300 dark:border-amber-700',
        ],
        'Rejected' => [
            'badgeCls' => 'text-red-700 bg-red-50 border-red-200',
            'progressBarHex' => '#EF4444',
            'progressTextColor' => 'text-red-600',
            'badgeDot' => 'bg-red-500',
            'badgeColor' => 'bg-red-100 text-red-800',
            'badgeBorder' => 'border-red-300',
        ],
        'Completed' => [
            'badgeCls' => 'text-blue-700 bg-blue-50 border-blue-200',
            'progressBarHex' => '#3B82F6',
            'progressTextColor' => 'text-blue-600',
            'badgeDot' => 'bg-blue-500',
            'badgeColor' => 'bg-blue-100 text-blue-800',
            'badgeBorder' => 'border-blue-300',
        ],
        'Archived' => [
            'badgeCls' => 'text-gray-500 bg-gray-100 border-gray-200',
            'progressBarHex' => '#6B7280',
            'progressTextColor' => 'text-gray-500',
            'badgeDot' => 'bg-outline',
            'badgeColor' => 'bg-surface-container text-outline',
            'badgeBorder' => 'border-outline-variant/40',
        ],
        default => [ // 'Private'
            'badgeCls' => 'text-gray-700 bg-gray-50 border-gray-300 shadow-sm',
            'progressBarHex' => '#9CA3AF',
            'progressTextColor' => 'text-on-surface-variant',
            'badgeDot' => 'bg-gray-500',
            'badgeColor' => 'bg-surface-container-high text-on-surface-variant',
            'badgeBorder' => 'border-outline-variant/50',
        ],
    };
}

/**
 * Mendapatkan Tombol Tindakan Kartu Course yang Konsisten (CTA Action Info).
 *
 * Menjamin keseragaman label tombol dan rute navigasi pada kartu course di Dashboard dan Kelola Course.
 *
 * @param string $displayStat Status tampilan hasil getCourseDisplayStatus().
 * @param int|string $courseId ID Course.
 * @return array Konfigurasi teks tombol, URL tautan, dan kelas styling.
 */
function getCourseActionInfo(string $displayStat, int|string $courseId): array
{
    $cId = e((string) $courseId);
    return match ($displayStat) {
        'Private' => [
            'text' => 'Edit Course',
            'href' => '?route=/teacher/course/create&course_id=' . $cId,
            'cls'  => 'text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-gray-400',
        ],
        'Rejected' => [
            'text' => 'Perbaiki Course',
            'href' => '?route=/teacher/course/create&course_id=' . $cId,
            'cls'  => 'text-red-700 border-red-300 hover:bg-red-50 hover:border-red-400',
        ],
        'Pending Review' => [
            'text' => 'Lihat Detail',
            'href' => '?route=/teacher/course/detail&course_id=' . $cId,
            'cls'  => 'text-amber-700 border-amber-300 hover:bg-amber-50 hover:border-amber-400',
        ],
        'Approved' => [
            'text' => 'Lihat Detail',
            'href' => '?route=/teacher/course/detail&course_id=' . $cId,
            'cls'  => 'text-teal-700 border-teal-300 hover:bg-teal-50 hover:border-teal-400',
        ],
        'In Progress' => [
            'text' => 'Lihat Detail',
            'href' => '?route=/teacher/course/detail&course_id=' . $cId,
            'cls'  => 'text-emerald-700 border-emerald-300 hover:bg-emerald-50 hover:border-emerald-400',
        ],
        'Completed' => [
            'text' => 'Lihat Detail',
            'href' => '?route=/teacher/course/detail&course_id=' . $cId,
            'cls'  => 'text-blue-700 border-blue-300 hover:bg-blue-50 hover:border-blue-400',
        ],
        'Archived' => [
            'text' => 'Lihat Arsip',
            'href' => '?route=/teacher/course/detail&course_id=' . $cId,
            'cls'  => 'text-gray-500 border-gray-200 hover:bg-gray-50 hover:border-gray-300',
        ],
        default => [
            'text' => 'Lihat Detail',
            'href' => '?route=/teacher/course/detail&course_id=' . $cId,
            'cls'  => 'text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-gray-400',
        ],
    };
}

