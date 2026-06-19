<?php
/**
 * LMS Pilih Peran Page
 * Learning Management System
 * 
 * Ref: pilih_peran
 */

$page_title = 'Pilih Peran - LMS';
$base_path = '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include __DIR__ . '/../includes/components/DX_LMS_Tailwind_Head.php'; ?>
    <style>
        .pilih-peran-wrapper {
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }
        .pilih-peran-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .pilih-peran-header .logo-text {
            font-family: 'Inter', sans-serif;
            font-size: 32px;
            font-weight: 700;
            color: #e20613;
            letter-spacing: -0.03em;
            line-height: 1;
            margin-bottom: 16px;
        }
        .pilih-peran-header h1 {
            font-family: 'Inter', sans-serif;
            font-size: 22px;
            font-weight: 700;
            color: #1b1c1c;
            margin-bottom: 4px;
        }
        .pilih-peran-header p {
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            color: #5e3f3b;
        }
        .pilih-peran-card {
            cursor: pointer;
            border: 2px solid #e9bcb6;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.2s ease;
            background: #ffffff;
        }
        .pilih-peran-card:hover {
            border-color: #5e3f3b;
        }
        .pilih-peran-card.selected {
            border-color: #e20613;
            background: rgba(226, 6, 19, 0.04);
            box-shadow: 0 4px 12px rgba(226, 6, 19, 0.15);
        }
        .pilih-peran-img-container {
            position: relative;
            width: 100%;
            height: 180px; /* Dipertinggi agar lebih banyak area vertikal gambar yang masuk */
            background-color: #eae7e7;
            overflow: hidden;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }
        .pilih-peran-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Kembali ke cover agar lebar gambar penuh tanpa ruang kosong */
            object-position: center 10%; /* Fokus titik crop 10% dari atas agar wajah aman */
        }
        .pilih-peran-img-gradient {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50%;
            background: linear-gradient(to top, rgba(0,0,0,0.3), transparent);
        }
        .pilih-peran-card-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px;
        }
        .pilih-peran-card-title {
            font-family: 'Inter', sans-serif;
            font-size: 18px;
            font-weight: 700;
            color: #1b1c1c;
        }
        .pilih-peran-card-desc {
            font-family: 'Inter', sans-serif;
            font-size: 12px;
            color: #5e3f3b;
            margin-top: 2px;
            line-height: 16px;
        }
        .pilih-peran-indicator {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 2px solid #e9bcb6;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }
        .pilih-peran-indicator span {
            font-size: 16px;
            color: transparent;
            transition: color 0.2s ease;
        }
        .pilih-peran-card.selected .pilih-peran-indicator {
            background-color: #e20613;
            border-color: #e20613;
        }
        .pilih-peran-card.selected .pilih-peran-indicator span {
            color: #ffffff;
        }
    </style>
</head>
<body class="bg-surface text-on-surface font-sans antialiased">

<div class="pilih-peran-wrapper">
    <!-- Header -->
    <div class="pilih-peran-header">
        <div class="logo-text">LMS</div>
        <h1>Pilih Peran</h1>
        <p>Pilih peran yang akan Anda gunakan saat ini</p>
    </div>

    <!-- Role Selection Form -->
    <form method="POST" action="?route=/auth/set-role" id="formPilihPeran" class="w-full max-w-sm">
        <div class="space-y-4">
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
        <div class="mt-8">
            <button type="submit" class="w-full h-11 bg-primary-container text-on-primary font-label-md rounded-full shadow-[0_4px_12px_rgba(226,6,19,0.25)] hover:bg-primary active:scale-95 transition-all flex items-center justify-center gap-2" id="btnMasuk">
                Masuk
                <span class="material-symbols-outlined" style="font-size: 20px;">arrow_forward</span>
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.pilih-peran-card');
    const hiddenInput = document.getElementById('selectedRole');

    cards.forEach(card => {
        card.addEventListener('click', function() {
            cards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            hiddenInput.value = this.dataset.role;
        });
    });
});
</script>

</body>
</html>
