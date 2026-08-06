#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <Servo.h>
#include <SoftwareSerial.h>
#include <DHT.h>

// --- PIN CONFIGURATION ---
#define TRIG_PIN     2
#define ECHO_PIN     3
#define LED_RED      4
#define LED_YELLOW   5
#define LED_GREEN    6
#define DHT_PIN      7
#define BUZZER_PIN   8
#define SERVO_PIN    9
#define WEMOS_RX     12  // Connect ke Pin D6 Wemos
#define WEMOS_TX     13  // Connect ke Pin D5 Wemos

#define DHTTYPE      DHT11

// --- OBJECT INITIALIZATION ---
LiquidCrystal_I2C lcd(0x27, 16, 2);
Servo servoGate;
SoftwareSerial wemosSerial(WEMOS_RX, WEMOS_TX);
DHT dht(DHT_PIN, DHTTYPE);

// Melodi Kuning: High-Pitch Arpeggio
int yellowMelody[] = {1047, 1319, 1568, 2093, 1568, 1319}; 
int noteDurations[] = {80, 80, 80, 150, 80, 80};

void setup() {
  Serial.begin(9600);
  wemosSerial.begin(9600);
  dht.begin();

  // Pin Modes
  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);
  pinMode(LED_RED, OUTPUT);
  pinMode(LED_YELLOW, OUTPUT);
  pinMode(LED_GREEN, OUTPUT);
  pinMode(BUZZER_PIN, OUTPUT);

  // Servo Setup
  servoGate.attach(SERVO_PIN);
  servoGate.write(0); // Pintu air tertutup rapat di awal

  // LCD Startup Screen
  lcd.init();
  lcd.backlight();
  lcd.setCursor(0, 0);
  lcd.print(" Bedadung SFEWS ");
  lcd.setCursor(0, 1);
  lcd.print("  System Ready  ");
  delay(2000);
  lcd.clear();
}

void loop() {
  // 1. BACA SENSOR ULTRASONIC
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(2);
  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG_PIN, LOW);

  long duration = pulseIn(ECHO_PIN, HIGH);
  float distance = duration * 0.034 / 2.0;

  // 2. BACA SENSOR DHT11 (Non-blocking setiap 2 detik)
  static unsigned long lastDHTRead = 0;
  static float temp = 28.5;
  static float hum = 65.0;

  if (millis() - lastDHTRead >= 2000) {
    lastDHTRead = millis();
    float t = dht.readTemperature();
    float h = dht.readHumidity();
    if (!isnan(t)) temp = t;
    if (!isnan(h)) hum = h;
  }

  // Kirim data lengkap (jarak, suhu, kelembapan) ke Wemos D1 Mini via Serial CSV format
  wemosSerial.print(distance, 1);
  wemosSerial.print(",");
  wemosSerial.print(temp, 1);
  wemosSerial.print(",");
  wemosSerial.println(hum, 1);

  // 3. LCD LINE 1: Distance & Temperature
  lcd.setCursor(0, 0);
  lcd.print("D:");
  lcd.print(distance, 1);
  lcd.print("cm ");
  
  lcd.print("T:");
  lcd.print((int)temp);
  lcd.print("C   ");

  // 4. ACTUATOR LOGIC + LCD LINE 2 STATUS
  lcd.setCursor(0, 1);

  if (distance < 10.0) { // DANGER MODE (< 10cm)
    lcd.print("DANGER! EVACUATE");
    
    digitalWrite(LED_YELLOW, LOW);
    digitalWrite(LED_GREEN, LOW);
    servoGate.write(90);     // Open floodgate 90 degrees

    // Rapid blink + emergency siren (Air-Raid Siren)
    digitalWrite(LED_RED, HIGH);
    for (int i = 0; i < 4; i++) {
      tone(BUZZER_PIN, 3800); delay(25);
      tone(BUZZER_PIN, 2400); delay(25);
    }
    
    digitalWrite(LED_RED, LOW);
    for (int hz = 3600; hz >= 800; hz -= 120) {
      tone(BUZZER_PIN, hz);
      delay(6);
    }

  } else if (distance >= 10.0 && distance <= 20.0) { // CAUTION MODE (10-20cm)
    lcd.print("CAUTION! STANDBY");
    
    digitalWrite(LED_RED, LOW);
    digitalWrite(LED_GREEN, LOW);
    servoGate.write(0);      // Keep floodgate closed on standby

    // Slow blink + warning melody
    digitalWrite(LED_YELLOW, HIGH);
    for (int i = 0; i < 6; i++) {
      tone(BUZZER_PIN, yellowMelody[i]);
      delay(noteDurations[i]);
    }
    noTone(BUZZER_PIN);

    digitalWrite(LED_YELLOW, LOW);
    delay(200); // 0.2s blink pause

  } else { // SAFE MODE (> 20cm)
    lcd.print("SAFE — NORMAL   ");
    
    digitalWrite(LED_RED, LOW);
    digitalWrite(LED_YELLOW, LOW);
    digitalWrite(LED_GREEN, HIGH);
    
    noTone(BUZZER_PIN);
    servoGate.write(0);      // Floodgate closed
    delay(100);
  }
}