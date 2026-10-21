$ErrorActionPreference = 'Stop'
$root = $PSScriptRoot
$backend = Join-Path $root 'Backend-LMS-Admin-Teacher'
$student = Join-Path $root 'NATIVE-LMS-Student'
$adminTeacherSchema = Join-Path $root 'database\admin&teacher.sql'
$studentSchema = Join-Path $root 'database\student.sql'
$serverLauncher = Join-Path $root 'start-all-server.bat'

foreach ($commandName in @('node', 'npm', 'php')) {
    if (-not (Get-Command $commandName -ErrorAction SilentlyContinue)) {
        throw "$commandName tidak ditemukan di PATH. Pasang runtime yang dibutuhkan lalu jalankan ulang."
    }
}
if (-not (Test-Path (Join-Path $backend 'node_modules'))) {
    throw 'Dependency backend belum terpasang. Jalankan npm install di Backend-LMS-Admin-Teacher.'
}
if (-not (Test-Path $adminTeacherSchema)) {
    throw 'Skema SQL Server tidak ditemukan: database\admin&teacher.sql.'
}
if (-not (Test-Path $studentSchema)) {
    throw 'Skema MySQL tidak ditemukan: database\student.sql.'
}
if (-not (Test-Path $serverLauncher)) {
    throw 'start-all-server.bat tidak ditemukan di folder root proyek.'
}

$securePassword = Read-Host 'Masukkan password khusus akun demo (12-72 byte UTF-8)' -AsSecureString
$passwordPointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($securePassword)
$demoPassword = $null

try {
    $demoPassword = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($passwordPointer)
    $passwordBytes = [Text.Encoding]::UTF8.GetByteCount($demoPassword)
    if ($passwordBytes -lt 12 -or $passwordBytes -gt 72) {
        throw 'Password demo harus memiliki panjang 12-72 byte UTF-8.'
    }

    $env:SEED_DEMO = 'true'
    $env:SEED_DEMO_PASSWORD = $demoPassword
    Push-Location $backend
    try {
        npm run db:seed:demo
        if ($LASTEXITCODE -ne 0) { throw 'Seeder Admin/Teacher gagal. Periksa koneksi SQL Server dan konfigurasi .env backend.' }
    }
    finally { Pop-Location }

    $env:STUDENT_PASSWORD = $demoPassword
    Push-Location $student
    try {
        php scripts/seed-demo.php
        if ($LASTEXITCODE -ne 0) { throw 'Seeder Student gagal. Periksa koneksi MySQL dan konfigurasi .env student.' }
    }
    finally { Pop-Location }

    Remove-Item Env:SEED_DEMO -ErrorAction SilentlyContinue
    Remove-Item Env:SEED_DEMO_PASSWORD -ErrorAction SilentlyContinue
    Remove-Item Env:STUDENT_PASSWORD -ErrorAction SilentlyContinue
    Write-Host 'Seeder selesai. Menjalankan semua server...'
    & $serverLauncher
    if ($LASTEXITCODE -ne 0) { throw 'Launcher server berhenti. Periksa konfigurasi .env dan database.' }
}
finally {
    Remove-Item Env:SEED_DEMO -ErrorAction SilentlyContinue
    Remove-Item Env:SEED_DEMO_PASSWORD -ErrorAction SilentlyContinue
    Remove-Item Env:STUDENT_PASSWORD -ErrorAction SilentlyContinue
    if ($passwordPointer -ne [IntPtr]::Zero) {
        [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($passwordPointer)
    }
    $demoPassword = $null
    $securePassword.Dispose()
}
