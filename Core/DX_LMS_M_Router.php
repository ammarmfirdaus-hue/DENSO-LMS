<?php
/**
 * Kelas Pengatur Jalur (Core Router).
 * 
 * Kelas ini bertanggung jawab atas sistem perutean (routing) URL pada aplikasi.
 * Ia akan memetakan permintaan URI (Uniform Resource Identifier) dari peramban ke pengontrol
 * (Controller) dan metode pengontrol yang telah ditentukan secara spesifik.
 */
class DX_LMS_Router {
    private $routes = [];

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
    public function add($path, $action) {
        $this->routes[$path] = $action;
    }

    /**
     * Menyalurkan dan Memproses Permintaan (Dispatch).
     * 
     * Fungsi mesin utama yang membedah URI permintaan, menyingkirkan subdirektori 
     * jika aplikasi tidak berjalan pada root, dan mencocokkan URI bersih ke dalam
     * daftar rute yang telah diregistrasi. Apabila cocok, fungsi ini memuat file 
     * Controller, menginisialisasi kelasnya, dan mengeksekusi metode terkait.
     * 
     * @param string $uri The requested URI dari variabel global $_SERVER['REQUEST_URI'].
     * 
     * @return void Menghentikan (die) alur jika file/kelas/metode tidak ditemukan, atau menghasilkan 404.
     */
    public function dispatch($uri) {
        // Hapus query string jika ada
        $uri = strtok($uri, '?');
        
        // Menangani root subdirektori jika dijalankan dalam subfolder (tidak umum untuk php -S, tapi aman)
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/' && $scriptName !== '\\') {
            $uri = str_replace($scriptName, '', $uri);
        }
        
        if (empty($uri)) {
            $uri = '/';
        }

        if (array_key_exists($uri, $this->routes)) {
            $action = $this->routes[$uri];
            list($controller, $method) = explode('@', $action);
            
            $controllerFile = 'Controllers/' . $controller . '.php';
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
                if (class_exists($controller)) {
                    $controllerObj = new $controller();
                    if (method_exists($controllerObj, $method)) {
                        $controllerObj->$method();
                        return;
                    } else {
                        die("Metode $method not found in $controller");
                    }
                } else {
                    die("Kelas $controller not found");
                }
            } else {
                die("Controller file $controllerFile not found");
            }
        } else {
            // 404
            http_response_code(404);
            echo "404 Not Found";
        }
    }
}
