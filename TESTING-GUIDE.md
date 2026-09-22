# 🧪 Manual Testing Guide — Bedadung SFEWS

## Prerequisites

1. ✅ PHP 8.2+
2. ✅ Composer installed
3. ✅ Node.js + NPM
4. ✅ Ollama installed (optional, untuk AI testing)
5. ✅ Telegram bot token & chat ID

---

## Quick Setup (5 menit)

### 1. Clone/Sudah Ada Project ✅

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Setup Environment
```bash
# Copy .env
copy .env.example .env

# Generate key
php artisan key:generate

# Create database
type nul > database\database.sqlite

# Run migrations
php artisan migrate
```

### 4. Configure Telegram
Edit `.env`:
```env
TELEGRAM_BOT_TOKEN=7123456789:AAHdqTcvCH1vGWJxfSeofSAs0K5PALDsaw
TELEGRAM_CHAT_ID=123456789
```

### 5. Configure Ollama (Optional)
```env
OLLAMA_URL=http://localhost:11434
OLLAMA_MODEL=qwen2.5:1.5b
OLLAMA_TIMEOUT=60
```

---

## Testing Steps

### Test 1: Telegram Bot ✅

```bash
# Test connection
php artisan telegram:test

# Custom message
php artisan telegram:test "🧪 Testing dari laptop Hadi"
```

**Expected Output:**
```
📤 Sending test message to Telegram...
✅ Message sent successfully
```

**Check:** Buka Telegram, message masuk!

---

### Test 2: Ollama AI ✅

```bash
# Make sure Ollama running
ollama serve

# Test connection
php artisan ollama:test
```

**Expected Output:**
```
🔍 Testing Ollama connection...

📡 URL: http://localhost:11434
🤖 Model: qwen2.5:1.5b

✅ Ollama is running

🧪 Testing inference...
✅ Response: 11763ms
Risk: low | Probability: 5%
AI: Based on the current sensor readings...
```

**Kalo gagal:**
```bash
# Start Ollama
ollama serve

# Pull model (first time)
ollama pull qwen2.5:1.5b
```

---

### Test 3: Full AI Analysis ✅

```bash
# Create test data + AI analysis
php artisan ai:test
```

**Expected Output:**
```
🔍 Finding node: BEDADUNG_01
🌊 Creating danger scenario (distance: 2.5cm)...
✅ Reading #1 created

🤖 Dispatching AI analysis job...

✅ Analysis completed!
Risk Level: high
Flood Probability: 75%
Model: qwen2.5:1.5b
Response Time: 11611ms

AI Response:
Based on the current sensor readings, the flood probability is high...
```

---

### Test 4: Web Dashboard 🌐

#### Terminal 1: Start Queue Worker
```bash
php artisan queue:work
```

#### Terminal 2: Start Dev Server
```bash
composer dev
```

Atau manual:
```bash
# Terminal 2: Laravel
php artisan serve

# Terminal 3: Queue
php artisan queue:listen

# Terminal 4: Vite
npm run dev
```

**Open Browser:**
```
http://localhost:8000
```

**Expected:** Dashboard loads dengan UI baru (dark mode toggle di header)

---

### Test 5: Simulate Sensor Data 📊

```bash
# Generate fake sensor readings
php artisan simulate:sensor
```

Atau manual via API:
```bash
curl -X POST http://localhost:8000/api/sensor/data \
  -H "Content-Type: application/json" \
  -d '{
    "node_id": "BEDADUNG_01",
    "distance_cm": 2.5,
    "temperature_c": 29.5,
    "humidity_percent": 88.0
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "status": "danger",
  "reading": {
    "id": 1,
    "distance_cm": 2.5,
    "status": "danger"
  },
  "commands": []
}
```

**Check:**
1. Dashboard updates (refresh or wait 15s)
2. Telegram message masuk (if danger status)
3. Queue worker processes AI job

---

### Test 6: Dark Mode Toggle 🌙

1. Open dashboard: `http://localhost:8000`
2. Click **light_mode icon** di header (top right, sebelah notification bell)
3. Should switch to dark mode
4. Refresh page → should persist

**Check localStorage:**
```javascript
// Browser console (F12)
localStorage.getItem('theme')
// Should return: "dark" or "light"
```

---

### Test 7: Toast Notifications 🔔

Open browser console (F12) dan run:
```javascript
// Success
showToast('Test success notification', 'success');

// Error
showToast('Test error notification', 'error');

// Warning
showToast('Test warning notification', 'warning');

// Info
showToast('Test info notification', 'info');
```

Toast muncul di top-right, auto-dismiss after 3 seconds.

---

### Test 8: Loading Skeleton 💀

Simulate slow loading:
```javascript
// Browser console
document.querySelector('[wire\\:poll]').setAttribute('wire:poll', '60s');
```

Reload → should see skeleton loaders briefly.

---

### Test 9: API Rate Limiting ⚡

Test rate limit:
```bash
# Spam API (should hit rate limit after 600 requests/min)
for /L %i in (1,1,700) do @curl -s http://localhost:8000/api/status/live > nul & echo Request %i
```

**Expected:** After ~600 requests, HTTP 429 (Too Many Requests)

---

### Test 10: Unit Tests ✅

```bash
# Run all tests
php artisan test

# Specific test
php artisan test --filter=TelegramNotificationTest

# With coverage (need Xdebug)
php artisan test --coverage
```

**Expected:**
```
✓ 8 tests passed (13 assertions)
Duration: 2.23s
```

---

## Common Issues & Fixes

### 1. Telegram "Failed to send"
```bash
# Check token format
echo %TELEGRAM_BOT_TOKEN%
# Should be: 123456789:ABC...

# Check chat ID
echo %TELEGRAM_CHAT_ID%
# Should be: 123456789 (personal) or -1001234567890 (group)

# Test manually
curl "https://api.telegram.org/bot%TELEGRAM_BOT_TOKEN%/sendMessage?chat_id=%TELEGRAM_CHAT_ID%&text=Test"
```

### 2. Ollama "not available"
```bash
# Check if running
curl http://localhost:11434/api/tags

# Start Ollama
ollama serve

# Pull model (first time)
ollama pull qwen2.5:1.5b

# List models
ollama list
```

### 3. Database locked
```bash
# Stop all PHP processes
taskkill /F /IM php.exe

# Clear cache
php artisan cache:clear

# Restart queue
php artisan queue:restart
php artisan queue:work
```

### 4. Dark mode not working
```javascript
// Clear localStorage
localStorage.clear();
location.reload();
```

### 5. Queue not processing
```bash
# Check jobs table
php artisan tinker
>>> \DB::table('jobs')->count();

# Restart queue
php artisan queue:restart
php artisan queue:work --tries=3 --timeout=120
```

---

## Full Testing Checklist

- [ ] Telegram bot responds (`php artisan telegram:test`)
- [ ] Ollama AI working (`php artisan ollama:test`)
- [ ] Full AI analysis runs (`php artisan ai:test`)
- [ ] Dashboard loads (http://localhost:8000)
- [ ] Dark mode toggle works
- [ ] Toast notifications show
- [ ] API returns data (`/api/status/live`)
- [ ] Sensor data ingestion works (POST `/api/sensor/data`)
- [ ] Queue processes jobs
- [ ] Unit tests pass (`php artisan test`)

---

## Testing Sequence (Recommended)

1. **Setup** (5 min)
   ```bash
   setup-test-local.bat
   ```

2. **Test Services** (2 min)
   ```bash
   php artisan telegram:test
   php artisan ollama:test
   ```

3. **Start Servers** (keep running)
   ```bash
   # Terminal 1
   php artisan serve
   
   # Terminal 2
   php artisan queue:work
   
   # Terminal 3
   npm run dev
   ```

4. **Test Dashboard** (3 min)
   - Open http://localhost:8000
   - Toggle dark mode
   - Check all cards load
   - Test toast (F12 console)

5. **Test AI** (2 min)
   ```bash
   php artisan ai:test
   ```
   Check Telegram for notification.

6. **Run Unit Tests** (1 min)
   ```bash
   php artisan test
   ```

**Total Time:** ~15 menit untuk full testing

---

## Production Testing Checklist

Sebelum deploy production:

- [ ] All unit tests pass
- [ ] Load test with Apache Bench (`ab -n 10000 -c 100`)
- [ ] Telegram bot added to production groups
- [ ] Ollama on GPU server (response < 2s)
- [ ] `.env.production` configured
- [ ] SSL/HTTPS enabled
- [ ] Rate limiting tested
- [ ] Error monitoring (Sentry) setup
- [ ] Backup strategy ready

---

## Quick Reference

### Important Commands
```bash
# Test
php artisan ollama:test
php artisan telegram:test
php artisan ai:test
php artisan test

# Serve
php artisan serve
php artisan queue:work
npm run dev

# Clear
php artisan cache:clear
php artisan config:clear
php artisan queue:restart

# Database
php artisan migrate
php artisan migrate:fresh --seed
php artisan tinker
```

### Important URLs
- Dashboard: http://localhost:8000
- API Status: http://localhost:8000/api/status/live
- Telegram Bot API: https://api.telegram.org/bot{TOKEN}/getUpdates

### Important Files
- Config: `.env`
- Database: `database/database.sqlite`
- Logs: `storage/logs/laravel.log`

---

Gampang kan? Tinggal run `setup-test-local.bat` terus test satu-satu! 🚀
