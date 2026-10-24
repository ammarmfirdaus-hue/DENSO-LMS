<?php
$page_title = 'Konfirmasi Keluar - DENSO Learning';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include __DIR__ . '/../../includes/components/DX_LMS_M_Tailwind_Head.php'; ?>
</head>
<body class="min-h-screen bg-surface-container-low flex items-center justify-center p-4">
    <main class="w-full max-w-md rounded-2xl border border-outline-variant bg-surface p-6 shadow-lg sm:p-8">
        <img src="assets/img/icon/denso-vector-logo.png" alt="DENSO" class="mb-6 h-12 w-12 object-contain">
        <p class="mb-2 text-sm font-semibold text-primary">DENSO Learning</p>
        <h1 class="text-2xl font-bold text-on-surface">Keluar dari akun?</h1>
        <p class="mt-3 text-sm leading-6 text-on-surface-variant">Konfirmasi untuk mengakhiri sesi Anda di perangkat ini.</p>
        <form method="POST" action="?route=/auth/logout" class="mt-7 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
            <a href="?route=/student" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-outline-variant px-4 font-semibold text-on-surface hover:bg-surface-container-low">Kembali</a>
            <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-error px-4 font-semibold text-white hover:brightness-95">Ya, keluar</button>
        </form>
    </main>
</body>
</html>
