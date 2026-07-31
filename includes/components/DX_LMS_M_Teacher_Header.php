<?php
/**
 * Komponen Bilah Atas Aplikasi Khusus Teacher — DLS.
 *
 * Menggunakan standar desain yang SAMA PERSIS dengan Admin dan Student (DX_LMS_M_Header.php).
 */
$show_back = $show_back_button ?? $show_back ?? false;
$header_title = $header_title ?? 'LMS';
$notif_count = $notif_count ?? ($stats['unread_notifs'] ?? 0);
$base_path = $base_path ?? '';
include __DIR__ . '/DX_LMS_M_Header.php';
