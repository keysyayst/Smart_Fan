#include <WiFi.h>
#include <HTTPClient.h>
#include "DHT.h"
#include <ArduinoJson.h>

/* ================= CONFIG ================= */
#define DHTPIN 4
#define DHTTYPE DHT11
#define FAN_RELAY_PIN 26      
#define LED_HIJAU 27
#define LED_MERAH 25

const char* ssid = "keyy";
const char* password = "01122315"
const char* serverSensor = "http://172.20.10.11:8000/api/sensor";
const char* serverManual = "http://172.20.10.11:8000/manual/latest";

DHT dht(DHTPIN, DHTTYPE);

// STATUS - HANYA FAN (LED OTOMATIS MENGIKUTI)
String fanStatus = "OFF";

// MODE CONTROL
String currentMode = "AUTO";  
String manualFan = "";

// TIMING
unsigned long lastSensorTime = 0;
unsigned long lastManualTime = 0;
const unsigned long SENSOR_INTERVAL = 5000;  // kirim sensor setiap 5 detik
const unsigned long MANUAL_INTERVAL = 1000;  // cek manual setiap 1 detik (responsif)

const int RELAY_ON  = LOW;   // atau LOW jika aktif-rendah
const int RELAY_OFF = HIGH;  // atau HIGH jika aktif-rendah

void setup() {
  Serial.begin(115200);
  dht.begin();

  pinMode(LED_HIJAU, OUTPUT);
  pinMode(LED_MERAH, OUTPUT);
  pinMode(FAN_RELAY_PIN, OUTPUT);

  // awal: kipas mati, LED hijau menyala (ruangan sejuk)
  setFanState(false);

  connectWiFi();
}

void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    connectWiFi();
  }

  float suhu = dht.readTemperature();
  float hum = dht.readHumidity();

  if (isnan(suhu) || isnan(hum)) {
    Serial.println("Gagal membaca DHT");
    delay(1000);
    return;
  }

  unsigned long currentTime = millis();

  // CECK MANUAL CONTROL SETIAP 1 DETIK (RESPONSIF)
  if (currentTime - lastManualTime >= MANUAL_INTERVAL) {
    lastManualTime = currentTime;
    ambilManual();
  }

  // JALANKAN LOGIKA KONTROL BERDASARKAN MODE
  if (currentMode == "MANUAL") {
    // Mode MANUAL: gunakan perintah dari server
    jalankanManual();
  } else {
    // Mode AUTO: gunakan sensor untuk kontrol otomatis
    otomatisasi(suhu);
  }

  // KIRIM DATA SENSOR SETIAP 5 DETIK
  if (currentTime - lastSensorTime >= SENSOR_INTERVAL) {
    lastSensorTime = currentTime;
    kirimData(suhu, hum);
  }

  delay(100);  // small delay untuk stabil
}

/* ============ SET FAN STATE (TERPUSAT) ============ */
// Fungsi ini mengatur RELAY KIPAS dan LED secara bersamaan
// Prinsip: LED mengikuti status FAN - SINGLE SOURCE OF TRUTHw

void setFanState(bool on) {
  if (on) {
    fanStatus = "ON";
    digitalWrite(FAN_RELAY_PIN, RELAY_ON);   // Kipas 12V MENYALA
    digitalWrite(LED_HIJAU, LOW);            // LED HIJAU MATI
    digitalWrite(LED_MERAH, HIGH);           // LED MERAH HIDUP
    Serial.println("→ FAN ON, LED MERAH");
  } else {
    fanStatus = "OFF";
    digitalWrite(FAN_RELAY_PIN, RELAY_OFF);  // Kipas 12V MATI
    digitalWrite(LED_HIJAU, HIGH);           // LED HIJAU HIDUP
    digitalWrite(LED_MERAH, LOW);            // LED MERAH MATI
    Serial.println("→ FAN OFF, LED HIJAU");
  }
}

/* ============ LOGIKA OTOMATIS ============ */

void otomatisasi(float suhu) {
  if (suhu < 30) {
    setFanState(false);  // Fan OFF
  } else {
    setFanState(true);   // Fan ON
  }

  Serial.print("Mode: OTOMATIS | Suhu: ");
  Serial.print(suhu);
  Serial.print(" | Fan: ");
  Serial.println(fanStatus);
}

/* ============ MANUAL MODE ============ */

void ambilManual() {
  // Ambil mode dan perintah FAN dari server
  // Format API: {"mode":"AUTO|MANUAL","fan":"ON|OFF"}
  // - Jika mode=AUTO: abaikan nilai fan, gunakan sensor
  // - Jika mode=MANUAL: gunakan perintah fan

  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverManual);
    http.setTimeout(3000);

    int code = http.GET();
    if (code == 200) {
      String res = http.getString();
      Serial.print("Respon manual: ");
      Serial.println(res);

      // Parse JSON: {"mode":"AUTO|MANUAL","fan":"ON|OFF|null"}
      DynamicJsonDocument doc(512);
      DeserializationError err = deserializeJson(doc, res);

      if (!err) {
        // Update MODE
        if (doc.containsKey("mode")) {
          const char* modePtr = doc["mode"];
          if (modePtr != nullptr) {
            String newMode = String(modePtr);
            if (newMode != currentMode) {
              currentMode = newMode;
              Serial.print("Mode berubah: ");
              Serial.println(currentMode);
            }
          }
        }

        // Update FAN command (hanya digunakan jika MANUAL mode)
        if (doc.containsKey("fan")) {
          const char* fanPtr = doc["fan"];
          if (fanPtr != nullptr && strlen(fanPtr) > 0) {
            manualFan = String(fanPtr);
            Serial.println("Manual fan command diupdate!");
          }
        }
      } else {
        Serial.print("Gagal parse JSON: ");
        Serial.println(err.c_str());
      }
    }
    http.end();
  }
}

void jalankanManual() {
  // Hanya dijalankan jika currentMode == "MANUAL"
  // Gunakan perintah FAN dari API
  // LED akan OTOMATIS mengikuti di setFanState()

  if (manualFan == "OFF") {
    setFanState(false);
  } else if (manualFan == "ON") {
    setFanState(true);
  }

  Serial.print("Mode: MANUAL | Fan: ");
  Serial.println(fanStatus);
}

/* ============ KIRIM DATA ============ */

void kirimData(float suhu, float hum) {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverSensor);
    http.setTimeout(3000);
    http.addHeader("Content-Type", "application/json");

    String payload = "{";
    payload += "\"temperature\":" + String(suhu, 1) + ",";
    payload += "\"humidity\":" + String(hum, 1) + ",";
    payload += "\"fan_status\":\"" + fanStatus + "\"";
    payload += "}";

    int code = http.POST(payload);
    Serial.print("Kirim data -> ");
    Serial.print(code);
    Serial.print(" | payload: ");
    Serial.println(payload);

    http.end();
  }
}

/* ============ WIFI ============ */

void connectWiFi() {
  WiFi.begin(ssid, password);
  Serial.print("Menghubungkan WiFi");

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("\nWiFi Tersambung!");
  Serial.print("IP: ");
  Serial.println(WiFi.localIP());
}
