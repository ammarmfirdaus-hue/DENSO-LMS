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

    /**
     * Endpoint untuk menerima dan menyimpan perubahan pemetaan otorisasi role (RBAC Matrix).
     */
    public function update()
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input && !empty($_POST)) {
            $input = $_POST;
        }

        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $_SESSION['admin_role_last_updated'] = date('Y-m-d H:i:s');
        $_SESSION['admin_role_pending_changes'] = $input;

        echo json_encode([
            'status' => 'success',
            'message' => 'Otoritas role dan kebijakan SoD berhasil disimpan ke sistem.',
            'timestamp' => date('d M Y, H:i')
        ]);
        exit;
    }
}
