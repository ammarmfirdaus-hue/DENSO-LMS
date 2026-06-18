<?php
/**
 * DLS Helper Functions
 * Sistem Pembelajaran Denso
 */

/**
 * Get greeting based on current time
 * @return string
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
 * Format relative time in Indonesian
 * @param string $datetime
 * @return string
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
 * Format date in Indonesian
 * @param string $date
 * @return string
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
 * Sanitize output for HTML
 * @param string $str
 * @return string
 */
function e(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate progress bar percentage text
 * @param int $completed
 * @param int $total
 * @return int
 */
function calcProgress(int $completed, int $total): int
{
    if ($total <= 0) return 0;
    return min(100, (int) round(($completed / $total) * 100));
}

/**
 * Get status badge class
 * @param string $status
 * @return string
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
 * Get material icon name for notification type
 * @param string $type
 * @return array [icon_name, bg_class, text_class]
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
