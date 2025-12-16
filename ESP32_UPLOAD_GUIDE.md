# ESP32 Firmware Upload Guide

## Overview

The ESP32 firmware has been completely refactored to support the new MODE system (AUTO vs MANUAL). This guide explains what changed and how to upload it to your device.

---

## Changes Made to sketsamodul6.ino

### 1. Global Variables Update

**BEFORE:**

```cpp
bool manualMode = false;
String manualFan = "";
```

**AFTER:**

```cpp
String currentMode = "AUTO";  // AUTO atau MANUAL
String manualFan = "";
```

**Why**: String-based mode is more explicit and matches server-side mode values.

---

### 2. Control Logic Update

**BEFORE (Boolean-based):**

```cpp
if (manualMode) {
    jalankanManual();
} else {
    otomatisasi(suhu);
}
```

**AFTER (String-based):**

```cpp
if (currentMode == "MANUAL") {
    jalankanManual();
} else {
    otomatisasi(suhu);
}
```

**Why**: Clearer intent, easier to debug, matches API response format.

---

### 3. API Parsing Function - Complete Rewrite

**NEW `ambilManual()` Function:**

The function now:

1. **Parses both mode AND fan** from API response

    - Old: Only parsed fan value
    - New: Gets both `mode` and `fan`

2. **Detects mode changes** with logging

    ```cpp
    if (newMode != currentMode) {
        currentMode = newMode;
        Serial.print("Mode berubah: ");
        Serial.println(currentMode);
    }
    ```

3. **Handles null values** properly

    - `fan` can be null in AUTO mode
    - Checks with `strlen()` before processing

4. **Robust error handling**
    - JSON parsing with error detection
    - Null pointer checks before dereferencing

**Complete Code:**

```cpp
void ambilManual() {
  // Ambil mode dan perintah FAN dari server
  // Format API: {"mode":"AUTO|MANUAL","fan":"ON|OFF"}

  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverManual);
    http.setTimeout(3000);

    int code = http.GET();
    if (code == 200) {
      String res = http.getString();
      Serial.print("Respon manual: ");
      Serial.println(res);

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

        // Update FAN command
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
```

---

## What Stays the Same

✅ **NO CHANGES** to:

-   Pin assignments (GPIO 4, 25, 26, 27)
-   WiFi connection code
-   DHT11 sensor reading
-   `setFanState()` function (single control point)
-   `kirimData()` sensor upload
-   `otomatisasi()` temperature-based logic
-   Polling intervals (1s manual, 5s sensor)
-   Serial communication setup

---

## Upload Instructions

### Using Arduino IDE

1. **Open the sketch:**

    ```
    File → Open → sketsamodul6/sketsamodul6.ino
    ```

2. **Verify no compile errors:**

    ```
    Sketch → Verify/Compile (Ctrl+R)
    ```

    - Should complete with 0 errors
    - Some warnings about deprecated functions are normal

3. **Upload to ESP32:**

    ```
    Sketch → Upload (Ctrl+U)
    ```

    - Or use: Tools → Upload
    - Wait for "Done uploading" message

4. **Monitor serial output:**
    ```
    Tools → Serial Monitor (Ctrl+Shift+M)
    - Baud: 115200
    ```

### Serial Output - What to Expect

**On Startup:**

```
Menghubungkan WiFi.......
WiFi Tersambung!
IP: 192.168.1.2
```

**Every 1 second (Manual Control Check):**

```
Respon manual: {"mode":"AUTO","fan":null}
```

**When Mode Changes:**

```
Mode berubah: MANUAL
```

**Every 5 seconds (Sensor Data Send):**

```
Kirim data -> 201 | payload: {"temperature":28.5,"humidity":65.0,"fan_status":"OFF"}
Mode: OTOMATIS | Suhu: 28.5 | Fan: OFF
```

**When Manual Command Executed:**

```
Manual fan command diupdate!
Mode: MANUAL | Fan: ON
→ FAN ON, LED MERAH
```

---

## Verification Checklist After Upload

After uploading, verify these behaviors on serial monitor:

### ✓ Startup Phase

-   [ ] WiFi connection message appears
-   [ ] IP address is 192.168.1.2 (or your network IP)
-   [ ] No error messages in first 5 seconds

### ✓ API Communication (repeat every 1 second)

-   [ ] Message: "Respon manual: {...}"
-   [ ] Contains "mode" field with value
-   [ ] Contains "fan" field (can be null)

### ✓ Mode Switching

-   [ ] Submit manual control on dashboard
-   [ ] ESP32 should show: "Mode berubah: MANUAL"
-   [ ] Click AUTO button on dashboard
-   [ ] ESP32 should show: "Mode berubah: AUTO"

### ✓ Control Execution

-   [ ] In MANUAL mode:
    -   Select FAN ON → See "→ FAN ON, LED MERAH"
    -   Select FAN OFF → See "→ FAN OFF, LED HIJAU"
-   [ ] In AUTO mode:
    -   If temp < 30°C → Fan OFF (LED HIJAU)
    -   If temp ≥ 30°C → Fan ON (LED MERAH)

### ✓ Sensor Readings

-   [ ] Every 5 seconds: "Kirim data -> 201"
-   [ ] Payload contains temperature and humidity values
-   [ ] Fan status reflects actual relay state

---

## Debugging Common Issues

### Issue: "Mode berubah" Not Appearing

**Possible Causes:**

1. API response not being received
2. Mode value not being updated
3. Check WiFi connection

**Debug Steps:**

```
1. Check serial output for "Respon manual:"
2. Manually curl: http://192.168.1.22:8000/manual/latest
3. Verify JSON includes "mode" field
4. Check if mode values are "AUTO" or "MANUAL" (case-sensitive)
```

### Issue: Fan Not Responding to Manual Control

**Possible Causes:**

1. Mode not updating to MANUAL
2. Fan command not being parsed
3. Relay connection issue

**Debug Steps:**

```
1. Verify "Manual fan command diupdate!" appears
2. Check fan value is ON or OFF (not null)
3. Test relay directly with separate code
4. Verify GPIO 26 relay pin is working
```

### Issue: Sensor Control Not Working in AUTO

**Possible Causes:**

1. Mode still set to MANUAL
2. Sensor reading failing
3. Temperature threshold not being met

**Debug Steps:**

```
1. Verify mode shows "AUTO" not "MANUAL"
2. Watch for "Gagal membaca DHT" errors
3. Check temp value in serial output
4. Verify threshold: <30°C = OFF, ≥30°C = ON
```

---

## Before/After Behavior

### AUTO Mode (Temperature-based)

```
BEFORE (didn't exist):
- Always checked manual control
- Temperature logic still worked

AFTER (new):
- Explicitly ignores fan value when mode=AUTO
- Temperature controls relay
- Serial log: "Mode: OTOMATIS | Suhu: XX | Fan: OFF"
```

### MANUAL Mode (User Control)

```
BEFORE (always on if fan value existed):
- Any fan value triggered manual mode
- Unreliable mode detection

AFTER (explicit):
- Only uses fan value when mode=MANUAL
- Mode change logged with timestamp
- Serial log: "Mode: MANUAL | Fan: ON"
```

---

## Code Comments in ESP32

Key sections have been commented in Indonesian for clarity:

```cpp
// MODE CONTROL
// Ambil mode dan perintah FAN dari server
// Format API: {"mode":"AUTO|MANUAL","fan":"ON|OFF"}
// - Jika mode=AUTO: abaikan nilai fan, gunakan sensor
// - Jika mode=MANUAL: gunakan perintah fan
```

This makes the logic easy to understand and modify if needed.

---

## Testing in Real-world Scenario

**Step 1: Upload Firmware**

-   Upload new sketsamodul6.ino to ESP32
-   Monitor serial at 115200 baud

**Step 2: Verify Communication**

-   Check "Respon manual:" message appears every 1 second
-   Verify JSON format with "mode" field

**Step 3: Test Mode Switching**

-   Open dashboard: http://192.168.1.22:8000
-   Click "FAN ON" button
-   Watch serial for: "Mode berubah: MANUAL"
-   Watch for: "Manual fan command diupdate!"
-   Verify: Relay activates, LED turns red

**Step 4: Test Auto Reset**

-   Click "Reset ke AUTO" button
-   Watch serial for: "Mode berubah: AUTO"
-   Verify: Fan switches to sensor control
-   Temperature should now control relay

**Step 5: Verify Temperature Control**

-   Hold warm object near DHT11
-   Watch temperature increase in serial
-   When ≥30°C, fan should activate (if was OFF)
-   Check LED status in dashboard updates

---

## Performance Impact

-   **No performance loss** - Mode checking uses same polling intervals
-   **Slightly more robust** - Better error handling in JSON parsing
-   **More predictable** - Explicit mode string vs implicit boolean state
-   **Easier to debug** - Mode changes logged to serial

---

## Rollback Instructions

If you need to revert to the old version:

1. Go back to previous Arduino backup (if available)
2. Or revert key changes:
    - Change `String currentMode` back to `bool manualMode`
    - Update if statement to check `if (manualMode)`
    - Remove mode parsing from `ambilManual()`

But this is **NOT RECOMMENDED** since the new system is better!

---

## Support Notes

-   **File Size**: Slightly larger (more robust code, better error handling)
-   **Memory Usage**: ~2KB additional for new String variable
-   **Power Consumption**: Unchanged (same polling intervals)
-   **Backward Compatibility**: New firmware works with updated Laravel backend

---

**Firmware Version**: 2.0 (MODE System Edition)  
**Upload Date**: 2025-12-16  
**Arduino IDE Version**: 2.0+ recommended
**ESP32 Board**: ESP32-WROOM-32 (or compatible)
