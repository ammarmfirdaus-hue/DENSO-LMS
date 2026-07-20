<?php
/**
 * Controller untuk Detail User (Drill-down).
 */

require_once 'Core/DX_LMS_M_Controller.php';
require_once 'includes/DX_LMS_M_Admin_MockData.php';

class DX_LMS_M_Admin_User_Detail extends DX_LMS_M_Controller
{
    public function index()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo "ID User tidak valid. <a href='?route=/admin/user'>Kembali</a>";
            return;
        }

        $user = DX_LMS_M_Admin_MockData::getUserDetail($id);

        if (!$user) {
            echo "Data User tidak ditemukan. <a href='?route=/admin/user'>Kembali</a>";
            return;
        }

        $this->view('Admin/DX_LMS_M_Admin_User_Detail', [
            'user' => $user,
        ]);
    }
}
