/**
 * Bedadung SFEWS — Wemos D1 Mini (ESP8266)
 * IoT Bridge: Arduino Uno <--> VPS Laravel API + Telegram
 *
 * Fungsi:
 *   1. Terima data jarak (cm) dari Arduino Uno via SoftwareSerial
 *   2. POST data ke VPS Laravel API (sensor/data)
 *   3. Ambil hardware command dari API (servo/siren)
 *   4. Teruskan perintah ke Arduino via SoftwareSerial
 *   5. Kirim alert Telegram untuk kondisi DANGER
 *
 * Wiring:
 *   Arduino TX (pin 10) -> Wemos RX (D5)
 *   Arduino RX (pin 11) -> Wemos TX (D6)
 */

#include <ESP8266WiFi.h>
#include <WiFiClientSecure.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>
#include <UniversalTelegramBot.h>
#include <SoftwareSerial.h>
#include <ArduinoJson.h>

// ── KONFIGURASI — Ganti sesuai setting lo ──────────────────────────
const char* WIFI_SSID     = "NAMA_WIFI_LO";
const char* WIFI_PASSWORD = "PASSWORD_WIFI_LO";

// Token & Chat ID Telegram (dari @BotFather dan @myidbot)
#define BOTtoken  "TOKEN_BOT_TELEGRAM_LO"
#define CHAT_ID   "CHAT_ID_TELEGRAM_LO"

// URL VPS Laravel API
// Untuk lokal (testing): "http://192.168.x.x:8000"
// Untuk VPS production : "http://your-vps-domain.com"
const char* API_BASE_URL  = "http://YOUR_VPS_IP_OR_DOMAIN";
const char* API_TOKEN     = "bedadung-sfews-secret-token-01";
const char* NODE_ID       = "BEDADUNG_01";

// ── INTERVAL ────────────────────────────────────────────────────────
const unsigned long API_INTERVAL  = 5000;    // Kirim data ke API setiap 5 detik
const unsigned long CMD_INTERVAL  = 10000;   // Poll command dari API setiap 10 detik
const unsigned long TG_COOLDOWN   = 60000;   // Cooldown Telegram alert: 60 detik

// ── OBJECTS ─────────────────────────────────────────────────────────
SoftwareSerial arduinoSerial(D5, D6);  // RX=D5, TX=D6

X509List cert(TELEGRAM_CERTIFICATE_ROOT);
WiFiClientSecure secureClient;
UniversalTelegramBot bot(BOTtoken, secureClient);

WiFiClient httpClient;   // Insecure client untuk HTTP ke VPS

// ── STATE ────────────────────────────────────────────────────────────
bool     alertSent       = false;
float    lastDistance    = 0.0;
unsigned long lastApiSend   = 0;
unsigned long lastCmdPoll   = 0;
unsigned long lastTelegramAlert = 0;

// ─────────────────────────────────────────────────────────────────────

void setup() {
  Serial.begin(115200);
  arduinoSerial.begin(9600);

  Serial.println("\n=== BEDADUNG SFEWS — Wemos Bridge v2.0 ===");
  Serial.println("[INFO] Menghubungkan ke WiFi...");

  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
  configTime(7 * 3600, 0, "pool.ntp.org");  // WIB UTC+7
  secureClient.setTrustAnchors(&cert);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("\n[OK] WiFi Terhubung!");
  Serial.print("[INFO] IP: ");
  Serial.println(WiFi.localIP());

  // Kirim notif startup ke Telegram
  sendTelegramStartup();
}

void loop() {
  // 1. Baca data dari Arduino Uno
  if (arduinoSerial.available()) {
    String rawData = arduinoSerial.readStringUntil('\n');
    rawData.trim();
    float distance = rawData.toFloat();

    if (distance > 0) {
      lastDistance = distance;
      Serial.print("[ARDUINO] Jarak: ");
      Serial.print(distance);
      Serial.println(" cm");

      // Kirim alert Telegram hanya jika DANGER & belum kirim dalam 60 detik
      if (distance < 10.0) {
        unsigned long now = millis();
        if (!alertSent || (now - lastTelegramAlert > TG_COOLDOWN)) {
          sendTelegramAlert(distance);
          alertSent = true;
          lastTelegramAlert = now;
        }
      } else {
        alertSent = false;
      }
    }
  }

  // 2. POST data ke Laravel API (setiap API_INTERVAL)
  unsigned long now = millis();
  if (lastDistance > 0 && (now - lastApiSend > API_INTERVAL)) {
    sendDataToAPI(lastDistance);
    lastApiSend = now;
  }

  // 3. Poll hardware commands dari API (setiap CMD_INTERVAL)
  if (now - lastCmdPoll > CMD_INTERVAL) {
    fetchAndExecuteCommands();
    lastCmdPoll = now;
  }

  delay(100);
}

// ─── POST sensor data ke Laravel API ─────────────────────────────────
void sendDataToAPI(float distance) {
  if (WiFi.status() != WL_CONNECTED) return;

  String url = String(API_BASE_URL) + "/api/sensor/data";

  // Build JSON payload
  StaticJsonDocument<128> doc;
  doc["node_id"]  = NODE_ID;
  doc["distance"] = distance;
  String body;
  serializeJson(doc, body);

  HTTPClient http;
  http.begin(httpClient, url);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("Authorization", String("Bearer ") + API_TOKEN);
  http.addHeader("Accept", "application/json");
  http.setTimeout(8000);

  int httpCode = http.POST(body);

  if (httpCode == 200) {
    String response = http.getString();
    Serial.println("[API] Data terkirim. Response: " + response);

    // Parse commands dari response
    StaticJsonDocument<512> resp;
    if (!deserializeJson(resp, response)) {
      JsonArray commands = resp["commands"].as<JsonArray>();
      for (JsonObject cmd : commands) {
        executeCommand(cmd["type"].as<String>(), cmd["payload"]);
      }
    }
  } else {
    Serial.print("[API] Error HTTP: ");
    Serial.println(httpCode);
  }

  http.end();
}

// ─── Poll hardware commands dari API ─────────────────────────────────
void fetchAndExecuteCommands() {
  if (WiFi.status() != WL_CONNECTED) return;

  String url = String(API_BASE_URL) + "/api/hardware/commands/" + NODE_ID;

  HTTPClient http;
  http.begin(httpClient, url);
  http.addHeader("Accept", "application/json");
  http.setTimeout(5000);

  int httpCode = http.GET();

  if (httpCode == 200) {
    String response = http.getString();
    StaticJsonDocument<512> doc;
    if (!deserializeJson(doc, response)) {
      JsonArray commands = doc["commands"].as<JsonArray>();
      for (JsonObject cmd : commands) {
        executeCommand(cmd["type"].as<String>(), cmd["payload"]);
      }
    }
  }

  http.end();
}

// ─── Eksekusi command ke Arduino ──────────────────────────────────────
void executeCommand(String type, JsonObject payload) {
  Serial.print("[CMD] Execute: " + type + " | ");

  if (type == "servo") {
    int angle = payload["angle"] | 0;
    String cmd = "SERVO:" + String(angle);
    arduinoSerial.println(cmd);
    Serial.println(cmd);
  }
  else if (type == "siren") {
    bool active = payload["active"] | false;
    String cmd = active ? "SIREN:ON" : "SIREN:OFF";
    arduinoSerial.println(cmd);
    Serial.println(cmd);
  }
  else if (type == "automated_mode") {
    bool active = payload["active"] | true;
    String cmd = active ? "AUTO:ON" : "AUTO:OFF";
    arduinoSerial.println(cmd);
    Serial.println(cmd);
  }
  else {
    Serial.println("UNKNOWN");
  }
}

// ─── Telegram: Startup notification ──────────────────────────────────
void sendTelegramStartup() {
  String msg = "✅ *BEDADUNG SFEWS ONLINE*\n\n";
  msg += "🤖 *Node:* " + String(NODE_ID) + "\n";
  msg += "📡 *IP:* " + WiFi.localIP().toString() + "\n";
  msg += "⚙️ *Bridge:* Siap memantau Sungai Bedadung.";

  if (bot.sendMessage(CHAT_ID, msg, "Markdown")) {
    Serial.println("[TELEGRAM] Startup notification terkirim.");
  } else {
    Serial.println("[TELEGRAM] Gagal kirim startup notification!");
  }
}

// ─── Telegram: Flood alert ────────────────────────────────────────────
void sendTelegramAlert(float distance) {
  String msg = "🚨 *BEDADUNG FLOOD EARLY WARNING* 🚨\n\n";
  msg += "📍 *Node:* " + String(NODE_ID) + " — Checkpoint Alpha\n";
  msg += "⚠️ *Status:* DANGER / CRITICAL\n";
  msg += "📊 *Jarak Air:* `" + String(distance, 1) + " cm`\n";
  msg += "⚡ *Auto Action:* Servo 90° & Sirene aktif\n\n";
  msg += "🤖 _AI sedang menganalisis data..._\n";
  msg += "🔗 Dashboard: " + String(API_BASE_URL) + "/dashboard";

  if (bot.sendMessage(CHAT_ID, msg, "Markdown")) {
    Serial.println("[TELEGRAM] 🚨 Alert terkirim!");
  }
}