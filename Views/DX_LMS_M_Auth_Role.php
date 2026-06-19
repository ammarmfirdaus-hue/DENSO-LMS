<?php
/**
 * Tampilan Pemilihan Peran DLS (DLS Role Selection View).
 * 
 * Antarmuka bagi pengguna yang memiliki peran ganda (misal: Siswa dan Pengajar)
 * untuk memilih mode akses yang akan digunakan saat ini.
 * 
 * Ref: pilih_peran
 */

$page_title = 'Pilih Peran - LMS';
$base_path = '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include __DIR__ . '/../includes/components/DX_LMS_M_Tailwind_Head.php'; ?>
    <style>
        .pilih-peran-wrapper {
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }
        @media (min-width: 768px) {
            .pilih-peran-wrapper {
                flex-direction: row;
                padding: 0;
            }
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
            background-color: #eae7e7;
            overflow: hidden;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }
        .pilih-peran-img-container img {
            width: 100%;
            height: auto;
            object-fit: cover;
            object-position: top;
            display: block;
            margin-bottom: -25%; /* Memotong 25% dari bawah gambar */
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

<div class="min-h-[100dvh] h-[100dvh] flex flex-col lg:flex-row bg-surface w-full lg:overflow-hidden overflow-y-auto">
    <!-- Desktop Left Image Panel / Branding Area -->
    <div class="hidden lg:flex flex-col w-full lg:flex-1 bg-[#0b0e14] relative overflow-hidden min-h-[600px] lg:min-h-[100dvh]">
        <!-- Background Image -->
        <!-- bg-left ensures the worker's body is fully visible -->
        <div class="absolute inset-0 bg-[url('<?= $base_path ?>assets/img/pilih-peran/DX_LMS_M_Pilih_Peran_Left.png')] bg-cover bg-left bg-no-repeat z-0 opacity-100"></div>
        
        <!-- Premium Dark Overlay (More transparent at bottom) -->
        <div class="absolute inset-0 bg-gradient-to-t from-[#0b0e14]/95 from-[20%] via-[#0b0e14]/70 to-[#0b0e14]/10 z-10"></div>
        
        <!-- Content -->
        <div class="relative z-20 flex-1 flex flex-col justify-end p-8 md:p-12 lg:p-16 lg:px-20 w-full max-w-2xl mx-auto lg:mx-0 lg:ml-auto xl:ml-0 pb-10 lg:pb-12">
            
            <div class="mt-auto mb-6">
                <!-- Title & Logo (Side by side) -->
                <div class="flex items-center gap-4 mb-6">
                    <!-- Logo -->
                    <div class="w-[56px] h-[56px] bg-gradient-to-br from-[#d42a2a] to-[#a01a1a] rounded-xl flex items-center justify-center shadow-[0_0_25px_rgba(212,42,42,0.5)] shrink-0">
                        <span class="material-symbols-outlined text-[28px] text-white">groups</span>
                    </div>
                    <!-- Title -->
                    <h2 class="text-2xl lg:text-3xl font-bold text-white tracking-tight leading-tight">
                        Peran Pembelajaran
                    </h2>
                </div>
                
                <!-- Description (Justified) -->
                <p class="text-[14px] lg:text-[15px] text-gray-200 leading-relaxed max-w-[460px] text-justify mb-4">
                    Sistem akan menyesuaikan modul dan analitik sesuai dengan tanggung jawab dan tingkat akses yang Anda miliki.
                </p>
                
            </div>

            <!-- Footer -->
            <div class="">
                <div class="w-12 h-[1px] bg-white/30 mb-4"></div>
                <p class="text-[11px] lg:text-xs text-gray-300 font-medium">© Learning Management System</p>
            </div>
        </div>
    </div>

    <!-- Right Content Panel -->
    <div class="flex-1 w-full flex flex-col justify-center items-center p-8 bg-surface">
        <div class="w-full max-w-[400px] flex flex-col justify-center">
            <!-- Header -->
            <div class="pilih-peran-header">
                <div class="logo-text">LMS</div>
                <h1>Pilih Peran</h1>
                <p>Pilih peran yang akan Anda gunakan saat ini</p>
            </div>

            <!-- Role Selection Form -->
            <form method="POST" action="?route=/auth/set-role" id="formPilihPeran" class="w-full">
                <div class="space-y-4">
                    <!-- Karyawan/Student Card -->
                    <div class="pilih-peran-card selected" data-role="student" id="roleStudent">
                        <div class="pilih-peran-img-container">
                            <img src="assets/img/pilih-peran/DX_LMS_M_Student.png" alt="Student" onerror="this.style.display='none'">
                            <div class="pilih-peran-img-gradient"></div>
                        </div>
                        <div class="pilih-peran-card-content py-3 lg:py-4 px-4">
                            <div class="pilih-peran-card-text">
                                <div class="pilih-peran-card-title text-base lg:text-lg">Student</div>
                                <div class="pilih-peran-card-desc text-[11px] lg:text-[12px]">Belajar materi, kerjakan quiz, isi absensi, dan lihat capaian.</div>
                            </div>
                            <div class="pilih-peran-indicator w-6 h-6 lg:w-7 lg:h-7">
                                <span class="material-symbols-outlined text-[14px] lg:text-[16px]">check</span>
                            </div>
                        </div>
                    </div>

                    <!-- Teacher Card -->
                    <div class="pilih-peran-card" data-role="teacher" id="roleTeacher">
                        <div class="pilih-peran-img-container">
                            <img src="assets/img/pilih-peran/DX_LMS_M_Teacher.png" alt="Teacher" onerror="this.style.display='none'">
                            <div class="pilih-peran-img-gradient"></div>
                        </div>
                        <div class="pilih-peran-card-content py-3 lg:py-4 px-4">
                            <div class="pilih-peran-card-text">
                                <div class="pilih-peran-card-title text-base lg:text-lg">Teacher</div>
                                <div class="pilih-peran-card-desc text-[11px] lg:text-[12px]">Kelola materi, buat quiz, dan pantau progress peserta.</div>
                            </div>
                            <div class="pilih-peran-indicator w-6 h-6 lg:w-7 lg:h-7">
                                <span class="material-symbols-outlined text-[14px] lg:text-[16px]">check</span>
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
    </div>
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
