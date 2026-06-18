<?php
/**
 * Core Router
 * Handles URI mapping to Controllers
 */
class DX_LMS_Router {
    private $routes = [];

    /**
     * Menambahkan route baru
     * @param string $path URL path
     * @param string $action Controller@Method
     */
    public function add($path, $action) {
        $this->routes[$path] = $action;
    }

    /**
     * Memproses request URI ke Controller yang tepat
     * @param string $uri The requested URI
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
