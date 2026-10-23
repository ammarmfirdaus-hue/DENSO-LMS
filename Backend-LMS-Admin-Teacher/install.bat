@echo off
setlocal EnableExtensions
cd /d "%~dp0"

echo [Backend API] Checking Node.js...
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
    node -e "const fs=require('fs'),crypto=require('crypto');let s=fs.readFileSync('.env.example','utf8');s=s.replace(/^JWT_SECRET=.*$/m,'JWT_SECRET='+crypto.randomBytes(48).toString('base64url'));fs.writeFileSync('.env',s,{flag:'wx'});"
    if errorlevel 1 (
        echo ERROR: Could not create .env.
        exit /b 1
    )
    echo Created .env with a fresh random JWT secret. Configure SQL Server credentials before starting the API.
) else (
    echo Keeping existing .env.
)

echo Installing backend dependencies...
if exist package-lock.json (
    call npm ci
) else (
    call npm install
)
if errorlevel 1 exit /b 1

echo.
echo Backend setup complete.
echo Next: configure Backend-LMS-Admin-Teacher\.env and apply the SQL Server schema.
exit /b 0
