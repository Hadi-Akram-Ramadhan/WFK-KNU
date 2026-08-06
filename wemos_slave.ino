#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClientSecure.h>
#include <SoftwareSerial.h>

// --- 1. KONFIGURASI WIFI ---
const char* ssid     = "hadi";      // Ganti dengan SSID Wi-Fi / Hotspot HP
const char* password = "";  // Ganti dengan Password Wi-Fi

// --- 2. KONFIGURASI WEBHOOK LARAVEL / DOKPLOY ---
const char* serverUrl = "https://wfk-samasama.hadooyy.my.id/api/sensor/data";

// Node ID Stasiun Sensor
const char* nodeId = "BEDADUNG_01";

// Software Serial terima data dari Arduino (RX = D5, TX = D6)
SoftwareSerial arduinoSerial(D5, D6);

unsigned long lastSendTime = 0;
const unsigned long sendInterval = 1000; // Kirim webhook setiap 1 detik (Realtime Ultra-fast)

void setup() {
  Serial.begin(115200);
  arduinoSerial.begin(9600);
  arduinoSerial.setTimeout(100); // Mencegah Serial timeout hanging 1 detik

  Serial.println("\n==========================================");
  Serial.println("  BEDADUNG SFEWS — DOKPLOY WEBHOOK BRIDGE ");
  Serial.println("==========================================");

  // Clear previous WiFi configuration from ESP flash & set station mode
  WiFi.persistent(false);
  WiFi.mode(WIFI_STA);
  WiFi.disconnect();
  delay(100);

  WiFi.setAutoReconnect(true);
  WiFi.begin(ssid, password);

  Serial.print("Connecting to WiFi: ");
  Serial.println(ssid);

  int retry = 0;
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
    retry++;

    // Print status hint every 20 retries (10 seconds)
    if (retry % 20 == 0) {
      Serial.println();
      Serial.print("[RETRY] Still connecting to '");
      Serial.print(ssid);
      Serial.println("'... Make sure HP Hotspot is set to 2.4 GHz & WPA2!");
    }
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

    float distance = 0.0;
    float temp = 0.0;
    float hum = 0.0;

    // Parse data CSV dari Arduino: "distance,temp,hum"
    int firstComma = rawData.indexOf(',');
    int secondComma = rawData.indexOf(',', firstComma + 1);

    if (firstComma > 0 && secondComma > firstComma) {
      distance = rawData.substring(0, firstComma).toFloat();
      temp     = rawData.substring(firstComma + 1, secondComma).toFloat();
      hum      = rawData.substring(secondComma + 1).toFloat();
    } else {
      distance = rawData.toFloat(); // Fallback jika cuma jarak
      temp = 28.5;
      hum = 65.0;
    }

    // Pastikan data jarak valid dan sesuai interval
    if (distance > 0 && (millis() - lastSendTime >= sendInterval)) {
      lastSendTime = millis();

      Serial.print("\n[DATA ARDUINO] Jarak: ");
      Serial.print(distance);
      Serial.print(" cm | Suhu: ");
      Serial.print(temp);
      Serial.print(" °C | Kelembapan: ");
      Serial.print(hum);
      Serial.println(" %RH");

      if (WiFi.status() == WL_CONNECTED) {
        WiFiClientSecure client;
        client.setInsecure(); // Skip SSL cert verification for speed
        client.setTimeout(3000);

        HTTPClient http;
        http.setTimeout(3000);
        http.setReuse(true); // Keep-alive connection to bypass SSL handshake overhead

        if (http.begin(client, serverUrl)) {
          http.addHeader("Content-Type", "application/json");

          // Format JSON Payload untuk Webhook Laravel
          String jsonPayload = "{";
          jsonPayload += "\"node_id\":\"" + String(nodeId) + "\",";
          jsonPayload += "\"distance_cm\":" + String(distance, 1) + ",";
          jsonPayload += "\"temperature_c\":" + String(temp, 1) + ",";
          jsonPayload += "\"humidity_percent\":" + String(hum, 1);
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
          Serial.println("[WEBHOOK ERROR] Unable to connect to server URL");
        }
      } else {
        Serial.println("[WIFI ERROR] Disconnected from WiFi. Reconnecting...");
        WiFi.reconnect();
      }
    }
  }
}