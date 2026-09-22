@echo off
REM Bedadung SFEWS - Quick Test Script

cls
echo ========================================
echo   BEDADUNG SFEWS - QUICK TEST
echo ========================================
echo.

REM Check if .env exists
if not exist .env (
    echo [ERROR] .env file not found!
    echo.
    echo Run this first:
    echo   copy .env.example .env
    echo   php artisan key:generate
    echo.
    pause
    exit /b 1
)

echo [1/6] Testing Telegram Bot...
echo ----------------------------------------
php artisan telegram:test "🧪 Test dari script"
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [!] Telegram test failed. Check your .env:
    echo     TELEGRAM_BOT_TOKEN=your-token
    echo     TELEGRAM_CHAT_ID=your-chat-id
    echo.
)

echo.
echo [2/6] Testing Ollama Connection...
echo ----------------------------------------
php artisan ollama:test
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [!] Ollama test failed. Make sure:
    echo     1. Ollama is running: ollama serve
    echo     2. Model installed: ollama pull qwen2.5:1.5b
    echo.
)

echo.
echo [3/6] Testing Full AI Analysis...
echo ----------------------------------------
php artisan ai:test
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [!] AI test failed.
    echo.
)

echo.
echo [4/6] Running Unit Tests...
echo ----------------------------------------
php artisan test
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [!] Some tests failed. Check output above.
    echo.
)

echo.
echo [5/6] Testing API Endpoint...
echo ----------------------------------------
curl -s http://localhost:8000/api/sensor/data > nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [!] API not reachable. Make sure server is running:
    echo     php artisan serve
    echo.
) else (
    echo [OK] API responding
)

echo.
echo [6/6] Checking Database...
echo ----------------------------------------
if exist database\database.sqlite (
    echo [OK] Database file exists
    php artisan db:show 2>nul | find "SQLite" >nul
    if %ERRORLEVEL% EQU 0 (
        echo [OK] Database connection working
    )
) else (
    echo [!] Database not found. Run:
    echo     type nul ^> database\database.sqlite
    echo     php artisan migrate
)

echo.
echo ========================================
echo   TESTING COMPLETE
echo ========================================
echo.
echo Next Steps:
echo   1. Start dev server: composer dev
echo   2. Open browser: http://localhost:8000
echo   3. Check dashboard UI (dark mode, toast, etc)
echo.
pause
