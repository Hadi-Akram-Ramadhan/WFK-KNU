@echo off
REM Local Testing Setup Script for Bedadung SFEWS

echo ========================================
echo Bedadung SFEWS - Local Testing Setup
echo ========================================
echo.

echo [1/5] Checking .env file...
if not exist .env (
    echo ERROR: .env file not found!
    echo Run: copy .env.example .env
    pause
    exit /b 1
)
echo OK - .env found

echo.
echo [2/5] Checking database...
if not exist database\database.sqlite (
    echo Creating SQLite database...
    type nul > database\database.sqlite
)
echo OK - Database ready

echo.
echo [3/5] Running migrations...
php artisan migrate --force
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Migration failed!
    pause
    exit /b 1
)
echo OK - Database migrated

echo.
echo [4/5] Clearing cache...
php artisan config:clear
php artisan cache:clear
php artisan view:clear
echo OK - Cache cleared

echo.
echo [5/5] Testing Ollama connection...
php artisan ollama:test
if %ERRORLEVEL% NEQ 0 (
    echo WARNING: Ollama not running or model not available
    echo Start Ollama: ollama serve
    echo Pull model: ollama pull qwen2.5:1.5b
)

echo.
echo ========================================
echo Setup Complete!
echo ========================================
echo.
echo Next steps:
echo 1. Update TELEGRAM_BOT_TOKEN in .env
echo 2. Update TELEGRAM_CHAT_ID in .env
echo 3. Run: setup-test-local.bat test
echo.
pause
