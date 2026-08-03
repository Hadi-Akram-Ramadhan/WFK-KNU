#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <Servo.h>
#include <SoftwareSerial.h>

// --- PIN CONFIGURATION ---
#define TRIG_PIN 2
#define ECHO_PIN 3
#define LED_RED 4
#define LED_YELLOW 5
#define LED_GREEN 6
#define BUZZER_PIN 8
#define SERVO_PIN 9

// --- OBJECT INITIALIZATION ---
LiquidCrystal_I2C lcd(0x27, 16, 2);
Servo servoGate;
SoftwareSerial wemosSerial(12, 13); // RX = Pin 12, TX = Pin 13 (ke Wemos)

// Melodi Kuning: High-Pitch Arpeggio
int yellowMelody[] = {1047, 1319, 1568, 2093, 1568, 1319}; 
int noteDurations[] = {80, 80, 80, 150, 80, 80};

void setup() {
  Serial.begin(9600);
  wemosSerial.begin(9600);

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

  // LCD Setup
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
  float distance = duration * 0.034 / 2;

  // Kirim data jarak ke Wemos D1 Mini via Serial
  wemosSerial.println(distance);

  // 2. DISPLAY LCD & AKTUATOR
  lcd.setCursor(0, 0);
  lcd.print("Jarak: ");
  lcd.print(distance, 1);
  lcd.print(" cm   ");

  lcd.setCursor(0, 1);

  if (distance < 10.0) { // DANGER MODE (< 10cm)
    lcd.print("Status: DANGER  ");
    
    digitalWrite(LED_YELLOW, LOW);
    digitalWrite(LED_GREEN, LOW);
    servoGate.write(90);     // Pintu air jebol buka 90 derajat

    // --- SIRINE HOROR / NUCLEAR AIR-RAID ALARM ---
    
    // Phase 1: High Strobe Screech (Bikin Telinga Pekak) + LED Red ON
    digitalWrite(LED_RED, HIGH);
    for (int i = 0; i < 4; i++) {
      tone(BUZZER_PIN, 3800); delay(25); // Frekuensi super tinggi
      tone(BUZZER_PIN, 2400); delay(25);
    }
    
    // Phase 2: Chaotic Pitch Drop (Meluncur Horor) + LED Red OFF
    digitalWrite(LED_RED, LOW);
    for (int hz = 3600; hz >= 800; hz -= 120) {
      tone(BUZZER_PIN, hz);
      delay(6);
    }

  } else if (distance >= 10.0 && distance <= 20.0) { // CAUTION MODE (10cm - 20cm)
    lcd.print("Status: CAUTION ");
    
    digitalWrite(LED_RED, LOW);
    digitalWrite(LED_GREEN, LOW);
    servoGate.write(0);      // Pintu air siaga tetep tutup rapat

    // KEDIP PELAN + MELODI WARNING
    digitalWrite(LED_YELLOW, HIGH);
    for (int i = 0; i < 6; i++) {
      tone(BUZZER_PIN, yellowMelody[i]);
      delay(noteDurations[i]);
    }
    noTone(BUZZER_PIN);

    digitalWrite(LED_YELLOW, LOW);
    delay(400); // Jeda mati 0.4 detik

  } else { // SAFE MODE (> 20cm)
    lcd.print("Status: SAFE    ");
    
    digitalWrite(LED_RED, LOW);
    digitalWrite(LED_YELLOW, LOW);
    digitalWrite(LED_GREEN, HIGH);
    
    noTone(BUZZER_PIN);
    servoGate.write(0);      // Pintu air tutup rapat
    delay(300);
  }
}