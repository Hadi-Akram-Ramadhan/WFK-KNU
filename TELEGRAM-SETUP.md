# 🤖 TELEGRAM BOT SETUP — Step by Step

## Step 1: Create Bot (2 menit)

1. **Buka Telegram** → Search `@BotFather`
2. **Klik Start** atau ketik `/start`
3. **Create new bot:**
   ```
   /newbot
   ```
4. **Kasih nama:**
   ```
   Bedadung SFEWS Alert Bot
   ```
5. **Kasih username** (must end with "bot"):
   ```
   BedadungSFEWSBot
   ```

6. **BotFather kasih token:**
   ```
   Done! Congratulations on your new bot.
   
   Use this token to access the HTTP API:
   7123456789:AAHdqTcvCH1vGWJxfSeofSAs0K5PALDsaw
   
   Keep your token secure and store it safely.
   ```

**COPY TOKEN INI!** ← Simpan di notepad dulu.

---

## Step 2: Get Chat ID

### Option A: Personal Chat (Untuk Testing)

1. **Cari bot lu** di Telegram (search `@BedadungSFEWSBot`)
2. **Klik Start** / kirim message apapun ke bot
3. **Run script ini:**

```bash
# Windows PowerShell
cd C:\Users\kkhad\Desktop\WFK-KNU
.\get-telegram-chat-id.bat
```

Atau manual:
```powershell
# Ganti TOKEN dengan token bot lu
$TOKEN = "7123456789:AAHdqTcvCH1vGWJxfSeofSAs0K5PALDsaw"

# Get updates
curl "https://api.telegram.org/bot$TOKEN/getUpdates" | Out-File -Encoding UTF8 updates.json

# Buka file
notepad updates.json
```

4. **Cari di `updates.json`:**
```json
{
  "message": {
    "chat": {
      "id": 123456789,  // ← INI CHAT ID LU (positif)
      "first_name": "Hadi",
      "username": "hadi_akram"
    }
  }
}
```

**COPY CHAT ID INI!** (angka positif)

---

### Option B: Group Chat (Untuk Production)

1. **Buat group** di Telegram
   - Tap "New Group"
   - Nama: "SFEWS Alert Jember"
   - Add minimal 1 kontak dulu (bisa hapus nanti)

2. **Add bot ke group:**
   - Tap group name
   - Tap "Add Members"
   - Search bot username: `@BedadungSFEWSBot`
   - Add

3. **Jadikan bot sebagai Admin:**
   - Tap group name
   - "Edit"
   - "Administrators"
   - "Add Admin"
   - Pilih bot lu
   - Enable semua permissions
   - "Done"

4. **Kirim message di group:**
   ```
   Hello @BedadungSFEWSBot
   ```

5. **Get Group Chat ID** (sama seperti personal):
```powershell
$TOKEN = "your-token"
curl "https://api.telegram.org/bot$TOKEN/getUpdates" | Out-File updates.json
notepad updates.json
```

6. **Cari di file:**
```json
{
  "message": {
    "chat": {
      "id": -1001234567890,  // ← GROUP ID (NEGATIF! starts with -100)
      "title": "SFEWS Alert Jember",
      "type": "supergroup"
    }
  }
}
```

**COPY CHAT ID INI!** (angka negatif, starts with `-100`)

---

## Step 3: Update .env

```bash
# Edit .env file
notepad .env
```

Tambah/update:
```env
# Telegram Bot Token (dari BotFather)
TELEGRAM_BOT_TOKEN=7123456789:AAHdqTcvCH1vGWJxfSeofSAs0K5PALDsaw

# Chat ID — bisa personal atau group atau multiple (comma-separated)
# Personal (positif):
TELEGRAM_CHAT_ID=123456789

# Atau Group (negatif):
TELEGRAM_CHAT_ID=-1001234567890

# Atau Multiple (personal + 2 groups):
TELEGRAM_CHAT_ID=123456789,-1001234567890,-1009876543210
```

Save & close.

---

## Step 4: Test Bot

```bash
# Clear config cache
php artisan config:clear

# Test kirim message
php artisan telegram:test

# Atau custom message
php artisan telegram:test "🧪 Testing bot dari Hadi"
```

**Expected Output:**
```
📤 Sending test message to Telegram...
✅ Message sent successfully
```

**Cek Telegram** → message masuk! ✅

---

## Troubleshooting

### ❌ "Failed to send message"

**Check 1: Token salah**
```powershell
# Test token manual
$TOKEN = "your-token"
curl "https://api.telegram.org/bot$TOKEN/getMe"
```

Expected response:
```json
{
  "ok": true,
  "result": {
    "id": 7123456789,
    "is_bot": true,
    "first_name": "Bedadung SFEWS Alert Bot",
    "username": "BedadungSFEWSBot"
  }
}
```

Kalo error → token salah, minta token baru ke BotFather:
```
/mybots
[pilih bot]
API Token
```

---

**Check 2: Chat ID salah**
```powershell
# Test send manual
$TOKEN = "your-token"
$CHAT_ID = "123456789"
curl "https://api.telegram.org/bot$TOKEN/sendMessage?chat_id=$CHAT_ID&text=Test"
```

Expected:
```json
{
  "ok": true,
  "result": {
    "message_id": 1,
    "text": "Test"
  }
}
```

Kalo error `"chat not found"` → chat ID salah, cek lagi `getUpdates`.

---

**Check 3: Bot di-block user**
- Buka chat dengan bot
- Tap "Restart" / "Start"
- Coba test lagi

---

**Check 4: Bot bukan admin (untuk group)**
- Group settings
- Administrators
- Bot harus ada di list
- Enable "Post Messages" permission

---

### ❌ "No updates found"

Kirim message ke bot dulu, baru run `getUpdates`.

---

### ❌ Multiple Chat IDs ga jalan

Check format:
```env
# BENAR (no spaces):
TELEGRAM_CHAT_ID=123,-1001234,-1009876

# SALAH (ada spasi):
TELEGRAM_CHAT_ID=123, -1001234, -1009876
```

Clear config:
```bash
php artisan config:clear
php artisan telegram:test
```

---

## Summary Checklist

- [ ] Bot created via @BotFather
- [ ] Token saved
- [ ] Message sent to bot (personal atau group)
- [ ] Chat ID retrieved via `getUpdates`
- [ ] `.env` updated with TOKEN & CHAT_ID
- [ ] `php artisan config:clear` run
- [ ] `php artisan telegram:test` works ✅
- [ ] Message received in Telegram ✅

---

## Quick Reference

### Get Bot Info
```powershell
$TOKEN = "your-token"
curl "https://api.telegram.org/bot$TOKEN/getMe"
```

### Get Updates (Chat IDs)
```powershell
curl "https://api.telegram.org/bot$TOKEN/getUpdates"
```

### Send Test Message
```powershell
$CHAT_ID = "123456789"
curl "https://api.telegram.org/bot$TOKEN/sendMessage?chat_id=$CHAT_ID&text=Test"
```

### Laravel Test Command
```bash
php artisan telegram:test "Custom message"
```

---

**Done!** Bot ready. Sekarang tinggal testing full integration! 🚀

Next: Run `quick-test.bat` untuk test semua fitur sekaligus.
