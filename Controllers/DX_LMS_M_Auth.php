<?php
/**
 * Controller Autentikasi
 * Menangani proses Login, Logout, dan Pemilihan Peran
 */
class DX_LMS_M_Auth extends DX_LMS_M_Controller {
    
    /**
     * Menampilkan halaman Login
     */
    public function login() {
        session_start();
        if (isset($_SESSION['user'])) {
            header('Location: ?route=/pilih-peran');
            exit;
        }
        $this->view('DX_LMS_M_Auth_Login');
    }

    /**
     * Memproses data login (Dummy)
     */
    public function processLogin() {
        session_start();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['npk'] ?? '';
            $password = $_POST['password'] ?? '';

            // TODO: Gunakan Model DX_LMS_M_Employees untuk validasi asli
            if (!empty($username) && !empty($password)) {
                unset($_SESSION['course_filters']);
                $_SESSION['user'] = [
                    'username' => $username,
                    'name' => 'Budi Santoso',
                    'role' => 'student'
                ];
                header('Location: ?route=/pilih-peran');
                exit;
            } else {
                header('Location: ?route=/login&error=1');
                exit;
            }
        }
    }

    /**
     * Menampilkan halaman Pemilihan Peran
     */
    public function roleSelection() {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ?route=/login');
            exit;
        }
        $this->view('DX_LMS_M_Auth_Role');
    }

    /**
     * Menyimpan pilihan peran ke session
     */
    public function setRole() {
        session_start();
        if (isset($_POST['role'])) {
            unset($_SESSION['course_filters']);
            $_SESSION['active_role'] = $_POST['role'];
            if ($_POST['role'] === 'student') {
                header('Location: ?route=/student');
            } else {
                // Teacher/Admin belum diimplementasi
                header('Location: ?route=/pilih-peran');
            }
            exit;
        }
    }

    /**
     * Proses Logout
     */
    public function logout() {
        session_start();
        session_destroy();
        header('Location: ?route=/login');
        exit;
    }
}
