<?php
/**
 * Controller untuk Manajemen Course (Tahap 1 MVP).
 */

require_once 'Core/DX_LMS_M_Controller.php';
require_once 'includes/DX_LMS_M_Teacher_MockData.php'; // Inheriting full course catalog from Teacher source

class DX_LMS_M_Admin_Course extends DX_LMS_M_Controller
{
    public function index()
    {
        // Using Teacher mock data to show more courses as requested.
        $rawCourses = DX_LMS_M_Teacher_MockData::getManagedCourses();
        $mappedCourses = [];

        foreach ($rawCourses as $c) {
            $raw = $c['status'] ?? 'Draft';
            $displayStat = match ($raw) {
                'Draft' => 'Private',
                'Submitted', 'Approved' => 'Pending Review',
                'Running' => 'In Progress',
                'Completed' => 'Completed',
                'Archived' => 'Archived',
                'Rejected' => 'Rejected',
                default => $raw,
            };

            $c['status'] = $displayStat; // Override with the mapped string

            // Admin shouldn't see Private (Draft) or Rejected
            if ($displayStat !== 'Private' && $displayStat !== 'Rejected') {
                $mappedCourses[] = $c;
            }
        }

        $this->view('Admin/DX_LMS_M_Admin_Course', [
            'courses' => $mappedCourses,
        ]);
    }
}
