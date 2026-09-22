@echo off
REM Sensor Simulation Testing Script

cls
echo ========================================
echo   SENSOR SIMULATION - TESTING
echo ========================================
echo.

:menu
echo Select scenario to test:
echo.
echo [1] SAFE      - Normal water level (18.5cm)
echo [2] CAUTION   - Rising water (4.2cm)
echo [3] DANGER    - Critical level (2.1cm)
echo [4] ALL       - Run all scenarios
echo [5] CUSTOM    - Manual input
echo [Q] Quit
echo.

set /p choice="Enter choice (1-5 or Q): "

if /i "%choice%"=="1" goto safe
if /i "%choice%"=="2" goto caution
if /i "%choice%"=="3" goto danger
if /i "%choice%"=="4" goto all
if /i "%choice%"=="5" goto custom
if /i "%choice%"=="Q" goto end
if /i "%choice%"=="q" goto end

echo Invalid choice!
pause
cls
goto menu

:safe
cls
echo Running SAFE scenario...
echo.
php artisan sensor:simulate safe
echo.
pause
cls
goto menu

:caution
cls
echo Running CAUTION scenario...
echo.
php artisan sensor:simulate caution
echo.
pause
cls
goto menu

:danger
cls
echo Running DANGER scenario...
echo.
php artisan sensor:simulate danger
echo.
echo Check Telegram for notification!
pause
cls
goto menu

:all
cls
echo Running ALL scenarios...
echo.
php artisan sensor:simulate all
echo.
pause
cls
goto menu

:custom
cls
echo ========================================
echo   CUSTOM SENSOR INPUT
echo ========================================
echo.
echo Distance to water (cm):
echo   ^> 15cm  = SAFE
echo   3-5cm   = CAUTION
echo   ^< 3cm   = DANGER
echo.

set /p distance="Distance (cm): "
set /p temp="Temperature (°C, default 28): "
set /p humidity="Humidity (%%, default 75): "

if "%temp%"=="" set temp=28
if "%humidity%"=="" set humidity=75

echo.
echo Sending to API...
curl -X POST http://localhost:8000/api/sensor/data ^
  -H "Content-Type: application/json" ^
  -d "{\"node_id\":\"BEDADUNG_01\",\"distance_cm\":%distance%,\"temperature_c\":%temp%,\"humidity_percent\":%humidity%}"

echo.
echo.
echo Data sent! Check dashboard.
pause
cls
goto menu

:end
echo.
echo Goodbye!
exit /b 0
