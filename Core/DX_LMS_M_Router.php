<?php
/**
 * Kelas Pengatur Jalur Utama (Core Router).
 *
 * Kelas ini bertanggung jawab atas sistem perutean (routing) URL pada aplikasi.
 * Ia akan memetakan permintaan URI (Uniform Resource Identifier) dari peramban
 * ke pengontrol (Controller) dan metode pengontrol yang telah ditentukan.
 * Mendukung struktur subfolder per modul (Auth, Student, Teacher, Admin).
 */
class DX_LMS_M_Router
{
    private $routes = [];

    /**
     * Daftar subfolder modul yang tersedia di dalam direktori Controllers.
     *
     * Router akan mencari file Controller secara otomatis di dalam
     * subfolder-subfolder ini jika file tidak ditemukan langsung
     * di dalam direktori utama Controllers/.
     *
     * @var array
     */
    private $moduleFolders = ['Auth', 'Student', 'Teacher', 'Admin'];

    /**
     * Menambahkan Rute (Route) Baru ke dalam Daftar Rute.
     *
     * Fungsi ini menyimpan pemetaan antara jalur URL dengan aksi (Controller@Method)
     * ke dalam array memori untuk dicocokkan saat ada permintaan (request) masuk.
     *
     * @param string $path Jalur URL yang diakses (misal: '/login', '/student').
     * @param string $action Format aksi yang dieksekusi berupa 'NamaController@namaMetode'.
     *
     * @return void
     */
    public function add($path, $action)
    {
        $this->routes[$path] = $action;
    }

    /**
     * Mencari File Controller pada Direktori dan Subfolder Modul.
     *
     * Melakukan pencarian bertingkat: pertama di folder utama Controllers/,
     * kemudian di setiap subfolder modul (Auth/, Student/, Teacher/, Admin/).
     *
     * @param string $controller Nama kelas controller yang dicari.
     *
     * @return string|null Jalur (path) lengkap file controller, atau null jika tidak ditemukan.
     */
    private function findControllerFile($controller)
    {
        /** Memeriksa keberadaan berkas secara langsung di dalam direktori pengontrol. */
        $directPath = 'Controllers/' . $controller . '.php';
        if (file_exists($directPath)) {
            return $directPath;
        }

        /** Melakukan pencarian berkas di dalam subdirektori modul terkait. */
        foreach ($this->moduleFolders as $folder) {
            $subPath = 'Controllers/' . $folder . '/' . $controller . '.php';
            if (file_exists($subPath)) {
                return $subPath;
            }
        }

        return null;
    }

    /**
     * Menyalurkan dan Memproses Permintaan (Dispatch).
     *
     * Fungsi mesin utama yang membedah URI permintaan, menyingkirkan subdirektori
     * jika aplikasi tidak berjalan pada root, dan mencocokkan URI bersih ke dalam
     * daftar rute yang telah diregistrasi. Apabila cocok, fungsi ini mencari file
     * Controller di seluruh subfolder modul, menginisialisasi kelasnya,
     * dan mengeksekusi metode terkait.
     *
     * @param string $uri URI permintaan dari variabel global $_SERVER['REQUEST_URI'].
     *
     * @return void Menghentikan (die) alur jika file/kelas/metode tidak ditemukan, atau menghasilkan 404.
     */
    public function dispatch($uri)
    {
        /** Menghilangkan parameter URL (query string) jika tersedia. */
        $uri = strtok($uri, '?');

        /** Mengelola jalur basis apabila sistem dijalankan melalui subdirektori. */
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/' && $scriptName !== '\\') {
            $uri = str_replace($scriptName, '', $uri);
        }

        if (empty($uri)) {
            $uri = '/';
        }

        if (array_key_exists($uri, $this->routes)) {
            $action = $this->routes[$uri];
            [$controller, $method] = explode('@', $action);

            $controllerFile = $this->findControllerFile($controller);
            if ($controllerFile) {
                require_once $controllerFile;
                if (class_exists($controller)) {
                    $controllerObj = new $controller();
                    if (method_exists($controllerObj, $method)) {
                        $controllerObj->$method();
                        return;
                    } else {
                        die("Metode $method tidak ditemukan pada kelas $controller.");
                    }
                } else {
                    die("Kelas $controller tidak ditemukan di dalam file $controllerFile.");
                }
            } else {
                die("File Controller untuk kelas $controller tidak ditemukan di seluruh direktori.");
            }
        } else {
            /** Menangani rute yang tidak ditemukan (Kesalahan 404). */
            http_response_code(404);
            echo '404 — Halaman Tidak Ditemukan';
        }
    }
}
