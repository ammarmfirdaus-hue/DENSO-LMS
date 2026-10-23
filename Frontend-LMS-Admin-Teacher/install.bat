@echo off
setlocal EnableExtensions
cd /d "%~dp0"

echo [Frontend] Checking Node.js...
where node >nul 2>nul
if errorlevel 1 (
    echo ERROR: Node.js was not found. Install Node.js 20+ and add it to PATH.
    exit /b 1
)
for /f %%V in ('node -p "Number(process.versions.node.split('.')[0])"') do set NODE_MAJOR=%%V
if %NODE_MAJOR% LSS 20 (
    echo ERROR: Node.js 20+ is required.
    exit /b 1
)

if not exist .env (
    if not exist .env.example (
        echo ERROR: .env.example was not found.
        exit /b 1
    )
    copy /Y .env.example .env >nul
    echo Created .env from .env.example. Confirm VITE_API_BASE_URL matches the backend address.
) else (
    echo Keeping existing .env.
)

echo Installing frontend dependencies...
if exist package-lock.json (
    call npm ci
) else (
    call npm install
)
if errorlevel 1 exit /b 1

echo.
echo Frontend setup complete.
echo Next: configure Frontend-LMS-Admin-Teacher\.env, then run start.bat.
exit /b 0
