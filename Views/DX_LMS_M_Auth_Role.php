<?php
/**
 * DLS Pilih Peran Page
 * Learning Management System
 * 
 * Ref: pilih_peran
 */

$page_title = 'Pilih Peran - DLS';
$base_path = '';
$page_css = ['assets/css/student/DX_LMS_M_Pilih_Peran.css'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include __DIR__ . '/../includes/components/DX_LMS_M_Head.php'; ?>
</head>
<body class="bg-background">

<div class="pilih-peran-wrapper">
    <!-- Header -->
    <div class="pilih-peran-header">
        <div class="logo-text">LMS</div>
        <h1>Pilih Peran</h1>
        <p>Pilih peran yang akan Anda gunakan saat ini</p>
    </div>

    <!-- Role Selection Form -->
    <form method="POST" action="?route=/auth/set-role" id="formPilihPeran">
        <div class="pilih-peran-cards">
            <!-- Karyawan/Student Card -->
            <div class="pilih-peran-card selected" data-role="student" id="roleStudent">
                <div class="pilih-peran-img-container">
                    <img src="assets/images/pilih-peran/DX_LMS_M_Student.png" alt="Student" onerror="this.style.display='none'">
                    <div class="pilih-peran-img-gradient"></div>
                </div>
                <div class="pilih-peran-card-content">
                    <div class="pilih-peran-card-text">
                        <div class="pilih-peran-card-title">Student</div>
                        <div class="pilih-peran-card-desc">Belajar materi, kerjakan quiz, isi absensi, dan lihat capaian.</div>
                    </div>
                    <div class="pilih-peran-indicator">
                        <span class="material-symbols-outlined">check</span>
                    </div>
                </div>
            </div>

            <!-- Teacher Card -->
            <div class="pilih-peran-card" data-role="teacher" id="roleTeacher">
                <div class="pilih-peran-img-container">
                    <img src="assets/images/pilih-peran/DX_LMS_M_Teacher.png" alt="Teacher" onerror="this.style.display='none'">
                    <div class="pilih-peran-img-gradient"></div>
                </div>
                <div class="pilih-peran-card-content">
                    <div class="pilih-peran-card-text">
                        <div class="pilih-peran-card-title">Teacher</div>
                        <div class="pilih-peran-card-desc">Kelola materi, buat quiz, dan pantau progress peserta.</div>
                    </div>
                    <div class="pilih-peran-indicator">
                        <span class="material-symbols-outlined">check</span>
                    </div>
                </div>
            </div>
        </div>


        <!-- Hidden input for role value -->
        <input type="hidden" name="role" id="selectedRole" value="student">

        <!-- Submit Button -->
        <div class="pilih-peran-actions">
            <button type="submit" class="dls-btn dls-btn-primary dls-btn-block dls-btn-rounded" id="btnMasuk">
                Masuk
                <span class="material-symbols-outlined" style="font-size: 20px;">arrow_forward</span>
            </button>
        </div>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="<?= $base_path ?>assets/js/DX_LMS_M_Bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.pilih-peran-card');
    const hiddenInput = document.getElementById('selectedRole');

    cards.forEach(card => {
        card.addEventListener('click', function() {
            // Hapus pilihan dari semua
            cards.forEach(c => c.classList.remove('selected'));
            // Tambahkan pilihan ke yang diklik
            this.classList.add('selected');
            // Perbarui input tersembunyi
            hiddenInput.value = this.dataset.role;
        });
    });
});
</script>

</body>
</html>
