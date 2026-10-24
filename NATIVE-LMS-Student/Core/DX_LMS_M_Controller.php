<?php
/**
 * Kelas Dasar (Base) Controller.
 *
 * Kelas ini berfungsi sebagai pondasi utama untuk seluruh Controller dalam
 * arsitektur MVC (Model-View-Controller). Menyediakan metode-metode standar
 * untuk merender tampilan (View) dan menginisialisasi model data (Model),
 * sehingga mencegah duplikasi kode pada controller turunan.
 *
 * Mendukung struktur subfolder per modul (Auth, Student, Teacher, Admin)
 * untuk pencarian file View dan Model secara otomatis.
 */
class DX_LMS_M_Controller
{
    /**
     * Daftar subfolder modul yang tersedia di dalam direktori Views dan Models.
     *
     * @var array
     */
    private $moduleFolders = [
        'Auth', 'Student', 'Student/Dashboard', 'Student/Courses', 'Student/Learning',
        'Student/Forum', 'Student/History', 'Student/Notifications', 'Student/Profile',
        'Teacher', 'Admin',
    ];

    /**
     * Memuat dan Merender File Tampilan (View).
     *
     * Berfungsi untuk menghubungkan controller dengan antarmuka pengguna.
     * Menerima sekumpulan data dalam bentuk array asosiatif, mengekstraknya
     * menjadi variabel yang dapat diakses langsung oleh file view, lalu
     * memuat file view tersebut.
     *
     * Pencarian file View dilakukan secara bertingkat:
     * 1. Langsung di folder Views/
     * 2. Di subfolder modul (Views/Auth/, Views/Student/, dst.)
     *
     * @param string $view Nama file view yang ingin dimuat (tanpa ekstensi .php).
     * @param array $data Array asosiatif berisi data yang akan dikirimkan dan dirender di view.
     *
     * @return void
     */
    protected function view($view, $data = [])
    {
        /** Mengekstrak susunan data (array) menjadi variabel terpisah untuk kemudahan akses. */
        extract($data, EXTR_SKIP);

        $viewFile = $this->findViewFile($view);
        if ($viewFile) {
            require_once $viewFile;
        } else {
            die("File View '$view.php' tidak ditemukan di seluruh direktori Views.");
        }
    }

    /**
     * Mencari File View pada Direktori dan Subfolder Modul.
     *
     * Melakukan pencarian bertingkat: pertama di folder utama Views/,
     * kemudian di setiap subfolder modul (Auth/, Student/, Teacher/, Admin/).
     *
     * @param string $view Nama file view (tanpa ekstensi .php).
     *
     * @return string|null Jalur (path) lengkap file view, atau null jika tidak ditemukan.
     */
    private function findViewFile($view)
    {
        /** Melakukan pencarian berkas langsung di dalam direktori Views. */
        if (!is_string($view) || !preg_match('/^[A-Za-z0-9_-]+$/D', $view)) {
            return null;
        }

        $directPath = APP_ROOT . '/Views/' . $view . '.php';
        if (file_exists($directPath)) {
            return $directPath;
        }

        /** Melakukan pencarian berkas di dalam subdirektori modul terkait. */
        foreach ($this->moduleFolders as $folder) {
            $subPath = APP_ROOT . '/Views/' . $folder . '/' . $view . '.php';
            if (file_exists($subPath)) {
                return $subPath;
            }
        }

        return null;
    }

    /**
     * Memuat dan Menginisialisasi Kelas Model.
     *
     * Digunakan oleh controller untuk memanggil model yang mengelola logika
     * basis data (database). Akan memverifikasi keberadaan file dan kelas
     * model sebelum melakukan instansiasi objek.
     *
     * @param string $model Nama kelas model sekaligus nama file model tersebut.
     *
     * @return object Mengembalikan instance (objek) dari kelas model yang diminta.
     */
    protected function model($model)
    {
        if (!is_string($model) || !preg_match('/^[A-Za-z][A-Za-z0-9_]*$/D', $model)) {
            throw new InvalidArgumentException('Nama model tidak valid.');
        }

        $modelFile = APP_ROOT . '/Models/' . $model . '.php';
        if (file_exists($modelFile)) {
            require_once $modelFile;
            if (class_exists($model)) {
                return new $model();
            } else {
                die("Kelas Model $model tidak ditemukan di dalam file $modelFile.");
            }
        } else {
            die("File Model $modelFile tidak ditemukan dalam sistem.");
        }
    }
}
