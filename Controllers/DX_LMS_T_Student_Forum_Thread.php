<?php
/**
 * Kelas Pengontrol Rincian Thread Forum (Forum Thread Detail Controller).
 * 
 * Berfungsi untuk mengambil dan menyajikan keseluruhan isi percakapan
 * pada sebuah topik diskusi, termasuk kiriman utama (parent post) beserta 
 * daftar seluruh tanggapan (replies) dari peserta lain.
 */
class DX_LMS_T_Student_Forum_Thread extends DX_LMS_Controller {
    /**
     * Menampilkan Halaman Rincian Topik Diskusi.
     * 
     * Memuat file tampilan khusus untuk membaca seluruh percakapan
     * dalam satu topik (thread) secara terurut.
     * 
     * @return void
     */
    public function index() {
        require_once __DIR__ . '/../Views/DX_LMS_T_Student_Forum_Thread.php';
    }
}
