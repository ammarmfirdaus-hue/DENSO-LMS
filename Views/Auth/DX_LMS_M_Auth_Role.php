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

$currentUser = $_SESSION['user'] ?? [];
$userName = $currentUser['fullname'] ?? ($currentUser['name'] ?? 'Pengguna');
$userNpk = $currentUser['npk'] ?? '';
$baseRole = $currentUser['role'] ?? 'teacher';
$availableRoles = $_SESSION['available_roles'] ?? (
    $baseRole === 'admin' ? ['student', 'teacher', 'admin'] : ['student', 'teacher']
);
$activeRole = $_SESSION['active_role'] ?? ($baseRole === 'admin' ? 'admin' : ($baseRole === 'teacher' ? 'teacher' : 'student'));
if (!in_array($activeRole, $availableRoles, true)) {
    $activeRole = in_array('teacher', $availableRoles, true) ? 'teacher' : 'student';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <?php include __DIR__ . '/../../includes/components/DX_LMS_M_Tailwind_Head.php'; ?>
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
            margin-bottom: 24px;
        }

        .pilih-peran-header .logo-text {
            font-family: 'Inter', sans-serif;
            font-size: 32px;
            font-weight: 700;
            color: #e20613;
            letter-spacing: -0.03em;
            line-height: 1;
            margin-bottom: 12px;
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
            height: 96px;
            background-color: #eae7e7;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pilih-peran-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center 25%;
            display: block;
        }

        .pilih-peran-img-gradient {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50%;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.35), transparent);
        }

        .pilih-peran-card-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
        }

        .pilih-peran-card-title {
            font-family: 'Inter', sans-serif;
            font-size: 17px;
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
            width: 26px;
            height: 26px;
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
            <div class="absolute inset-0 bg-cover bg-left bg-no-repeat z-0 opacity-100" style="background-image: url('<?= $base_path ?>assets/img/pilih-peran/DX_LMS_M_Pilih_Peran_Left.png');"></div>

            <!-- Premium Dark Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-[#0b0e14]/95 from-[20%] via-[#0b0e14]/70 to-[#0b0e14]/10 z-10"></div>

            <!-- Content -->
            <div class="relative z-20 flex-1 flex flex-col justify-end p-8 md:p-12 lg:p-16 lg:px-20 w-full max-w-2xl mx-auto lg:mx-0 lg:ml-auto xl:ml-0 pb-10 lg:pb-12">

                <div class="mt-auto mb-6">
                    <!-- Title & Logo -->
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-[56px] h-[56px] bg-gradient-to-br from-[#d42a2a] to-[#a01a1a] rounded-xl flex items-center justify-center shadow-[0_0_25px_rgba(212,42,42,0.5)] shrink-0">
                            <span class="material-symbols-outlined text-[28px] text-white">groups</span>
                        </div>
                        <h2 class="text-2xl lg:text-3xl font-bold text-white tracking-tight leading-tight">
                            Peran Pembelajaran
                        </h2>
                    </div>

                    <!-- Description -->
                    <p class="text-[14px] lg:text-[15px] text-gray-200 leading-relaxed max-w-[460px] text-justify mb-4">
                        Sistem akan menyesuaikan modul dan analitik sesuai dengan tanggung jawab dan tingkat akses yang Anda miliki.
                    </p>

                </div>

                <!-- Footer -->
                <div>
                    <div class="w-12 h-[1px] bg-white/30 mb-4"></div>
                    <p class="text-[11px] lg:text-xs text-gray-300 font-medium">© Learning Management System</p>
                </div>
            </div>
        </div>

        <!-- Right Content Panel -->
        <div class="flex-1 w-full flex flex-col justify-center items-center p-6 lg:p-8 bg-surface overflow-y-auto">
            <div class="w-full max-w-[420px] flex flex-col justify-center my-auto py-2">
                <!-- Header -->
                <div class="pilih-peran-header">
                    <div class="logo-text">LMS</div>
                    <h1>Pilih Peran</h1>
                    <p>Pilih peran yang akan Anda gunakan saat ini</p>
                </div>

                <!-- Current User Identity Banner -->
                <div class="mb-5 p-3 rounded-2xl bg-surface-container/60 border border-outline-variant/40 flex items-center justify-between">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs shrink-0">
                            <?= strtoupper(substr($userName, 0, 1)) ?>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-on-surface truncate"><?= htmlspecialchars($userName) ?></p>
                            <p class="text-[11px] text-on-surface-variant truncate">NPK: <?= htmlspecialchars($userNpk) ?> · Akun <?= ucfirst($baseRole) ?></p>
                        </div>
                    </div>
                    <a href="?route=/login&switch=1" class="text-xs text-primary font-semibold hover:underline shrink-0 ml-3 flex items-center gap-1" title="Login Akun Lain">
                        <span class="material-symbols-outlined text-[14px]">swap_horiz</span>
                        <span>Login Akun Lain</span>
                    </a>
                </div>

                <!-- Role Selection Form -->
                <form method="POST" action="?route=/auth/set-role" id="formPilihPeran" class="w-full">
                    <div class="space-y-3">
                        <!-- Student Card -->
                        <?php if (in_array('student', $availableRoles, true)): ?>
                        <div class="pilih-peran-card <?= $activeRole === 'student' ? 'selected' : '' ?>" data-role="student" id="roleStudent">
                            <div class="pilih-peran-img-container">
                                <img src="assets/img/pilih-peran/DX_LMS_M_Student.png" alt="Student" onerror="this.style.display='none'">
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
                        <?php endif; ?>

                        <!-- Teacher Card -->
                        <?php if (in_array('teacher', $availableRoles, true)): ?>
                        <div class="pilih-peran-card <?= $activeRole === 'teacher' ? 'selected' : '' ?>" data-role="teacher" id="roleTeacher">
                            <div class="pilih-peran-img-container">
                                <img src="assets/img/pilih-peran/DX_LMS_M_Teacher.png" alt="Teacher" onerror="this.style.display='none'">
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
                        <?php endif; ?>

                        <!-- Admin Card -->
                        <?php if (in_array('admin', $availableRoles, true)): ?>
                        <div class="pilih-peran-card <?= $activeRole === 'admin' ? 'selected' : '' ?>" data-role="admin" id="roleAdmin">
                            <div class="pilih-peran-img-container">
                                <img src="assets/img/pilih-peran/DX_LMS_M_Admin.png" alt="Admin" onerror="this.style.display='none'">
                                <div class="pilih-peran-img-gradient"></div>
                            </div>
                            <div class="pilih-peran-card-content">
                                <div class="pilih-peran-card-text">
                                    <div class="pilih-peran-card-title">Admin</div>
                                    <div class="pilih-peran-card-desc">Kelola pengguna, master data, hak akses sistem, dan analitik.</div>
                                </div>
                                <div class="pilih-peran-indicator">
                                    <span class="material-symbols-outlined">check</span>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Hidden input for role value -->
                    <input type="hidden" name="role" id="selectedRole" value="<?= htmlspecialchars($activeRole, ENT_QUOTES, 'UTF-8') ?>">

                    <!-- Submit Button -->
                    <div class="mt-6">
                        <button type="submit" class="w-full h-11 bg-primary-container text-on-primary font-label-md rounded-full shadow-[0_4px_12px_rgba(226,6,19,0.25)] hover:bg-primary active:scale-95 transition-all flex items-center justify-center gap-2" id="btnMasuk">
                            Masuk
                            <span class="material-symbols-outlined" style="font-size: 20px;">arrow_forward</span>
                        </button>
                    </div>

                    <!-- Switch Account Link -->
                    <div class="mt-4 text-center space-y-1">
                        <div>
                            <a href="?route=/login&switch=1" class="text-xs text-on-surface-variant hover:text-primary transition-colors font-medium inline-flex items-center gap-1">
                                <span>Ingin buka akun pengajar/admin lain?</span>
                                <span class="font-bold underline text-primary">Login Akun Lain</span>
                            </a>
                        </div>
                        <div>
                            <a href="?route=/auth/logout" class="text-[11px] text-on-surface-variant/70 hover:text-error transition-colors">
                                Keluar dari semua sesi
                            </a>
                        </div>
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

    <!-- SweetAlert2 & DLS Helpers (loaded with defer for optimal performance) -->
    <script src="<?= $base_path ?>assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
    <script src="<?= $base_path ?>assets/js/DX_LMS_M_Helpers.js" defer></script>

</body>

</html>