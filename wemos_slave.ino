#include <ESP8266WiFi.h>
#include <WiFiClientSecure.h>
#include <UniversalTelegramBot.h>
#include <SoftwareSerial.h>

// --- KONFIGURASI WIFI & TELEGRAM ---
const char* ssid     = "NAMA_WIFI_LO";      // Ganti SSID Wi-Fi / Hotspot HP
const char* password = "PASSWORD_WIFI_LO";  // Ganti Password Wi-Fi
#define BOTtoken "TOKEN_BOT_TELEGRAM_LO"    // Token dari @BotFather
#define CHAT_ID  "CHAT_ID_TELEGRAM_LO"     // Chat ID dari @myidbot

// Software Serial terima data dari Arduino (RX = D5, TX = D6)
SoftwareSerial arduinoSerial(D5, D6);

X509List cert(TELEGRAM_CERTIFICATE_ROOT);
WiFiClientSecure client;
UniversalTelegramBot bot(BOTtoken, client);

bool alertSent = false;

void setup() {
  Serial.begin(115200);
  arduinoSerial.begin(9600);

  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);
  configTime(0, 0, "pool.ntp.org");
  client.setTrustAnchors(&cert);

  Serial.println("\n--- BEDADUNG SFEWS IoT BRIDGE ---");
  Serial.print("Connecting to WiFi: ");
  Serial.println(ssid);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("\n[SUCCESS] WiFi Connected!");
  Serial.print("[INFO] IP Address: ");
  Serial.println(WiFi.localIP());

  // === TES KONEKSI TELEGRAM SAAT STARTUP ===
  String testMsg = "✅ *BEDADUNG SFEWS ONLINE*\n\n";
  testMsg += "🤖 *Node Status:* Connected to WiFi!\n";
  testMsg += "📡 *IP Address:* " + WiFi.localIP().toString() + "\n";
  testMsg += "⚙️ *Bridge System:* Monitoring active.";
  
  if (bot.sendMessage(CHAT_ID, testMsg, "Markdown")) {
    Serial.println("[TELEGRAM TEST] Message sent successfully!");
  } else {
    Serial.println("[TELEGRAM TEST] Failed to send message. Check Bot Token / Chat ID!");
  }
}

void loop() {
  if (arduinoSerial.available()) {
    String rawData = arduinoSerial.readStringUntil('\n');
    rawData.trim();
    
    float distance = rawData.toFloat();
    
    if (distance > 0) {
      Serial.print("[DATA ARDUINO] Jarak: ");
      Serial.print(distance);
      Serial.println(" cm");

      if (distance < 10.0) {
        if (!alertSent) {
          String msg = "🚨 *BEDADUNG FLOOD EARLY WARNING SYSTEM* 🚨\n\n";
          msg += "📍 *Lokasi:* Bridge Sensor Node 01 (Sumbersari)\n";
          msg += "⚠️ *Status:* DANGER / CRITICAL!\n";
          msg += "📊 *Jarak Air:* " + String(distance, 1) + " cm\n";
          msg += "⚡ *Tindakan Otomatis:* Pintu Air Dibuka 90° & Sirene Bahaya Aktif!\n\n";
          msg += "📌 *Peringatan:* Potensi luapan tinggi! Segera lakukan evakuasi.";
          
          if (bot.sendMessage(CHAT_ID, msg, "Markdown")) {
            Serial.println("[ALERT] Emergency Telegram Sent!");
            alertSent = true;
          }
        }
      } else {
        alertSent = false; // Reset flag saat kondisi air aman kembali
      }
    }
  }
}