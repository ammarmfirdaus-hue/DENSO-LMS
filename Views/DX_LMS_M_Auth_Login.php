<?php
/**
 * LMS Login Page
 * Learning Management System
 * 
 * Ref: login_screen_clean_header
 */

$page_title = 'Login - Learning Management System';
$base_path = '';
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <?php include __DIR__ . '/../includes/components/DX_LMS_Tailwind_Head.php'; ?>
    <style>
        .login-wrapper {
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
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
    </style>
</head>
<body class="bg-surface text-on-surface font-sans antialiased">

<div class="login-wrapper">
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
        <?php if (isset($_GET['error'])): ?>
        <div class="flex items-center gap-2 p-3 rounded-xl bg-error-container/30 text-on-error-container">
            <span class="material-symbols-outlined" style="font-size:20px;">error</span>
            <span class="text-label-md">NPK dan Password wajib diisi / Salah.</span>
        </div>
        <?php endif; ?>

        <!-- NPK / Email Input -->
        <div class="relative w-full">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant">person</span>
            <input type="text" 
                   class="w-full h-[52px] pl-12 pr-4 bg-surface-container-lowest border border-outline-variant rounded-lg font-sans text-body-lg text-on-surface placeholder:text-on-surface-variant/60 focus:border-secondary focus:ring-2 focus:ring-secondary/15 outline-none transition-all" 
                   name="npk" 
                   id="inputNpk"
                   placeholder="NPK atau Email" 
                   value="<?= htmlspecialchars($_POST['npk'] ?? '') ?>"
                   autocomplete="username"
                   required>
        </div>

        <!-- Password Input -->
        <div class="relative w-full">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant">lock</span>
            <input type="password" 
                   class="w-full h-[52px] pl-12 pr-12 bg-surface-container-lowest border border-outline-variant rounded-lg font-sans text-body-lg text-on-surface placeholder:text-on-surface-variant/60 focus:border-secondary focus:ring-2 focus:ring-secondary/15 outline-none transition-all" 
                   name="password" 
                   id="inputPassword"
                   placeholder="Password" 
                   value=""
                   autocomplete="current-password"
                   required>
            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:bg-surface-container-low p-1 rounded-full transition-colors" id="togglePassword" aria-label="Tampilkan password">
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
});
</script>

</body>
</html>
