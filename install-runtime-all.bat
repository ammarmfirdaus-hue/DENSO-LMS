@echo off
setlocal EnableExtensions
cd /d "%~dp0"

echo Installing Native PHP student project...
call "%~dp0NATIVE-LMS-Student\install.bat"
if errorlevel 1 goto failed

echo.
echo Installing Admin and Teacher backend...
call "%~dp0Backend-LMS-Admin-Teacher\install.bat"
if errorlevel 1 goto failed

echo.
echo Installing Admin and Teacher frontend...
call "%~dp0Frontend-LMS-Admin-Teacher\install.bat"
if errorlevel 1 goto failed

echo.
echo All project dependencies and local assets are installed.
echo Configure each project's .env and database before starting the servers.
exit /b 0

:failed
echo.
echo Installation stopped because a project setup failed. Fix the reported issue and rerun install-all.bat.
exit /b 1
