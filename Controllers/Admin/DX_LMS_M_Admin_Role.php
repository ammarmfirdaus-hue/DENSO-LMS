<?php
/**
 * Controller untuk Manajemen Role (Tahap 1 MVP).
 */

require_once 'Core/DX_LMS_M_Controller.php';
require_once 'includes/DX_LMS_M_Admin_MockData.php';

class DX_LMS_M_Admin_Role extends DX_LMS_M_Controller
{
    public function index()
    {
        $roles = DX_LMS_M_Admin_MockData::getRoles();

        $this->view('Admin/DX_LMS_M_Admin_Role', [
            'roles' => $roles,
        ]);
    }
}
