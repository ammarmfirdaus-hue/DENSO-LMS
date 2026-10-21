@echo off
setlocal EnableExtensions

set "ROOT=%~dp0"
set "BACKEND=%ROOT%Backend-LMS-Admin-Teacher"
set "FRONTEND=%ROOT%Frontend-LMS-Admin-Teacher"
set "STUDENT=%ROOT%NATIVE-LMS-Student"
set "CONFIG_CREATED=0"

where.exe node >nul 2>&1 || goto :missing_node
where.exe npm >nul 2>&1 || goto :missing_npm
where.exe php >nul 2>&1 || goto :missing_php

if not exist "%BACKEND%\node_modules" goto :missing_backend_deps
if not exist "%FRONTEND%\node_modules" goto :missing_frontend_deps

if not exist "%BACKEND%\.env" (
  copy /Y "%BACKEND%\.env.example" "%BACKEND%\.env" >nul
  set "CONFIG_CREATED=1"
)
if not exist "%FRONTEND%\.env" (
  copy /Y "%FRONTEND%\.env.example" "%FRONTEND%\.env" >nul
  set "CONFIG_CREATED=1"
)
if not exist "%STUDENT%\.env" (
  copy /Y "%STUDENT%\.env.example" "%STUDENT%\.env" >nul
  set "CONFIG_CREATED=1"
)

if "%CONFIG_CREATED%"=="1" goto :configure_env
findstr /C:"generate-a-random-secret-at-least-32-bytes" "%BACKEND%\.env" >nul && goto :configure_env
findstr /C:"change-this-password" "%STUDENT%\.env" >nul && goto :configure_env
findstr /R /C:"^DB_USER=." "%BACKEND%\.env" >nul || goto :configure_sql_auth
findstr /R /C:"^DB_PASSWORD=." "%BACKEND%\.env" >nul || goto :configure_sql_auth

start "DLS Backend - API 3001" /D "%BACKEND%" cmd /k "npm run dev"
start "DLS Frontend - React 5173" /D "%FRONTEND%" cmd /k "npm run dev"
start "DLS Student - PHP 8082" /D "%STUDENT%" cmd /k "php -S 127.0.0.1:8082 -t public public/router.php"

start "" "http://127.0.0.1:5173"
start "" "http://127.0.0.1:8082"
echo.
echo Semua runtime sudah diminta berjalan:
echo   Admin/Teacher React : http://127.0.0.1:5173
echo   Admin/Teacher API   : http://127.0.0.1:3001
echo   Student PHP         : http://127.0.0.1:8082
echo Tutup masing-masing terminal atau tekan Ctrl+C untuk menghentikan server.
exit /b 0

:configure_env
echo Konfigurasi .env baru dibuat atau masih berisi nilai contoh.
echo Edit kredensial dan secret pada file berikut sebelum menjalankan runtime:
echo   %BACKEND%\.env
echo   %STUDENT%\.env
echo Pastikan SQL Server dan MySQL sudah aktif, database/schema sudah dibuat,
echo dan akun database sesuai dengan konfigurasi .env.
echo Setelah selesai, jalankan start-all-server.bat lagi.
pause
exit /b 1

:configure_sql_auth
echo Backend .env belum memiliki DB_USER dan DB_PASSWORD SQL Server yang terisi.
echo Isi keduanya sesuai login SQL Server, lalu jalankan start-all-server.bat lagi.
echo DB_PASSWORD boleh kosong hanya jika login SQL Server Anda memang tanpa password.
pause
exit /b 1

:missing_node
echo Node.js belum ditemukan di PATH. Install Node.js 20 atau lebih baru.
pause
exit /b 1
:missing_npm
echo npm belum ditemukan di PATH. Periksa instalasi Node.js.
pause
exit /b 1
:missing_php
echo PHP belum ditemukan di PATH. Install PHP CLI lalu tambahkan ke PATH.
pause
exit /b 1
:missing_backend_deps
echo Dependency backend belum terpasang. Jalankan: cd Backend-LMS-Admin-Teacher ^&^& npm install
pause
exit /b 1
:missing_frontend_deps
echo Dependency frontend belum terpasang. Jalankan: cd Frontend-LMS-Admin-Teacher ^&^& npm install
pause
exit /b 1
