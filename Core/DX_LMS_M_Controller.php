<?php
/**
 * Base Controller
 * Menyediakan fungsi-fungsi dasar untuk me-load Model dan View
 */
class DX_LMS_M_Controller {
    
    /**
     * Memuat file View dan mengirimkan data ke View
     * @param string $view Nama file View (tanpa ekstensi .php)
     * @param array $data Array data yang akan di-extract menjadi variabel
     */
    protected function view($view, $data = []) {
        // Extract array menjadi variabel terpisah (misal: ['name' => 'John'] menjadi $name)
        extract($data);
        
        $viewFile = 'Views/' . $view . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("View file $viewFile not found");
        }
    }

    /**
     * Memuat class Model
     * @param string $model Nama file Model
     * @return object Instance dari Model
     */
    protected function model($model) {
        $modelFile = 'Models/' . $model . '.php';
        if (file_exists($modelFile)) {
            require_once $modelFile;
            if (class_exists($model)) {
                return new $model();
            } else {
                die("Model class $model not found");
            }
        } else {
            die("Model file $modelFile not found");
        }
    }
}
