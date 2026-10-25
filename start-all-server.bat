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

set "WAIT_ATTEMPTS=0"
:wait_api_ready
powershell.exe -NoProfile -Command "try { Invoke-WebRequest -UseBasicParsing -Uri 'http://127.0.0.1:3001/health' -TimeoutSec 2 | Out-Null; exit 0 } catch { exit 1 }" >nul 2>&1
if not errorlevel 1 goto :api_ready
set /a WAIT_ATTEMPTS+=1
if %WAIT_ATTEMPTS% GEQ 30 goto :api_not_ready
timeout /t 2 /nobreak >nul
goto :wait_api_ready

:api_ready
set "WAIT_ATTEMPTS=0"
:wait_frontend_ready
powershell.exe -NoProfile -Command "try { Invoke-WebRequest -UseBasicParsing -Uri 'http://127.0.0.1:5173/' -TimeoutSec 2 | Out-Null; exit 0 } catch { exit 1 }" >nul 2>&1
if not errorlevel 1 goto :frontend_ready
set /a WAIT_ATTEMPTS+=1
if %WAIT_ATTEMPTS% GEQ 30 goto :frontend_not_ready
timeout /t 2 /nobreak >nul
goto :wait_frontend_ready

:frontend_ready
set "WAIT_ATTEMPTS=0"
:wait_student_ready
powershell.exe -NoProfile -Command "try { Invoke-WebRequest -UseBasicParsing -Uri 'http://127.0.0.1:8082/' -TimeoutSec 2 | Out-Null; exit 0 } catch { exit 1 }" >nul 2>&1
if not errorlevel 1 goto :student_ready
set /a WAIT_ATTEMPTS+=1
if %WAIT_ATTEMPTS% GEQ 30 goto :student_not_ready
timeout /t 2 /nobreak >nul
goto :wait_student_ready

:student_ready
start "" "http://127.0.0.1:5173"
start "" "http://127.0.0.1:8082"
echo.
echo Semua runtime sudah siap:
echo   Admin/Teacher React : http://127.0.0.1:5173
echo   Admin/Teacher API   : http://127.0.0.1:3001
echo   Student PHP         : http://127.0.0.1:8082
echo Tutup masing-masing terminal atau tekan Ctrl+C untuk menghentikan server.
exit /b 0

:api_not_ready
echo Backend belum siap setelah 60 detik. Periksa jendela Backend, koneksi SQL Server, dan file Backend-LMS-Admin-Teacher\.env.
pause
exit /b 1

:frontend_not_ready
echo Frontend belum merespons setelah 60 detik. Periksa jendela Frontend dan dependency Vite.
pause
exit /b 1

:student_not_ready
echo Native Student belum merespons setelah 60 detik. Periksa jendela PHP, PDO MySQL, dan file NATIVE-LMS-Student\.env.
pause
exit /b 1

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
