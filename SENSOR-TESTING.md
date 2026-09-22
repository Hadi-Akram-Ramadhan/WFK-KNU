# 📊 Sensor Testing Guide

## Quick Start

### Option 1: Interactive Script (Recommended)
```bash
# Run interactive menu
sensor-test.bat
```

**Menu Options:**
- `[1]` SAFE - Normal water level (18.5cm)
- `[2]` CAUTION - Rising water (4.2cm)  
- `[3]` DANGER - Critical level (2.1cm)
- `[4]` ALL - Run all scenarios sequentially
- `[5]` CUSTOM - Manual input

### Option 2: Artisan Command
```bash
# Run specific scenario
php artisan sensor:simulate safe
php artisan sensor:simulate caution
php artisan sensor:simulate danger

# Run all scenarios
php artisan sensor:simulate all
```

---

## Scenarios Explained

### 1. SAFE ✅
**Water Distance:** 18.5 cm  
**Status:** Safe  
**Behavior:**
- Green status on dashboard
- No AI analysis triggered
- No Telegram notification

**Use Case:** Normal monitoring state

---

### 2. CAUTION ⚠️
**Water Distance:** 4.2 cm  
**Status:** Caution  
**Behavior:**
- Yellow status on dashboard
- AI analysis triggered
- No Telegram notification (unless risk escalates)

**Use Case:** Monitor closely, prepare

---

### 3. DANGER 🚨
**Water Distance:** 2.1 cm  
**Status:** Danger  
**Behavior:**
- Red status on dashboard
- AI analysis triggered immediately
- **Telegram notification sent** 📱
- Hardware commands issued (servo, siren)

**Use Case:** Evacuation alert

---

## Testing Workflow

### Step 1: Start Services
```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2: Queue worker (important!)
php artisan queue:work

# Terminal 3: Vite
npm run dev
```

### Step 2: Open Dashboard
```
http://localhost:8000
```

### Step 3: Run Simulation
```bash
# Run test script
sensor-test.bat

# Or direct command
php artisan sensor:simulate danger
```

### Step 4: Observe Results

**Dashboard (refresh or wait 15s):**
- Hero banner changes color (green/yellow/red)
- Status updates
- Water level chart updates
- AI analysis card shows result

**Queue Worker Terminal:**
```
[timestamp] Processing: App\Jobs\AnalyzeFloodDataWithAI
[timestamp] Processed: App\Jobs\AnalyzeFloodDataWithAI
```

**Telegram (for danger only):**
```
🚨 BEDADUNG SFEWS — PERINGATAN DINI 🚨

📍 Node: BEDADUNG_01 — Checkpoint Alpha
📊 Jarak Air: 2.1 cm
📈 Laju Kenaikan: +3.50 cm/menit
⚡ Status: DANGER
🕐 Waktu: 15:30:45 WIB

🤖 Analisis AI:
Critical water level detected...

🔗 Buka Dashboard
```

---

## Expected Output Examples

### SAFE Scenario
```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📡 SCENARIO: SAFE
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ SAFE - Normal water level

+-------------------+---------+-----------+
| Metric            | Value   | Status    |
+-------------------+---------+-----------+
| Distance to Water | 18.5 cm | ✅ SAFE   |
| Water Level       | 1.5 cm  |           |
| Capacity          | 7.5%    |           |
| Temperature       | 27.5 °C | ☀️ Normal |
| Humidity          | 65.0%   | ☁️ Normal |
| Rise Rate         | 0.10    |           |
+-------------------+---------+-----------+

✅ Reading #1 created
   Node: BEDADUNG_01
   Status: SAFE
   Time: 15:30:00
```

### DANGER Scenario
```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📡 SCENARIO: DANGER
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🚨 DANGER - Critical water level, evacuate!

+-------------------+---------+-------------------------+
| Metric            | Value   | Status                  |
+-------------------+---------+-------------------------+
| Distance to Water | 2.1 cm  | 🚨 DANGER               |
| Water Level       | 17.9 cm |                         |
| Capacity          | 89.5%   |                         |
| Temperature       | 29.8 °C | 🔥 High                 |
| Humidity          | 88.0%   | 💧 Very High (Rain...)  |
| Rise Rate         | 3.50    |                         |
+-------------------+---------+-------------------------+

✅ Reading #8200 created
   Node: BEDADUNG_01
   Status: DANGER
   Time: 15:35:00

🤖 Triggering AI analysis...
   AI job queued (run queue worker to process)
   📱 Telegram notification will be sent
```

---

## Custom Testing

### Via API (PowerShell)
```powershell
# SAFE (distance > 5cm)
curl -X POST http://localhost:8000/api/sensor/data `
  -H "Content-Type: application/json" `
  -d '{"node_id":"BEDADUNG_01","distance_cm":15.5,"temperature_c":27,"humidity_percent":70}'

# CAUTION (3-5cm)
curl -X POST http://localhost:8000/api/sensor/data `
  -H "Content-Type: application/json" `
  -d '{"node_id":"BEDADUNG_01","distance_cm":4.0,"temperature_c":28,"humidity_percent":80}'

# DANGER (< 3cm)
curl -X POST http://localhost:8000/api/sensor/data `
  -H "Content-Type: application/json" `
  -d '{"node_id":"BEDADUNG_01","distance_cm":2.0,"temperature_c":30,"humidity_percent":90}'
```

### Via Script Menu
```bash
sensor-test.bat
# Choose [5] CUSTOM
# Input your values
```

---

## Status Calculation

```php
// From SensorReading model
if ($distanceCm <= 3.0)  return 'danger';   // 🚨
if ($distanceCm <= 5.0)  return 'caution';  // ⚠️
return 'safe';                               // ✅
```

**Examples:**
- 2.5 cm → DANGER
- 4.0 cm → CAUTION
- 15.0 cm → SAFE

---

## Troubleshooting

### ❌ AI analysis not running
**Check:** Queue worker running?
```bash
php artisan queue:work
```

### ❌ Telegram not sending
**Check:** 
1. Bot token correct in .env
2. Chat ID correct (negative for groups)
3. Scenario is DANGER (caution doesn't send)

### ❌ Dashboard not updating
**Check:**
1. Server running: `php artisan serve`
2. Vite running: `npm run dev`
3. Refresh browser (or wait 15s for auto-poll)

### ❌ "Queue connection not configured"
```bash
# Check .env
QUEUE_CONNECTION=database

# Run migration
php artisan migrate

# Restart queue
php artisan queue:restart
php artisan queue:work
```

---

## Testing Checklist

- [ ] Start all services (serve, queue, vite)
- [ ] Run SAFE scenario → green status
- [ ] Run CAUTION scenario → yellow status, AI queued
- [ ] Run DANGER scenario → red status, Telegram sent
- [ ] Check dashboard updates
- [ ] Check Telegram message received
- [ ] Check AI analysis in database/dashboard

---

## Advanced: Continuous Simulation

Create realistic timeline:
```bash
# Safe morning
php artisan sensor:simulate safe
sleep 300  # 5 min

# Caution afternoon (rain starting)
php artisan sensor:simulate caution
sleep 300

# Danger evening (heavy rain)
php artisan sensor:simulate danger
sleep 300

# Back to caution (rain stopping)
php artisan sensor:simulate caution
sleep 300

# Safe again
php artisan sensor:simulate safe
```

Save as `continuous-sim.sh` and run.

---

**Ready to test!** 🚀

Run `sensor-test.bat` atau `php artisan sensor:simulate all` untuk mulai.
