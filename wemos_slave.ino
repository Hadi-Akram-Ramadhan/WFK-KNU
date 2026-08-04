#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>
#include <SoftwareSerial.h>

// --- 1. KONFIGURASI WIFI ---
const char* ssid     = "NAMA_WIFI_LO";      // Ganti dengan SSID Wi-Fi / Hotspot HP
const char* password = "PASSWORD_WIFI_LO";  // Ganti dengan Password Wi-Fi

// --- 2. KONFIGURASI WEBHOOK LARAVEL / DOKPLOY ---
const char* serverUrl = "https://wfk-samasama.hadooyy.my.id/api/sensor/data";

// Node ID Stasiun Sensor
const char* nodeId = "BEDADUNG_01";

// Software Serial terima data dari Arduino (RX = D5, TX = D6)
SoftwareSerial arduinoSerial(D5, D6);

unsigned long lastSendTime = 0;
const unsigned long sendInterval = 2000; // Kirim webhook setiap 2 detik

void setup() {
  Serial.begin(115200);
  arduinoSerial.begin(9600);

  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);

  Serial.println("\n==========================================");
  Serial.println("  BEDADUNG SFEWS — DOKPLOY WEBHOOK BRIDGE ");
  Serial.println("==========================================");
  Serial.print("Connecting to WiFi: ");
  Serial.println(ssid);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("\n[SUCCESS] WiFi Connected!");
  Serial.print("[INFO] IP Address: ");
  Serial.println(WiFi.localIP());
  Serial.print("[INFO] Webhook URL: ");
  Serial.println(serverUrl);
}

void loop() {
  if (arduinoSerial.available()) {
    String rawData = arduinoSerial.readStringUntil('\n');
    rawData.trim();

    float distance = rawData.toFloat();

    // Pastikan data jarak valid dan sesuai interval 2 detik
    if (distance > 0 && (millis() - lastSendTime >= sendInterval)) {
      lastSendTime = millis();

      Serial.print("\n[DATA ARDUINO] Jarak Air: ");
      Serial.print(distance);
      Serial.println(" cm");

      if (WiFi.status() == WL_CONNECTED) {
        WiFiClient client;
        HTTPClient http;

        http.begin(client, serverUrl);
        http.addHeader("Content-Type", "application/json");

        // Format JSON Payload untuk Webhook Laravel
        String jsonPayload = "{";
        jsonPayload += "\"node_id\":\"" + String(nodeId) + "\",";
        jsonPayload += "\"distance_cm\":" + String(distance, 1) + ",";
        jsonPayload += "\"temperature_c\":28.5,";
        jsonPayload += "\"humidity_percent\":78.0";
        jsonPayload += "}";

        Serial.print("[WEBHOOK POST] Sending payload to Dokploy: ");
        Serial.println(jsonPayload);

        int httpResponseCode = http.POST(jsonPayload);

        if (httpResponseCode > 0) {
          String response = http.getString();
          Serial.print("[WEBHOOK SUCCESS] HTTP Code: ");
          Serial.println(httpResponseCode);
          Serial.print("[WEBHOOK RESPONSE] ");
          Serial.println(response);
        } else {
          Serial.print("[WEBHOOK ERROR] HTTP POST Failed, Error Code: ");
          Serial.println(httpResponseCode);
        }

        http.end();
      } else {
        Serial.println("[WIFI ERROR] Disconnected from WiFi. Reconnecting...");
        WiFi.reconnect();
      }
    }
  }
}