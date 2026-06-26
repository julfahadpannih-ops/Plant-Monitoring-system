#define BLYNK_TEMPLATE_ID         "TMPL6nvxBKn5S"
#define BLYNK_TEMPLATE_NAME       "IoT Enabled VPM and MS"
#define BLYNK_AUTH_TOKEN          "YOUR_BLYNK_AUTH_TOKEN"  // Replace with your actual token

#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <BlynkSimpleEsp32.h>
#include "DHT.h"
#include <HTTPClient.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <time.h>

// ---------------- WIFI & TELEGRAM ----------------
const char* ssid = "YOUR_WIFI_SSID";            // Replace with your WiFi name
const char* pass = "YOUR_WIFI_PASSWORD";        // Replace with your WiFi password
const char* BOT_TOKEN = "YOUR_TELEGRAM_BOT_TOKEN";  // Replace with your BotFather token
const int64_t CHAT_ID = 0;                      // Replace with your Telegram chat ID (numeric)

// ---------------- PINS ----------------
#define DHTPIN 25
#define DHTTYPE DHT11
#define SOIL_PIN 33
#define RELAY_PIN 26

// --- MGA BAGONG PINS PARA SA NPK ---
#define RE_PIN 4    // Pin para sa Receive Enable
#define DE_PIN 5    // Pin para sa Data Enable
#define NPK_RX 16   
#define NPK_TX 17   

// ---------------- GLOBAL VARIABLES ----------------
int nitrogen = 0, phosphorus = 0, potassium = 0;
int soilThreshold = 40;
float stableLevel = 50.0;
float tempThreshold = 30.0;

bool relayState = false;
bool isManualMode = false;
bool notifiedDry = false;
bool notifiedStable = false;
bool notifiedTemp = false;
bool telegramOnline = true;
unsigned long lastUpdateID = 0;

// --- BAGONG VARIABLES PARA SA COOLDOWN AT AI LOCKOUT ---
unsigned long lastPumpTime = 0;
const unsigned long PUMP_COOLDOWN = 10000; // 10 seconds cooldown para iwas on/off spam
bool aiWatering = false; // Flag para malaman kung AI ang kumokontrol

LiquidCrystal_I2C lcd(0x27, 16, 2);
DHT dht(DHTPIN, DHTTYPE);
BlynkTimer timer;

// Bagong NPK Inquiry Frame
const byte npkInquiryFrame[] = {0x01, 0x03, 0x00, 0x1E, 0x00, 0x03, 0x65, 0xCD};
byte npkValues[11];

// ---------------- BLYNK CONTROL ----------------
BLYNK_WRITE(V4) {
  int pinValue = param.asInt();

  // 1. Kapag nag-a-auto pump ang AI, i-block ang manual button
  if (aiWatering) {
    Serial.println("❌ Manual override rejected: AI is currently auto-watering.");
    sendTelegram("⚠️ Manual control disabled while AI is auto-watering.");
    Blynk.virtualWrite(V4, 1); // I-force pabalik sa ON ang UI button
    return;
  }

  // 2. Cooldown protection para sa manual spam
  if (millis() - lastPumpTime < PUMP_COOLDOWN) {
    Serial.println("⏳ Manual override rejected: Cooldown active.");
    Blynk.virtualWrite(V4, relayState); // I-revert ang UI button state
    return;
  }

  relayState = (pinValue == 1);
  digitalWrite(RELAY_PIN, relayState ? LOW : HIGH);
  isManualMode = true;
  lastPumpTime = millis(); // I-record ang oras ng pag-switch
  
  String msg = relayState ? "Manual Control: Pump ON" : "Manual Control: Pump OFF";
  sendTelegram(msg);
  Serial.println(msg);
}

// ---------------- TELEGRAM FUNCTIONS ----------------
void sendTelegram(String text) {
  if (WiFi.status() != WL_CONNECTED) return;
  WiFiClientSecure client;
  client.setInsecure();
  HTTPClient https;
  String url = "https://api.telegram.org/bot" + String(BOT_TOKEN) + "/sendMessage";
  https.begin(client, url);
  https.addHeader("Content-Type", "application/x-www-form-urlencoded");
  https.POST("chat_id=" + String(CHAT_ID) + "&text=" + text);
  https.end();
}

// ---------------- SENSOR LOGIC ----------------
void readSensors() {
  int soilRaw = analogRead(SOIL_PIN);
  int soilPercent = map(soilRaw, 3000, 1500, 0, 100);
  soilPercent = constrain(soilPercent, 0, 100);
  
  float temp = dht.readTemperature();
  float hum = dht.readHumidity();

  // --- AUTOMATION LOGIC ---
  if (!isManualMode) {
    if (soilPercent < soilThreshold) {
      // Check cooldown bago mag-ON
      if (!relayState && (millis() - lastPumpTime >= PUMP_COOLDOWN)) {
        digitalWrite(RELAY_PIN, LOW);
        relayState = true;
        aiWatering = true; // AI took control, i-lock ang manual
        lastPumpTime = millis();
        
        if (!notifiedDry) {
          String logMsg = "🚨 AUTO PUMP ON (AI Decision) - Soil Dry (" + String(soilPercent) + "%)";
          sendTelegram(logMsg);
          Serial.println(logMsg);
          notifiedDry = true;
          notifiedStable = false;
        }
      }
    } else if (soilPercent >= stableLevel) {
      // Check cooldown bago mag-OFF
      if (relayState && (millis() - lastPumpTime >= PUMP_COOLDOWN)) {
        digitalWrite(RELAY_PIN, HIGH);
        relayState = false;
        aiWatering = false; // AI released control, pwede na ulit mag-manual
        lastPumpTime = millis();
        
        if (!notifiedStable) {
          String logMsg = "✅ Soil Stable (" + String(soilPercent) + "%). Auto-Pump OFF";
          sendTelegram(logMsg);
          Serial.println(logMsg);
          notifiedStable = true;
          notifiedDry = false;
        }
      }
    }
  }

  // --- LCD & BLYNK UPDATE ---
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("S:" + String(soilPercent) + "% " + (isManualMode ? "MAN" : "AUTO"));
  lcd.setCursor(0, 1);
  lcd.print("T:" + String(temp, 1) + " P:" + (relayState ? "ON" : "OFF"));

  Blynk.virtualWrite(V0, soilPercent);
  Blynk.virtualWrite(V1, temp);
  Blynk.virtualWrite(V2, hum);
  Blynk.virtualWrite(V4, relayState); 
}

// ---------------- TELEGRAM COMMANDS ----------------
void handleTelegramCommands() {
  if (WiFi.status() != WL_CONNECTED) return;
  WiFiClientSecure client; client.setInsecure();
  HTTPClient https;
  String url = "https://api.telegram.org/bot" + String(BOT_TOKEN) + "/getUpdates?offset=" + String(lastUpdateID + 1);
  https.begin(client, url);
  
  if (https.GET() == 200) {
    String payload = https.getString();
    if (payload.indexOf("\"update_id\":") > 0) {
      int pos = payload.indexOf("\"update_id\":");
      lastUpdateID = payload.substring(pos + 12).toInt();
      
      if (payload.indexOf("/water_on") > 0) {
        if (aiWatering) {
          sendTelegram("⚠️ Command rejected: AI is currently auto-watering.");
        } else if (millis() - lastPumpTime < PUMP_COOLDOWN) {
          sendTelegram("⏳ Please wait! Pump cooldown is active.");
        } else {
          isManualMode = true; 
          relayState = true;
          digitalWrite(RELAY_PIN, LOW);
          lastPumpTime = millis();
          sendTelegram("Manual Mode: Pump ON");
        }
      } 
      else if (payload.indexOf("/water_off") > 0) {
        if (aiWatering) {
          sendTelegram("⚠️ Command rejected: AI is currently auto-watering.");
        } else if (millis() - lastPumpTime < PUMP_COOLDOWN) {
          sendTelegram("⏳ Please wait! Pump cooldown is active.");
        } else {
          isManualMode = true;
          relayState = false;
          digitalWrite(RELAY_PIN, HIGH);
          lastPumpTime = millis();
          sendTelegram("Manual Mode: Pump OFF");
        }
      }
      else if (payload.indexOf("/auto") > 0) {
        isManualMode = false;
        aiWatering = false; // Reset AI lock just in case
        sendTelegram("Auto Mode: Enabled (Sensors in control)");
      }
    }
  }
  https.end();
}

// ---------------- NPK LOGIC (UPDATED) ----------------
void readNPK() {
  // 1. I-set sa TRANSMIT MODE bago magpadala ng command (parehong HIGH)
  digitalWrite(RE_PIN, HIGH);
  digitalWrite(DE_PIN, HIGH); 
  delay(10);
  
  Serial2.write(npkInquiryFrame, sizeof(npkInquiryFrame));
  Serial2.flush(); 
  
  // 2. I-set pabalik sa RECEIVE MODE para hintayin ang sagot ng sensor (parehong LOW)
  digitalWrite(RE_PIN, LOW);
  digitalWrite(DE_PIN, LOW); 
  
  // Hintayin ang data (up to 1000ms timeout para iwas hang)
  unsigned long startTime = millis();
  while(Serial2.available() < 11 && millis() - startTime < 1000) {
    delay(10);
  }

  if (Serial2.available() >= 11) {
    for (int i = 0; i < 11; i++) {
      npkValues[i] = Serial2.read();
    }
    
    // Kunin ang NPK values
    nitrogen = (npkValues[3] << 8) | npkValues[4];
    phosphorus = (npkValues[5] << 8) | npkValues[6];
    potassium = (npkValues[7] << 8) | npkValues[8];
    
    Serial.println("--- NPK Real-Time Data ---");
    Serial.print("Nitrogen (N): "); Serial.print(nitrogen); Serial.println(" mg/kg");
    Serial.print("Phosphorus (P): "); Serial.print(phosphorus); Serial.println(" mg/kg");
    Serial.print("Potassium (K): "); Serial.print(potassium); Serial.println(" mg/kg");
    Serial.println("--------------------------");
    
    // I-send ang bagong NPK data sa Blynk!
    Blynk.virtualWrite(V5, nitrogen);
    Blynk.virtualWrite(V6, phosphorus);
    Blynk.virtualWrite(V7, potassium);
  } else {
    // Clear buffer kung nag-timeout o kulang ang data
    while(Serial2.available()) Serial2.read();
    Serial.println("\n[ERROR] Timeout: Walang na-detect na NPK data. Check wiring at RE/DE pins.");
  }
}

// ---------------- SETUP & LOOP ----------------
void setup() {
  Serial.begin(115200);
  Serial2.begin(4800, SERIAL_8N1, NPK_RX, NPK_TX);
  
  pinMode(RELAY_PIN, OUTPUT);
  digitalWrite(RELAY_PIN, HIGH);
  
  // Setup para sa RE at DE Pins ng NPK
  pinMode(RE_PIN, OUTPUT);
  pinMode(DE_PIN, OUTPUT);
  
  // I-set sa RECEIVE MODE by default (parehong LOW)
  digitalWrite(RE_PIN, LOW); 
  digitalWrite(DE_PIN, LOW); 
  
  dht.begin();
  Wire.begin(21, 22);
  lcd.init();
  lcd.backlight();
  
  WiFi.begin(ssid, pass);
  while (WiFi.status() != WL_CONNECTED) { delay(500); }
  
  Blynk.begin(BLYNK_AUTH_TOKEN, ssid, pass);
  
  timer.setInterval(5000, readSensors);            // Every 5s
  timer.setInterval(10000, readNPK);               // Every 10s
  timer.setInterval(4000, handleTelegramCommands); // Every 4s
  
  Serial.println("System Ready.");
}

void loop() {
  Blynk.run();
  timer.run();
}