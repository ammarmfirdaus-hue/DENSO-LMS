<?php
/**
 * Halaman Masuk LMS (LMS Login Page).
 *
 * Antarmuka autentikasi pengguna. Meminta masukan NPK dan kata sandi
 * untuk memverifikasi identitas pengguna sebelum mengakses sistem.
 *
 * Ref: login_screen_clean_header
 */

$page_title = 'Login - Learning Management System';
$base_path = '';
?>


<!DOCTYPE html>
<html lang="id">

<head>
    <?php include __DIR__ . '/../../includes/components/DX_LMS_M_Tailwind_Head.php'; ?>
    <style>
        .login-wrapper {
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        @media (min-width: 768px) {
            .login-wrapper {
                flex-direction: row;
                padding: 0;
            }
        }

        .login-logo {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-logo-text {
            font-family: 'Inter', sans-serif;
            font-size: 40px;
            font-weight: 700;
            color: #e20613;
            letter-spacing: -0.03em;
            line-height: 1;
        }

        .login-logo-subtitle {
            font-family: 'Inter', sans-serif;
            font-size: 11px;
            font-weight: 500;
            color: #5e3f3b;
            letter-spacing: 0.15em;
            margin-top: 4px;
        }

        /* Force hide Edge/Chrome password reveal icons */
        input::-ms-reveal,
        input::-ms-clear,
        input::-webkit-credentials-auto-fill-button,
        input::-webkit-contacts-auto-fill-button {
            display: none !important;
            visibility: hidden;
            pointer-events: none;
        }
    </style>
</head>

<body class="bg-surface text-on-surface font-sans antialiased">

    <div class="min-h-[100dvh] flex flex-col lg:flex-row bg-surface w-full">
        <!-- Left Image Panel / Branding Area -->
        <div class="hidden lg:flex flex-col w-full lg:flex-1 bg-[#0b0e14] relative overflow-hidden min-h-[600px] lg:min-h-[100dvh]">
            <!-- Background Image -->
            <!-- Changed to bg-left to ensure the worker's body is fully visible -->
            <div class="absolute inset-0 bg-cover bg-left bg-no-repeat z-0 opacity-100" style="background-image: url('<?= $base_path ?>assets/img/login/DX_LMS_M_Login.png');"></div>

            <!-- Premium Dark Overlay (More transparent at bottom) -->
            <div class="absolute inset-0 bg-gradient-to-t from-[#0b0e14]/95 from-[20%] via-[#0b0e14]/70 to-[#0b0e14]/10 z-10"></div>

            <!-- Content -->
            <div class="relative z-20 flex flex-col justify-end h-full p-8 md:p-12 lg:p-16 lg:px-20 w-full max-w-2xl mx-auto lg:mx-0 lg:ml-auto xl:ml-0 pb-10 lg:pb-12">

                <div class="mt-auto mb-6">
                    <!-- Title & Logo (Side by side) -->
                    <div class="flex items-center gap-4 mb-6">
                        <!-- Logo -->
                        <div class="w-[56px] h-[56px] bg-gradient-to-br from-[#d42a2a] to-[#a01a1a] rounded-xl flex items-center justify-center shadow-[0_0_25px_rgba(212,42,42,0.5)] shrink-0">
                            <span class="material-symbols-outlined text-[28px] text-white">school</span>
                        </div>
                        <!-- Title -->
                        <h2 class="text-2xl lg:text-3xl font-bold text-white tracking-tight leading-tight">
                            Learning Management System
                        </h2>
                    </div>

                    <!-- Description (Justified) -->
                    <p class="text-[14px] lg:text-[15px] text-gray-200 leading-relaxed max-w-[460px] text-justify mb-10">
                        Platform pembelajaran digital terintegrasi untuk meningkatkan kompetensi, produktivitas, dan standar kualitas operasional secara berkelanjutan.
                    </p>

                    <!-- Highlights Grid with Dividers -->
                    <div class="flex flex-row items-stretch max-w-[460px] gap-0 divide-x divide-white/20">
                        <div class="flex-1 flex flex-col items-center lg:items-start text-center lg:text-left pr-4">
                            <span class="material-symbols-outlined text-[#e20613] text-[28px] mb-2 drop-shadow-md">menu_book</span>
                            <span class="text-[11px] lg:text-[12px] font-semibold text-white/90">Learning<br>Path</span>
                        </div>
                        <div class="flex-1 flex flex-col items-center lg:items-start text-center lg:text-left px-4">
                            <span class="material-symbols-outlined text-[#e20613] text-[28px] mb-2 drop-shadow-md">assignment_turned_in</span>
                            <span class="text-[11px] lg:text-[12px] font-semibold text-white/90">Assessment</span>
                        </div>
                        <div class="flex-1 flex flex-col items-center lg:items-start text-center lg:text-left px-4">
                            <span class="material-symbols-outlined text-[#e20613] text-[28px] mb-2 drop-shadow-md">insights</span>
                            <span class="text-[11px] lg:text-[12px] font-semibold text-white/90">Progress<br>Tracking</span>
                        </div>
                        <div class="flex-1 flex flex-col items-center lg:items-start text-center lg:text-left pl-4">
                            <span class="material-symbols-outlined text-[#e20613] text-[28px] mb-2 drop-shadow-md">workspace_premium</span>
                            <span class="text-[11px] lg:text-[12px] font-semibold text-white/90">Certification</span>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="">
                    <div class="w-12 h-[1px] bg-white/30 mb-4"></div>
                    <p class="text-[11px] lg:text-xs text-gray-300 font-medium">© Learning Management System</p>
                </div>
            </div>
        </div>

        <!-- Right Login Form -->
        <div class="flex-1 w-full flex flex-col justify-center items-center p-8 bg-surface py-16 lg:py-8">
            <!-- Logo -->
            <div class="login-logo">
                <div class="login-logo-text">LMS</div>
                <div class="login-logo-subtitle">LEARNING MANAGEMENT SYSTEM</div>
            </div>

            <!-- Heading -->
            <div class="text-center mb-8 w-full max-w-sm">
                <h1 class="text-headline-md font-bold text-on-surface">Selamat datang!</h1>
                <p class="text-body-md text-on-surface-variant mt-1">Masuk dengan akun karyawan Anda</p>
            </div>

            <!-- Login Form -->
            <form class="w-full max-w-sm space-y-4" method="POST" action="?route=/auth/process" id="loginForm">
                <input type="hidden" name="_csrf" value="<?= e($csrf_token ?? '') ?>">
                <?php if (isset($_GET['error'])): ?>
                <div class="flex items-center gap-2 p-3 rounded-xl bg-error-container/30 text-on-error-container">
                    <span class="material-symbols-outlined" style="font-size:20px;">error</span>
                    <span class="text-label-md">NPK dan Password wajib diisi / Salah.</span>
                </div>
                <?php endif; ?>

                <!-- NPK / Email Input -->
                <div class="relative w-full">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant">person</span>
                    <input type="text" class="w-full h-[52px] pl-12 pr-4 bg-surface-container-lowest border border-outline-variant rounded-lg font-sans text-body-lg text-on-surface placeholder:text-on-surface-variant/60 focus:border-secondary focus:ring-2 focus:ring-secondary/15 outline-none transition-all" name="npk" id="inputNpk" placeholder="NPK atau Email" value="<?= htmlspecialchars($_POST['npk'] ?? '') ?>" autocomplete="username" required>
                </div>

                <!-- Password Input -->
                <div class="relative w-full">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant">lock</span>
                    <input type="password" class="w-full h-[52px] pl-12 pr-12 bg-surface-container-lowest border border-outline-variant rounded-lg font-sans text-body-lg text-on-surface placeholder:text-on-surface-variant/60 focus:border-secondary focus:ring-2 focus:ring-secondary/15 outline-none transition-all" name="password" id="inputPassword" placeholder="Password" value="" autocomplete="current-password" required>
                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 mt-[2px] text-on-surface-variant hover:bg-surface-container-low p-1 rounded-full transition-colors" id="togglePassword" aria-label="Tampilkan password">
                        <span class="material-symbols-outlined" style="font-size:20px;">visibility_off</span>
                    </button>
                </div>

                <!-- Forgot Password Link -->
                <div class="text-right">
                    <a href="#" class="text-label-md text-secondary hover:underline">Lupa password?</a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full h-11 bg-secondary text-on-secondary font-label-md rounded-full shadow-[0_4px_12px_rgba(67,85,185,0.2)] hover:bg-[#293ca0] active:scale-95 transition-all" id="btnLogin">
                    Masuk
                </button>
            </form>

            <!-- Footer -->
            <div class="mt-8 text-center">
                <p class="text-body-md text-on-surface-variant">Belum punya akun? <a href="#" class="text-secondary font-medium hover:underline">Hubungi Admin</a></p>
            </div>
        </div>
    </div>

    <!-- Login JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('inputPassword');

            if (toggleBtn && passwordInput) {
                toggleBtn.addEventListener('click', function() {
                    const icon = this.querySelector('.material-symbols-outlined');
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        icon.textContent = 'visibility';
                    } else {
                        passwordInput.type = 'password';
                        icon.textContent = 'visibility_off';
                    }
                });
            }

            const form = document.getElementById('loginForm');
            const inputs = form.querySelectorAll('input[required]');
            const submitButton = document.getElementById('btnLogin');

            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.value.trim() === '') {
                        this.classList.add('border-error');
                        this.classList.remove('border-outline-variant');
                    } else {
                        this.classList.remove('border-error');
                        this.classList.add('border-outline-variant');
                    }
                });

                input.addEventListener('focus', function() {
                    this.classList.remove('border-error');
                    this.classList.add('border-outline-variant');
                });
            });

            form.addEventListener('submit', function(event) {
                if (!form.reportValidity()) {
                    event.preventDefault();
                    return;
                }
                submitButton.disabled = true;
                submitButton.setAttribute('aria-busy', 'true');
                submitButton.textContent = 'Memproses...';
            });

            window.addEventListener('pageshow', function() {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.removeAttribute('aria-busy');
                    submitButton.textContent = 'Masuk';
                }
            });
        });
    </script>

    <!-- SweetAlert2 & DLS Helpers (loaded with defer for optimal performance) -->
    <script src="<?= $base_path ?>assets/js/DX_LMS_M_SweetAlert2.min.js" defer></script>
    <script src="<?= $base_path ?>assets/js/DX_LMS_M_Helpers.js" defer></script>

</body>

</html>
