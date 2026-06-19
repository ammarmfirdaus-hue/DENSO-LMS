<?php
/**
 * Kelas Dasar (Base) Controller.
 * 
 * Kelas ini berfungsi sebagai pondasi utama untuk seluruh Controller dalam arsitektur MVC (Model-View-Controller).
 * Menyediakan metode-metode standar untuk merender tampilan (View) dan menginisialisasi model data (Model),
 * sehingga mencegah duplikasi kode pada controller turunan.
 */
class DX_LMS_Controller {
    
    /**
     * Memuat dan Merender File Tampilan (View).
     * 
     * Berfungsi untuk menghubungkan controller dengan antarmuka pengguna.
     * Menerima sekumpulan data dalam bentuk array asosiatif, mengekstraknya menjadi variabel 
     * yang dapat diakses langsung oleh file view, lalu memuat file view tersebut.
     * 
     * @param string $view Nama file view yang ingin dimuat (tanpa ekstensi .php).
     * @param array $data Array asosiatif berisi data yang akan dikirimkan dan dirender di view.
     * 
     * @return void
     */
    protected function view($view, $data = []) {
        // Ekstrak array menjadi variabel terpisah (contoh: ['nama' => 'Budi'] menjadi $nama = 'Budi')
        extract($data);
        
        $viewFile = 'Views/' . $view . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("File View $viewFile tidak ditemukan dalam sistem.");
        }
    }

    /**
     * Memuat dan Menginisialisasi Kelas Model.
     * 
     * Digunakan oleh controller untuk memanggil model yang mengelola logika basis data (database).
     * Akan memverifikasi keberadaan file dan kelas model sebelum melakukan instansiasi objek.
     * 
     * @param string $model Nama kelas model sekaligus nama file model tersebut.
     * 
     * @return object Mengembalikan instance (objek) dari kelas model yang diminta.
     */
    protected function model($model) {
        $modelFile = 'Models/' . $model . '.php';
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
