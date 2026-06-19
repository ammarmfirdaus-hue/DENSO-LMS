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

    if ($diff->y > 0) return $diff->y . ' tahun yang lalu';
    if ($diff->m > 0) return $diff->m . ' bulan yang lalu';
    if ($diff->d > 0) {
        if ($diff->d === 1) return 'Kemarin';
        return $diff->d . ' hari yang lalu';
    }
    if ($diff->h > 0) return $diff->h . ' jam yang lalu';
    if ($diff->i > 0) return $diff->i . ' menit yang lalu';
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
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
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
    if ($total <= 0) return 0;
    return min(100, (int) round(($completed / $total) * 100));
}

/**
 * Mengambil Kelas Warna Lencana (Badge Color Class).
 * 
 * Memetakan string status menjadi format CSS/kelas HTML untuk memberikan label visual 
 * berwarna pada komponen lencana (badge) di antarmuka.
 * 
 * @param string $status Teks yang menggambarkan status aktivitas/sistem.
 * @return string String kelas nama CSS yang sesuai standar komponen Denso.
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
        'materi'  => ['menu_book', 'bg-secondary-container-light', 'text-secondary'],
        'course'  => ['school', 'bg-tertiary-container-light', 'text-tertiary'],
        'quiz'    => ['assignment_turned_in', 'bg-primary-container-light', 'text-primary'],
        'video'   => ['play_circle', 'bg-secondary-container-light', 'text-secondary'],
        default   => ['notifications', 'bg-surface-container', 'text-on-surface-variant'],
    };
}
