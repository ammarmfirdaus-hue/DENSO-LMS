[CmdletBinding()]
param(
    [ValidateSet('all', 'admin', 'student')]
    [string]$Target = 'all',

    [ValidateSet('up', 'status')]
    [string]$Action = 'up',

    [switch]$Force
)

$ErrorActionPreference = 'Stop'
$root = $PSScriptRoot
$backend = Join-Path $root 'Backend-LMS-Admin-Teacher'
$student = Join-Path $root 'NATIVE-LMS-Student'
$migrateScript = Join-Path $root 'database\migrate.js'
$backendEnvPath = Join-Path $backend '.env'
$studentEnvPath = Join-Path $student '.env'

# 1. Validasi runtime yang dibutuhkan
if (-not (Get-Command 'node' -ErrorAction SilentlyContinue)) {
    throw 'Node.js tidak ditemukan di PATH. Pasang Node.js 20+ lalu jalankan ulang.'
}

if (-not (Test-Path (Join-Path $backend 'node_modules'))) {
    throw 'Dependency backend belum terpasang. Jalankan npm install di Backend-LMS-Admin-Teacher.'
}

if (-not (Test-Path $migrateScript)) {
    throw "Script migrasi tidak ditemukan di '$migrateScript'."
}

if (-not (Test-Path $backendEnvPath)) {
    throw 'File konfigurasi backend tidak ditemukan. Buat Backend-LMS-Admin-Teacher\.env terlebih dahulu.'
}

# 2. Helper untuk membaca file .env
function Read-DotEnvValue([string]$Path, [string]$Key) {
    if (-not (Test-Path $Path)) { return '' }
    $line = Get-Content -LiteralPath $Path | Where-Object { $_ -match "^\s*$([regex]::Escape($Key))\s*=" } | Select-Object -Last 1
    if (-not $line) { return '' }
    $value = ($line -split '=', 2)[1].Trim()
    return $value.Trim('"', "'")
}

# 3. Baca konfigurasi database
$adminServer = Read-DotEnvValue $backendEnvPath 'DB_SERVER'
$adminPort = Read-DotEnvValue $backendEnvPath 'DB_PORT'
$adminDatabase = Read-DotEnvValue $backendEnvPath 'DB_NAME'
$adminUser = Read-DotEnvValue $backendEnvPath 'MIGRATION_DB_USER'
if (-not $adminUser) { $adminUser = Read-DotEnvValue $backendEnvPath 'DB_USER' }
if (-not $adminServer) { $adminServer = 'localhost' }
if (-not $adminPort) { $adminPort = '1433' }

$backendStudentDatabase = Read-DotEnvValue $backendEnvPath 'STUDENT_DB_NAME'
$backendStudentHost = Read-DotEnvValue $backendEnvPath 'STUDENT_DB_HOST'
$backendStudentPort = Read-DotEnvValue $backendEnvPath 'STUDENT_DB_PORT'
$backendStudentUser = Read-DotEnvValue $backendEnvPath 'MIGRATION_STUDENT_DB_USER'
if (-not $backendStudentUser) { $backendStudentUser = Read-DotEnvValue $backendEnvPath 'STUDENT_DB_USER' }
if (-not $backendStudentDatabase) { $backendStudentDatabase = 'dls_student' }
if (-not $backendStudentHost) { $backendStudentHost = '127.0.0.1' }
if (-not $backendStudentPort) { $backendStudentPort = '3306' }

$studentDatabase = ''
$studentHost = ''
$studentPort = ''
$studentUser = ''

if (Test-Path $studentEnvPath) {
    $studentDatabase = Read-DotEnvValue $studentEnvPath 'DB_NAME'
    $studentHost = Read-DotEnvValue $studentEnvPath 'DB_HOST'
    $studentPort = Read-DotEnvValue $studentEnvPath 'DB_PORT'
    $studentUser = Read-DotEnvValue $studentEnvPath 'DB_USER'
    if (-not $studentDatabase) { $studentDatabase = 'dls_student' }
    if (-not $studentHost) { $studentHost = '127.0.0.1' }
    if (-not $studentPort) { $studentPort = '3306' }
}

# 4. Validasi konsistensi konfigurasi target
if ($Target -in @('all', 'admin')) {
    if (-not $adminDatabase) {
        throw 'Isi DB_NAME pada Backend-LMS-Admin-Teacher/.env sebelum menjalankan migrasi SQL Server.'
    }
}

if ($Target -in @('all', 'student')) {
    if (Test-Path $studentEnvPath) {
        if ($backendStudentDatabase -ne $studentDatabase) {
            throw "Target MySQL tidak sama: Backend STUDENT_DB_NAME='$backendStudentDatabase', Student DB_NAME='$studentDatabase'. Samakan konfigurasi agar migrasi menulis ke database yang sama."
        }
        $isHostEquivalent = (($backendStudentHost -in @('localhost', '127.0.0.1')) -and ($studentHost -in @('localhost', '127.0.0.1'))) -or ($backendStudentHost -eq $studentHost)
        if ($backendStudentPort -ne $studentPort -or -not $isHostEquivalent) {
            throw "Koneksi MySQL tidak sama: backend memakai $backendStudentHost`:$backendStudentPort sedangkan Student memakai $studentHost`:$studentPort. Samakan kedua konfigurasi .env."
        }
    }
}

# 5. Cek database production untuk keamanan
$isProduction = $false
$targetDbs = @()
if ($Target -in @('all', 'admin')) { $targetDbs += $adminDatabase }
if ($Target -in @('all', 'student')) { $targetDbs += $backendStudentDatabase }

foreach ($db in $targetDbs) {
    if ($db -match '(?i)(^|[_-])(prod|production|live)([_-]|$)') {
        $isProduction = $true
        Write-Warning "Target database '$db' terindikasi sebagai lingkungan PRODUCTION."
    }
}

# 6. Tampilkan ringkasan koneksi
Write-Host '==================================================' -ForegroundColor Cyan
Write-Host '          DENSO LMS DATABASE MIGRATION            ' -ForegroundColor Cyan
Write-Host '==================================================' -ForegroundColor Cyan
Write-Host "Mode Eksekusi : $($Action.ToUpper())" -ForegroundColor Yellow
Write-Host "Target Schema : $($Target.ToUpper())" -ForegroundColor Yellow

if ($Target -in @('all', 'admin')) {
    Write-Host "Target Admin/Teacher (SQL Server) : $adminServer`:$adminPort / $adminDatabase (User: $adminUser)"
}
if ($Target -in @('all', 'student')) {
    Write-Host "Target Student (MySQL)            : $backendStudentHost`:$backendStudentPort / $backendStudentDatabase (User: $backendStudentUser)"
}
Write-Host '--------------------------------------------------' -ForegroundColor DarkGray

# 7. Tampilkan status migrasi saat ini
Write-Host "Memeriksa status migrasi saat ini ($Target)...`n" -ForegroundColor Cyan
Push-Location $root
try {
    & node $migrateScript status $Target
    if ($LASTEXITCODE -ne 0) {
        throw "Gagal membaca status migrasi untuk target '$Target'. Periksa koneksi database dan kredensial .env."
    }
}
finally {
    Pop-Location
}

# Jika pengguna hanya meminta status, hentikan di sini
if ($Action -eq 'status') {
    Write-Host "`nPemeriksaan status selesai." -ForegroundColor Green
    return
}

# 8. Konfirmasi pengguna sebelum menjalankan migrasi up
Write-Host ''
if (-not $Force) {
    if ($isProduction) {
        $confirm = Read-Host "PERINGATAN: Database production terdeteksi! Ketik 'MIGRATE PRODUCTION' untuk melanjutkan"
        if ($confirm -cne 'MIGRATE PRODUCTION') {
            throw 'Migrasi production dibatalkan oleh pengguna.'
        }
    } else {
        $confirm = Read-Host "Ketik 'MIGRATE' untuk menjalankan migrasi $Target (atau Enter/Ctrl+C untuk batal)"
        if ($confirm -cne 'MIGRATE') {
            throw 'Migrasi dibatalkan oleh pengguna.'
        }
    }
}

# 9. Eksekusi migrasi up
Write-Host "`nMenjalankan migrasi database ($Target)..." -ForegroundColor Cyan
Push-Location $root
try {
    & node $migrateScript up $Target
    if ($LASTEXITCODE -ne 0) {
        throw "Migrasi database untuk target '$Target' gagal. Periksa error di atas dan pastikan hak akses DDL terpenuhi."
    }
}
finally {
    Pop-Location
}

# 10. Tampilkan status akhir setelah migrasi
Write-Host "`n--------------------------------------------------" -ForegroundColor DarkGray
Write-Host "Status akhir migrasi ($Target):" -ForegroundColor Cyan
Push-Location $root
try {
    & node $migrateScript status $Target
}
finally {
    Pop-Location
}

Write-Host "`n==================================================" -ForegroundColor Green
Write-Host " Migrasi skema database berhasil diselesaikan!    " -ForegroundColor Green
Write-Host " Semua riwayat tersimpan di tabel schema_migrations." -ForegroundColor Green
Write-Host "==================================================" -ForegroundColor Green
