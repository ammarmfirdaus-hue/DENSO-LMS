<?php
/**
 * Kelas Pengontrol Pembuatan Topik Forum Baru (New Forum Topic Controller).
 *
 * Mengelola proses interaksi ketika siswa ingin membuat ruang diskusi
 * atau topik forum yang baru terkait dengan suatu materi atau kursus.
 */
class DX_LMS_T_Student_Forum_New extends DX_LMS_M_Controller
{
    /**
     * Menampilkan Halaman Formulir Topik Baru.
     *
     * Merender antarmuka pengguna yang memuat formulir masukan (input)
     * untuk judul dan konten diskusi (thread) baru.
     *
     * @return void
     */
    public function index()
    {
        $this->view('DX_LMS_T_Student_Forum_New');
    }
}
