<?php
/**
 * Controller untuk Manajemen User (Tahap 1 MVP).
 */

require_once 'Core/DX_LMS_M_Controller.php';
require_once 'includes/DX_LMS_M_Admin_MockData.php';

class DX_LMS_M_Admin_User extends DX_LMS_M_Controller
{
    public function index()
    {
        $users = DX_LMS_M_Admin_MockData::getUsers();

        $this->view('Admin/DX_LMS_M_Admin_User', [
            'users' => $users,
        ]);
    }
}
