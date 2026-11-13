<?php
$page_title = 'Konfirmasi Keluar - DENSO Learning';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include __DIR__ . '/../../includes/components/DX_LMS_M_Tailwind_Head.php'; ?>
</head>
<body class="min-h-screen bg-[#FAFAFA] flex items-center justify-center p-4 text-[#171717]">
    <main class="w-full max-w-md rounded-[12px] bg-white p-[24px] shadow-[0_4px_12px_rgba(0,0,0,0.12)] sm:p-[30px]">
        <div class="mb-[18px] flex items-center gap-[12px]">
            <div class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-[12px] bg-[#FEF2F2] p-2">
                <img src="assets/img/icon/denso-vector-logo.png" alt="DENSO" class="h-8 w-8 object-contain">
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-[#DC2626]">DENSO Learning</p>
                <p class="text-[12px] text-[#737373]">Konfirmasi Keamanan Sesi</p>
            </div>
        </div>
        <h1 class="fd-h1 text-[24px] font-bold leading-[30px] text-[#171717]">Keluar dari akun?</h1>
        <p class="mt-[12px] text-[14px] leading-[22px] text-[#525252]">Konfirmasi untuk mengakhiri sesi Anda di perangkat ini. Anda dapat masuk kembali kapan saja dengan akun DENSO Anda.</p>
        <form method="POST" action="?route=/auth/logout" class="mt-[24px] flex flex-col-reverse gap-[12px] sm:flex-row sm:justify-end">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
            <a href="?route=/student" class="fd-btn fd-btn-secondary fd-btn-md w-full rounded-full sm:w-auto">Kembali</a>
            <button type="submit" class="fd-btn fd-btn-primary fd-btn-md w-full rounded-full sm:w-auto">
                <span class="material-symbols-outlined" style="font-size:18px;">logout</span>
                <span>Ya, keluar</span>
            </button>
        </form>
    </main>
</body>
</html>
