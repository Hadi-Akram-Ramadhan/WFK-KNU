# 🤖 Setup Ollama AI - Quick Start

## Windows Installation (5 menit)

### Option 1: Installer (Recommended)

1. **Download Ollama:**
   - Buka: https://ollama.com/download/windows
   - Atau direct link: https://ollama.com/download/OllamaSetup.exe

2. **Install:**
   - Double click `OllamaSetup.exe`
   - Follow installer (Next → Next → Install)
   - Ollama auto-starts as Windows service

3. **Verify Installation:**
```powershell
ollama --version
# Output: ollama version is 0.x.x
```

### Option 2: Winget

```powershell
# Install via Windows Package Manager
winget install Ollama.Ollama

# Verify
ollama --version
```

---

## Pull Model (1-2 menit download)

```powershell
# Pull lightweight model (1GB, recommended untuk laptop)
ollama pull qwen2.5:1.5b

# Atau alternatif:
ollama pull llama3.2:3b     # 2GB, lebih akurat
ollama pull phi3:mini       # 2GB, Microsoft
```

**Recommended:** `qwen2.5:1.5b` (paling ringan, cukup akurat)

---

## Start Ollama Server

Ollama auto-starts setelah install. Kalo perlu restart manual:

```powershell
# Start (biasanya udah jalan)
ollama serve

# Atau restart Windows service
Restart-Service Ollama
```

**Check if running:**
```powershell
curl http://localhost:11434/api/tags

# Expected output:
# {"models":[{"name":"qwen2.5:1.5b",...}]}
```

---

## Test Ollama

```bash
# Test via Laravel
cd C:\Users\kkhad\Desktop\WFK-KNU
php artisan ollama:test
```

**Expected output:**
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

---

## Quick Test (Manual)

```powershell
# Test model directly
ollama run qwen2.5:1.5b "Halo, apa kabar?"

# Expected:
# Halo! Kabar saya baik, terima kasih. Ada yang bisa saya bantu?
```

---

## Troubleshooting

### ❌ "ollama: command not found"

**Fix:** Restart terminal atau tambah ke PATH manually:
```powershell
# Add to PATH
$env:Path += ";C:\Users\$env:USERNAME\AppData\Local\Programs\Ollama"

# Permanent (run as Admin):
[Environment]::SetEnvironmentVariable("Path", $env:Path + ";C:\Users\$env:USERNAME\AppData\Local\Programs\Ollama", "Machine")
```

### ❌ "connection refused" di Laravel

**Check if running:**
```powershell
Get-Process ollama
# Should show ollama.exe process

# If not running, start it:
ollama serve
```

### ❌ Port 11434 already in use

```powershell
# Kill existing process
taskkill /F /IM ollama.exe

# Start again
ollama serve
```

### ❌ Model slow (> 30 seconds)

**Options:**
1. Use smaller model: `qwen2.5:1.5b` (fastest)
2. Close background apps (Chrome, etc)
3. Production: Deploy ke GPU server (AWS g4dn.xlarge)

---

## Models Comparison

| Model | Size | Speed (CPU) | Accuracy | Recommended |
|-------|------|-------------|----------|-------------|
| `qwen2.5:1.5b` | 1GB | ~11-15s | Good | ✅ **Best for laptop** |
| `llama3.2:3b` | 2GB | ~25-35s | Better | For desktop |
| `phi3:mini` | 2GB | ~20-30s | Better | Alternative |
| `qwen2.5:7b` | 4GB | ~60-90s | Best | GPU only |

---

## Production Setup (Optional)

Untuk production dengan 1000+ users, gunakan GPU server:

### AWS EC2 (Recommended)
```bash
# Instance: g4dn.xlarge (T4 GPU)
# Install Ollama
curl -fsSL https://ollama.com/install.sh | sh

# Pull model
ollama pull qwen2.5:1.5b

# Start service
sudo systemctl enable ollama
sudo systemctl start ollama

# Result: Response time < 2s ✅
```

### Local VPS dengan GPU
- Minimum: GTX 1660 / RTX 3060
- RAM: 8GB+
- Storage: 20GB+

---

## Update .env

```env
# Local (default)
OLLAMA_URL=http://localhost:11434
OLLAMA_MODEL=qwen2.5:1.5b
OLLAMA_TIMEOUT=60

# Production GPU server
OLLAMA_URL=https://your-gpu-server.com:11434
OLLAMA_MODEL=qwen2.5:1.5b
OLLAMA_TIMEOUT=30
```

---

## Commands Reference

```bash
# List installed models
ollama list

# Pull new model
ollama pull qwen2.5:1.5b

# Run interactive chat
ollama run qwen2.5:1.5b

# Remove model
ollama rm qwen2.5:1.5b

# Show model info
ollama show qwen2.5:1.5b

# Check version
ollama --version

# Start server
ollama serve
```

---

## Laravel Integration

Setelah Ollama installed:

```bash
# Test connection
php artisan ollama:test

# Test full AI analysis
php artisan ai:test

# Check if fallback used
php artisan tinker
>>> AIAnalysis::latest()->first()->model_used
# "qwen2.5:1.5b" = Ollama working ✅
# "fallback" = Ollama down, using rule-based ⚠️
```

---

## Performance Tips

1. **Close background apps** saat testing (Chrome, games, etc)
2. **Use SSD** for faster model loading
3. **Upgrade RAM** (min 8GB, recommended 16GB)
4. **For production:** Always use GPU server

---

## Summary Checklist

- [ ] Ollama downloaded & installed
- [ ] Model pulled: `ollama pull qwen2.5:1.5b`
- [ ] Service running: `curl http://localhost:11434/api/tags`
- [ ] Laravel test passes: `php artisan ollama:test`
- [ ] Full integration works: `php artisan ai:test`

**Done!** AI ready untuk local development! 🚀

Next: Run `quick-test.bat` untuk test semua fitur.
