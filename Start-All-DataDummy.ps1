$ErrorActionPreference = 'Stop'
$root = $PSScriptRoot
$backend = Join-Path $root 'Backend-LMS-Admin-Teacher'
$student = Join-Path $root 'NATIVE-LMS-Student'
$backendEnvPath = Join-Path $backend '.env'
$studentEnvPath = Join-Path $student '.env'

foreach ($commandName in @('node', 'npm', 'php')) {
    if (-not (Get-Command $commandName -ErrorAction SilentlyContinue)) {
        throw "$commandName tidak ditemukan di PATH. Pasang runtime yang dibutuhkan lalu jalankan ulang."
    }
}
if (-not (Test-Path (Join-Path $backend 'node_modules'))) {
    throw 'Dependency backend belum terpasang. Jalankan npm install di Backend-LMS-Admin-Teacher.'
}
if (-not (Test-Path $backendEnvPath)) { throw 'File konfigurasi backend tidak ditemukan. Buat Backend-LMS-Admin-Teacher\.env terlebih dahulu.' }
if (-not (Test-Path $studentEnvPath)) { throw 'File konfigurasi student tidak ditemukan. Buat NATIVE-LMS-Student\.env terlebih dahulu.' }

function Read-DotEnvValue([string]$Path, [string]$Key) {
    $line = Get-Content -LiteralPath $Path | Where-Object { $_ -match "^\s*$([regex]::Escape($Key))\s*=" } | Select-Object -Last 1
    if (-not $line) { return '' }
    $value = ($line -split '=', 2)[1].Trim()
    return $value.Trim('"', "'")
}

$adminDatabase = Read-DotEnvValue $backendEnvPath 'DB_NAME'
$backendStudentDatabase = Read-DotEnvValue $backendEnvPath 'STUDENT_DB_NAME'
$studentDatabase = Read-DotEnvValue $studentEnvPath 'DB_NAME'
$backendStudentHost = Read-DotEnvValue $backendEnvPath 'STUDENT_DB_HOST'
$backendStudentPort = Read-DotEnvValue $backendEnvPath 'STUDENT_DB_PORT'
$studentHost = Read-DotEnvValue $studentEnvPath 'DB_HOST'
$studentPort = Read-DotEnvValue $studentEnvPath 'DB_PORT'
if (-not $backendStudentDatabase) { $backendStudentDatabase = 'dls_student' }
if (-not $studentDatabase) { $studentDatabase = 'dls_student' }
if (-not $backendStudentHost) { $backendStudentHost = '127.0.0.1' }
if (-not $studentHost) { $studentHost = '127.0.0.1' }
if (-not $backendStudentPort) { $backendStudentPort = '3306' }
if (-not $studentPort) { $studentPort = '3306' }
if (-not $adminDatabase) { throw 'Isi DB_NAME pada Backend-LMS-Admin-Teacher/.env sebelum menjalankan seeder.' }
if ($backendStudentDatabase -ne $studentDatabase) {
    throw "Target MySQL tidak sama: Backend STUDENT_DB_NAME='$backendStudentDatabase', Student DB_NAME='$studentDatabase'. Samakan agar kedua seeder menulis ke database Student yang sama."
}
if ($backendStudentPort -ne $studentPort -or ($backendStudentHost -ne $studentHost -and !(($backendStudentHost -in @('localhost','127.0.0.1')) -and ($studentHost -in @('localhost','127.0.0.1'))))) {
    throw "Koneksi MySQL tidak sama: backend memakai $backendStudentHost`:$backendStudentPort sedangkan Student memakai $studentHost`:$studentPort. Samakan kedua konfigurasi .env."
}
foreach ($databaseName in @($adminDatabase, $studentDatabase)) {
    if ($databaseName -match '(?i)(^|[_-])(prod|production|live)([_-]|$)') {
        throw "Seeder demo diblokir untuk database bernama '$databaseName'. Gunakan database staging atau lokal yang terpisah dari production."
    }
}

Write-Host "Target Admin/Teacher: $adminDatabase"
Write-Host "Target Student:       $studentDatabase"
$confirmation = Read-Host "Seeder hanya menambah/memperbarui data berprefix DLS-DEMO. Ketik SEED DEMO untuk melanjutkan"
if ($confirmation -cne 'SEED DEMO') { throw 'Seeder dibatalkan.' }
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
    Write-Host 'Seeder data dummy selesai. Data demo tersambung di SQL Server dan MySQL. Server tidak dijalankan.'
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
