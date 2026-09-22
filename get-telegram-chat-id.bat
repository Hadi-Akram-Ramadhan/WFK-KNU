@echo off
REM Get Telegram Chat ID Helper Script

echo ========================================
echo Telegram Bot Chat ID Finder
echo ========================================
echo.

set /p TOKEN="Paste your bot token: "

echo.
echo 1. Send a message to your bot on Telegram
echo 2. Press Enter here to get your chat ID
pause > nul

curl -s "https://api.telegram.org/bot%TOKEN%/getUpdates" > updates.json

echo.
echo Chat ID found in updates.json
echo.
echo Opening updates.json...
start notepad updates.json

echo.
echo Look for: "chat":{"id": YOUR_CHAT_ID
echo.
pause
