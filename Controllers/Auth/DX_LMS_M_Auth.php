<?php
/**
 * Kelas Pengontrol Autentikasi (Authentication Controller).
 *
 * Bertanggung jawab penuh atas manajemen sesi pengguna, termasuk proses
 * masuk (Login), keluar (Logout), serta pemilihan peran (Role Selection)
 * setelah proses login berhasil dilakukan.
 */
class DX_LMS_M_Auth extends DX_LMS_M_Controller
{
    /**
     * Menampilkan Halaman Login Pengguna.
     *
     * Fungsi ini bertugas untuk merender antarmuka halaman autentikasi.
     * Jika sistem mendeteksi bahwa pengguna sudah memiliki sesi aktif (sudah login),
     * maka pengguna akan diarahkan (redirect) secara otomatis ke halaman pemilihan peran.
     *
     * @return void Tidak mengembalikan nilai, langsung merender View atau melakukan redirect.
     */
    public function login()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Jika ada parameter ?switch=1, ?relogin=1, ?new=1, ?role=..., atau ?portal=..., 
        // biarkan form login tampil agar pengguna bisa login akun/peran lain di tab terpisah pada browser yang sama
        if (isset($_GET['switch']) || isset($_GET['relogin']) || isset($_GET['new']) || isset($_GET['role']) || isset($_GET['portal'])) {
            $this->view('DX_LMS_M_Auth_Login');
            return;
        }

        if (isset($_SESSION['user'])) {
            $baseRole = $_SESSION['user']['role'] ?? 'student';
            if ($baseRole === 'student') {
                $_SESSION['active_role'] = 'student';
                header('Location: ?route=/student');
            } else {
                header('Location: ?route=/pilih-peran');
            }
            exit();
        }
        $this->view('DX_LMS_M_Auth_Login');
    }

    /**
     * Memproses Data Login dari Formulir (Form Submit).
     *
     * Menerima kiriman data (POST) berisi NPK dan kata sandi (password).
     * Memvalidasi akun dan mengarahkan pengguna secara cerdas:
     * - Role 'student': langsung menuju halaman /student
     * - Role 'teacher': menuju pemilihan peran (Student / Teacher)
     * - Role 'admin': menuju pemilihan peran (Student / Teacher / Admin)
     *
     * @return void Melakukan redirect berdasarkan status autentikasi.
     */
    public function processLogin()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $npk = trim($_POST['npk'] ?? '');
            $password = $_POST['password'] ?? '';

            if (!empty($npk) && !empty($password)) {
                require_once 'Models/DX_LMS_M_Employees.php';
                $employeeModel = new DX_LMS_M_Employees();
                $user = $employeeModel->findByNpk($npk);

                /** Melakukan verifikasi ketersediaan pengguna dan kecocokan kata sandi. */
                if ($user && password_verify($password, $user['password'])) {
                    // Mencegah Session Fixation (OWASP A07)
                    session_regenerate_id(true);
                    unset($_SESSION['course_filters']);
                    $baseRole = strtolower($user['role'] ?? 'student');

                    // Menentukan daftar peran yang diizinkan untuk akun ini
                    $availableRoles = ['student'];
                    if ($baseRole === 'teacher') {
                        $availableRoles = ['student', 'teacher'];
                    } elseif ($baseRole === 'admin') {
                        $availableRoles = ['student', 'teacher', 'admin'];
                    }

                    /** Menyimpan data penting (esensial) ke dalam sesi (session). */
                    $userData = [
                        'employee_id' => (int) $user['employee_id'],
                        'id' => (int) $user['employee_id'],
                        'npk' => $user['npk'],
                        'username' => $user['npk'],
                        'name' => $user['fullname'],
                        'fullname' => $user['fullname'],
                        'role' => $baseRole, // Role dasar akun tetap dan permanen
                        'golongan' => $user['golongan'] ?? $user['golongan_id'] ?? 'Gol 1',
                        'jabatan' => $user['jabatan'] ?? $user['position_id'] ?? 'Staff',
                        'position' => $user['jabatan'] ?? $user['position_id'] ?? 'Staff',
                        'department' => $user['department'] ?? '',
                        'division' => $user['division'] ?? '',
                        'section' => $user['section'] ?? '',
                        'email' => $user['email'] ?? '',
                    ];

                    $_SESSION['user'] = $userData;
                    $_SESSION['available_roles'] = $availableRoles;

                    // Mengisolasi slot peran di $_SESSION['roles'] agar multi-tab tidak saling menimpa
                    if (!isset($_SESSION['roles'])) {
                        $_SESSION['roles'] = [];
                    }
                    $_SESSION['roles'][$baseRole] = $userData;

                    // Alur pengalihan berdasarkan Role akun
                    if ($baseRole === 'student') {
                        // Role student langsung masuk ke halaman student
                        $_SESSION['active_role'] = 'student';
                        header('Location: ?route=/student');
                        exit();
                    } else {
                        // Role teacher dan admin masuk ke pemilihan peran
                        header('Location: ?route=/pilih-peran');
                        exit();
                    }
                } else {
                    /** Menangani kondisi ketika kata sandi salah atau pengguna tidak ditemukan/tidak aktif. */
                    header('Location: ?route=/login&error=1');
                    exit();
                }
            } else {
                header('Location: ?route=/login&error=empty');
                exit();
            }
        }
        header('Location: ?route=/login');
        exit();
    }

    /**
     * Menampilkan Halaman Pemilihan Peran (Role Selection).
     *
     * Hanya dapat diakses oleh akun yang memiliki opsi multi-peran (Teacher atau Admin).
     * Pengguna dengan peran 'student' murni akan otomatis diarahkan ke /student.
     *
     * @return void Merender halaman pemilihan peran atau redirect.
     */
    public function roleSelection()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user'])) {
            header('Location: ?route=/login');
            exit();
        }

        $baseRole = $_SESSION['user']['role'] ?? 'student';

        // Jika akun merupakan Student murni, langsung arahkan ke beranda student
        if ($baseRole === 'student') {
            $_SESSION['active_role'] = 'student';
            header('Location: ?route=/student');
            exit();
        }

        $this->view('DX_LMS_M_Auth_Role');
    }

    /**
     * Memproses Penyimpanan Pilihan Peran ke dalam Sesi.
     *
     * Melakukan validasi ketat (whitelist) terhadap peran yang diminta:
     * - Akun Teacher hanya boleh memilih 'student' atau 'teacher'
     * - Akun Admin boleh memilih 'student', 'teacher', atau 'admin'
     * - Role asli pada database ($_SESSION['user']['role']) tetap terjaga dan tidak berubah.
     *
     * @return void Redirect ke dasbor target (Student/Teacher/Admin).
     */
    public function setRole()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user'])) {
            header('Location: ?route=/login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role'])) {
            $baseRole = $_SESSION['user']['role'] ?? 'student';
            $selectedRole = strtolower(trim($_POST['role']));

            // Daftar peran sah yang boleh diakses berdasarkan role akun
            $allowedRoles = $_SESSION['available_roles'] ?? ['student'];
            if (!in_array($selectedRole, $allowedRoles, true)) {
                // Percobaan akses peran ilegal ditolak
                header('Location: ?route=/pilih-peran&error=unauthorized');
                exit();
            }

            unset($_SESSION['course_filters']);
            // Ubah active_role dan simpan profil pada slot peran spesifik
            $_SESSION['active_role'] = $selectedRole;
            if (!isset($_SESSION['roles'])) {
                $_SESSION['roles'] = [];
            }
            // Jangan menimpa akun asli jika peran yang dipilih sudah memiliki akun murni aktif
            if (empty($_SESSION['roles'][$selectedRole]) || strtolower($_SESSION['roles'][$selectedRole]['role'] ?? '') !== $selectedRole) {
                $_SESSION['roles'][$selectedRole] = $_SESSION['user'];
            }

            if ($selectedRole === 'student') {
                header('Location: ?route=/student');
            } elseif ($selectedRole === 'teacher') {
                header('Location: ?route=/teacher');
            } elseif ($selectedRole === 'admin') {
                header('Location: ?route=/admin');
            } else {
                header('Location: ?route=/pilih-peran');
            }
            exit();
        }

        header('Location: ?route=/pilih-peran');
        exit();
    }

    /**
     * Memproses Keluar Sistem (Logout).
     *
     * Menghancurkan sesi pengguna secara aman. Mendukung logout per peran
     * (jika parameter ?role= dispesifikasikan) atau logout penuh dari seluruh sistem.
     *
     * @return void Redirect otomatis ke rute '/login'.
     */
    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $portal = $_GET['role'] ?? $_GET['portal'] ?? null;
        if ($portal && isset($_SESSION['roles'][$portal])) {
            unset($_SESSION['roles'][$portal]);
            if (($_SESSION['user']['role'] ?? '') === $portal) {
                unset($_SESSION['user']);
                foreach (['admin', 'teacher', 'student'] as $r) {
                    if (!empty($_SESSION['roles'][$r])) {
                        $_SESSION['user'] = $_SESSION['roles'][$r];
                        break;
                    }
                }
            }
            if (empty($_SESSION['user']) && empty($_SESSION['roles'])) {
                $_SESSION = [];
                session_destroy();
            }
            header('Location: ?route=/login&relogin=1');
            exit();
        }

        $_SESSION = [];
        session_destroy();
        header('Location: ?route=/login');
        exit();
    }
}
