<?php
/**
 * Learning Management System
 * 
 * Ref: login_screen_clean_header
 */

$page_title = 'Login - Learning Management System';
$base_path = '';
$page_css = ['assets/css/student/DX_LMS_M_Login.css'];

// Pesan error dapat dilemparkan via GET atau array data
$error_message = isset($_GET['error']) ? 'NPK dan Password wajib diisi / Salah.' : '';
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <?php include __DIR__ . '/../includes/components/DX_LMS_Head.php'; ?>
</head>
<body class="bg-background">

<div class="login-wrapper">
    <!-- Logo -->
    <div class="login-logo">
        <div class="login-logo-text">LMS</div>
        <div class="login-logo-subtitle">LEARNING MANAGEMENT SYSTEM</div>
    </div>

    <!-- Heading -->
    <div class="login-heading">
        <h1>Selamat datang!</h1>
        <p>Masuk dengan akun karyawan Anda</p>
    </div>

    <!-- Login Form -->
    <form class="login-form" method="POST" action="?route=/auth/process" id="loginForm">
        <?php if ($error_message): ?>
        <div class="d-flex align-items-center gap-2 p-3 rounded-3" style="background-color: var(--dls-error-container); color: var(--dls-on-error-container);">
            <span class="material-symbols-outlined" style="font-size:20px;">error</span>
            <span class="dls-label-md"><?= e($error_message) ?></span>
        </div>
        <?php endif; ?>

        <!-- NPK / Email Input -->
        <div class="dls-input-group">
            <span class="material-symbols-outlined dls-input-icon">person</span>
            <input type="text" 
                   class="dls-input" 
                   name="npk" 
                   id="inputNpk"
                   placeholder="NPK atau Email" 
                   value="<?= e($_POST['npk'] ?? '') ?>"
                   autocomplete="username"
                   required>
        </div>

        <!-- Password Input -->
        <div class="dls-input-group">
            <span class="material-symbols-outlined dls-input-icon">lock</span>
            <input type="password" 
                   class="dls-input" 
                   name="password" 
                   id="inputPassword"
                   placeholder="Password" 
                   value=""
                   autocomplete="current-password"
                   required>
            <button type="button" class="dls-input-action" id="togglePassword" aria-label="Tampilkan password">
                <span class="material-symbols-outlined" style="font-size:20px;">visibility_off</span>
            </button>
        </div>

        <!-- Forgot Password Link -->
        <div class="login-forgot">
            <a href="#">Lupa password?</a>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="dls-btn dls-btn-secondary dls-btn-block dls-btn-rounded" id="btnLogin">
            Masuk
        </button>
    </form>

    <!-- Footer -->
    <div class="login-footer">
        <p>Belum punya akun? <a href="#">Hubungi Admin</a></p>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="<?= $base_path ?>assets/js/DX_LMS_M_Bootstrap.bundle.min.js"></script>

<!-- Login JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ubah visibilitas password
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

    // Umpan balik visual validasi form
    const form = document.getElementById('loginForm');
    const inputs = form.querySelectorAll('.dls-input');
    
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value.trim() === '' && this.hasAttribute('required')) {
                this.style.borderColor = 'var(--dls-error)';
            } else {
                this.style.borderColor = '';
            }
        });

        input.addEventListener('focus', function() {
            this.style.borderColor = '';
        });
    });
});
</script>

</body>
</html>
