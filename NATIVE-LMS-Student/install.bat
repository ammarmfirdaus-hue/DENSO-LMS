@echo off
setlocal EnableExtensions
cd /d "%~dp0"

echo [Native PHP] Checking required tools...
where php >nul 2>nul
if errorlevel 1 (
    echo ERROR: PHP was not found. Install PHP 8.1+ and add php.exe to PATH.
    exit /b 1
)
php -r "exit(PHP_VERSION_ID >= 80100 && extension_loaded('pdo_mysql') ? 0 : 1);"
if errorlevel 1 (
    echo ERROR: PHP 8.1+ and the pdo_mysql extension are required.
    echo Enable pdo_mysql in php.ini, then run this installer again.
    exit /b 1
)

where node >nul 2>nul
if errorlevel 1 (
    echo ERROR: Node.js was not found. Install Node.js 20+ and add it to PATH.
    exit /b 1
)
for /f %%V in ('node -p "Number(process.versions.node.split('.')[0])"') do set NODE_MAJOR=%%V
if %NODE_MAJOR% LSS 20 (
    echo ERROR: Node.js 20+ is required to build the native PHP assets.
    exit /b 1
)

if not exist .env (
    if not exist .env.example (
        echo ERROR: .env.example was not found.
        exit /b 1
    )
    copy /Y .env.example .env >nul
    echo Created .env from .env.example. Configure your database credentials before starting the app.
) else (
    echo Keeping existing .env.
)

echo Installing native PHP asset dependencies...
if exist package-lock.json (
    call npm ci
) else (
    call npm install
)
if errorlevel 1 exit /b 1

echo Building Tailwind CSS...
call npm run build:css
if errorlevel 1 exit /b 1

if exist composer.json (
    where composer >nul 2>nul
    if errorlevel 1 (
        echo ERROR: composer.json exists but Composer is not installed or not in PATH.
        exit /b 1
    )
    call composer install --no-interaction --prefer-dist
    if errorlevel 1 exit /b 1
) else (
    echo Composer dependencies: none. This native PHP project has no composer.json.
)

echo.
echo Native PHP setup complete.
echo Next: configure NATIVE-LMS-Student\.env and import the student database schema.
exit /b 0
