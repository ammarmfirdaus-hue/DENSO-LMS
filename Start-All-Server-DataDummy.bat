@echo off
setlocal

powershell.exe -NoLogo -NoProfile -ExecutionPolicy Bypass -File "%~dp0Start-All-Server-DataDummy.ps1"
set "EXIT_CODE=%ERRORLEVEL%"

if not "%EXIT_CODE%"=="0" (
  echo.
  echo Proses data dummy atau launcher server gagal. Lihat pesan error di atas.
  pause
)

exit /b %EXIT_CODE%
