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
        session_start();
        if (isset($_SESSION['user'])) {
            header('Location: ?route=/pilih-peran');
            exit();
        }
        $this->view('DX_LMS_M_Auth_Login');
    }

    /**
     * Memproses Data Login dari Formulir (Form Submit).
     *
     * Menerima kiriman data (POST) berisi NPK dan kata sandi (password).
     * Akan memvalidasi keberadaan pengguna di basis data dan mencocokkan hash sandi.
     * Jika validasi berhasil, data profil esensial akan disimpan ke dalam sesi ($_SESSION),
     * lalu pengguna diarahkan ke antarmuka pemilihan peran.
     *
     * @return void Melakukan redirect berdasarkan status autentikasi (berhasil atau gagal).
     */
    public function processLogin()
    {
        session_start();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $npk = $_POST['npk'] ?? '';
            $password = $_POST['password'] ?? '';

            if (!empty($npk) && !empty($password)) {
                require_once 'Models/DX_LMS_M_Employees.php';
                $employeeModel = new DX_LMS_M_Employees();
                $user = $employeeModel->findByNpk($npk);

                /** Melakukan verifikasi ketersediaan pengguna dan kecocokan kata sandi. */
                if ($user && password_verify($password, $user['password'])) {
                    unset($_SESSION['course_filters']);
                    /** Menyimpan data penting (esensial) ke dalam sesi (session). */
                    $_SESSION['user'] = [
                        'npk' => $user['npk'],
                        'username' => $user['npk'],
                        'name' => $user['fullname'],
                        'role' => $user['role'],
                        'golongan' => $user['golongan_id'],
                        'jabatan' => $user['position_id'],
                    ];

                    header('Location: ?route=/pilih-peran');
                    exit();
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
    }

    /**
     * Menampilkan Halaman Pemilihan Peran (Role Selection).
     *
     * Setelah pengguna berhasil login, antarmuka ini disajikan agar pengguna
     * dapat memilih mode masuk (contoh: sebagai Student atau Admin/Teacher)
     * sesuai dengan hak akses yang terdaftar pada akun mereka.
     *
     * @return void Merender halaman pemilihan peran jika sesi tersedia, jika tidak kembali ke login.
     */
    public function roleSelection()
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ?route=/login');
            exit();
        }
        $this->view('DX_LMS_M_Auth_Role');
    }

    /**
     * Memproses Penyimpanan Pilihan Peran ke dalam Sesi.
     *
     * Menerima input peran yang dipilih pengguna (POST), menyimpannya sebagai
     * 'active_role' di dalam memori sesi, lalu merutekan pengguna ke dasbor yang sesuai.
     *
     * @return void Redirect ke dasbor target (Student/Teacher/Admin).
     */
    public function setRole()
    {
        session_start();
        if (isset($_POST['role'])) {
            unset($_SESSION['course_filters']);
            $_SESSION['active_role'] = $_POST['role'];
            if ($_POST['role'] === 'student') {
                header('Location: ?route=/student');
            } elseif ($_POST['role'] === 'teacher') {
                header('Location: ?route=/teacher');
            } else {
                header('Location: ?route=/pilih-peran');
            }
            exit();
        }
    }

    /**
     * Memproses Keluar Sistem (Logout).
     *
     * Menghancurkan seluruh data sesi yang aktif pada server untuk memutuskan
     * status login pengguna secara aman, kemudian mengembalikan pengguna ke halaman login.
     *
     * @return void Redirect otomatis ke rute '/login'.
     */
    public function logout()
    {
        session_start();
        session_destroy();
        header('Location: ?route=/login');
        exit();
    }
}
