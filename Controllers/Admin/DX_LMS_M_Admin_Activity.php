<?php
/**
 * Controller untuk Activity Center (Stage 2).
 * Mendukung filter berdasarkan role, tanggal, dan keyword pencarian.
 */

require_once 'Core/DX_LMS_M_Controller.php';
require_once 'includes/DX_LMS_M_Admin_MockData.php';

class DX_LMS_M_Admin_Activity extends DX_LMS_M_Controller
{
    public function index()
    {
        $all_activities = DX_LMS_M_Admin_MockData::getActivityLogs();

        // --- Ambil parameter filter ---
        $filter_role     = isset($_GET['role'])    ? trim($_GET['role'])    : 'all';
        $filter_date     = isset($_GET['date'])    ? trim($_GET['date'])    : '';
        $filter_keyword  = isset($_GET['q'])       ? trim($_GET['q'])       : '';

        // --- Hitung statistik per-role (dari semua data, sebelum difilter) ---
        $stats = [
            'total'   => count($all_activities),
            'admin'   => count(array_filter($all_activities, fn($a) => strtolower($a['role']) === 'admin')),
            'teacher' => count(array_filter($all_activities, fn($a) => strtolower($a['role']) === 'teacher')),
            'student' => count(array_filter($all_activities, fn($a) => strtolower($a['role']) === 'student')),
        ];

        // --- Terapkan filter ---
        $activities = $all_activities;

        // Filter by role
        if ($filter_role !== 'all') {
            $activities = array_filter($activities, function ($a) use ($filter_role) {
                return strtolower($a['role']) === strtolower($filter_role);
            });
        }

        // Filter by date
        if (!empty($filter_date)) {
            $activities = array_filter($activities, function ($a) use ($filter_date) {
                return isset($a['date']) && $a['date'] === $filter_date;
            });
        }

        // Filter by keyword (user name or target)
        if (!empty($filter_keyword)) {
            $activities = array_filter($activities, function ($a) use ($filter_keyword) {
                $keyword_lower = strtolower($filter_keyword);
                return str_contains(strtolower($a['user']), $keyword_lower)
                    || str_contains(strtolower($a['target']), $keyword_lower)
                    || str_contains(strtolower($a['action']), $keyword_lower);
            });
        }

        // Reindex array setelah filter
        $activities = array_values($activities);

        $this->view('Admin/DX_LMS_M_Admin_Activity', [
            'activities'     => $activities,
            'all_activities' => $all_activities,
            'stats'          => $stats,
            'filter_role'    => $filter_role,
            'filter_date'    => $filter_date,
            'filter_keyword' => $filter_keyword,
        ]);
    }
}
