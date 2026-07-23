@echo off
echo =======================================================
echo Menjalankan LMS dengan PHP 8.3.31 (Portable Workspace)
echo =======================================================
echo.

:: %~dp0 mengambil direktori tempat file .bat ini berada (DENSO-LMS)
set PHP_PATH=%~dp0php-8.3.31\php.exe

if not exist "%PHP_PATH%" (
    echo [ERROR] PHP 8.3.31 tidak ditemukan di:
    echo %PHP_PATH%
    echo Silakan ekstrak folder download PHP 8.3.31 Anda ke dalam folder "LMS"
    echo dan pastikan nama foldernya adalah "php-8.3.31".
    pause
    exit /b
)

echo Menjalankan server pada http://localhost:8080
echo Tekan CTRL+C untuk berhenti.
echo.

"%PHP_PATH%" -c "%~dp0php-8.3.31\php.ini" -S localhost:8080
