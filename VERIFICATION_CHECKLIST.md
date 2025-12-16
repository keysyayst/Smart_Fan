# Smart Fan IoT - Final Verification Checklist

Complete this checklist to ensure the entire system is working correctly.

---

## ✅ PART 1: Laravel Backend Verification

### 1.1 Database Migrations

-   [ ] Check database has `mode` column in `manual_control` table
    ```bash
    # Run this command:
    php artisan tinker
    # Then: DB::table('manual_control')->first()
    ```
-   [ ] Verify `mode` defaults to 'AUTO'
-   [ ] Verify `fan` column accepts NULL values

### 1.2 Routes

-   [ ] Route `GET /` exists → DashboardController@index
-   [ ] Route `POST /manual` exists → DashboardController@manual
-   [ ] Route `POST /auto` exists → DashboardController@auto (NEW)
-   [ ] Route `GET /manual/latest` exists → JSON response
    ```bash
    # Run: php artisan route:list
    ```

### 1.3 Controllers

-   [ ] DashboardController has `index()` method
-   [ ] DashboardController has `manual(Request $request)` method
-   [ ] DashboardController has `auto()` method (NEW)
-   [ ] Methods create ManualControl entries with mode

### 1.4 Models

-   [ ] ManualControl model has `['mode', 'fan']` in fillable
-   [ ] Model has timestamps enabled (default in Laravel)
-   [ ] No compilation errors in model file

### 1.5 Dashboard UI

-   [ ] Dashboard loads without errors
-   [ ] Temperature and humidity display visible
-   [ ] Fan status display visible
-   [ ] LED status display visible
-   [ ] Manual control form is present
-   [ ] AUTO reset button is present (NEW)

---

## ✅ PART 2: API Endpoint Verification

### 2.1 Test /manual/latest Endpoint

**In browser or terminal:**

```bash
curl http://192.168.1.22:8000/manual/latest
```

Should return:

```json
{ "mode": "AUTO", "fan": null }
```

-   [ ] Response includes `mode` field
-   [ ] Response includes `fan` field
-   [ ] Values are strings or null
-   [ ] No HTML error messages
-   [ ] Status code is 200

### 2.2 Test POST /manual Endpoint

**On Dashboard:**

1. [ ] Select "FAN ON" from dropdown
2. [ ] Click "Kirim Kontrol" button
3. [ ] Check database for new entry:
    ```bash
    # Database should have new entry:
    # id: (new), mode: "MANUAL", fan: "ON", created_at: (now)
    ```

Then test with "FAN OFF": 4. [ ] Select "FAN OFF" 5. [ ] Click button 6. [ ] Check new entry has fan: "OFF"

### 2.3 Test POST /auto Endpoint

**On Dashboard:**

1. [ ] Click "Reset ke AUTO (Sensor)" button
2. [ ] Check database for new entry:
    ```bash
    # Should have new entry:
    # id: (new), mode: "AUTO", fan: null, created_at: (now)
    ```
3. [ ] /manual/latest now returns mode: "AUTO"

---

## ✅ PART 3: ESP32 Firmware Verification

### 3.1 Serial Monitor Check

1. [ ] Connect ESP32 via USB
2. [ ] Open Arduino IDE Serial Monitor (115200 baud)
3. [ ] Reset ESP32 (press RESET button)
4. [ ] Wait for startup messages

Should see:

```
Menghubungkan WiFi.......
WiFi Tersambung!
IP: 192.168.1.2
```

-   [ ] WiFi connection successful
-   [ ] IP address assigned (may be different if network changed)
-   [ ] No error messages in first 10 seconds

### 3.2 API Polling Check

Watch serial monitor for 10 seconds:

-   [ ] Should see "Respon manual:" message about 10 times (every 1s)
-   [ ] JSON response visible: `{"mode":"...","fan":"..."}`
-   [ ] Mode value is either "AUTO" or "MANUAL"
-   [ ] Fan value is either "ON", "OFF", or null
-   [ ] No timeout errors

### 3.3 Mode Detection Check

1. [ ] Note current mode in serial output
2. [ ] Go to dashboard
3. [ ] Submit manual control form
4. [ ] Watch serial monitor
5. [ ] Should see: "Mode berubah: MANUAL"
6. [ ] Click AUTO button
7. [ ] Should see: "Mode berubah: AUTO"

-   [ ] Mode changes are logged
-   [ ] Mode changes happen within 2 seconds
-   [ ] No error messages during transitions

### 3.4 Control Execution Check

**Test MANUAL Control:**

1. [ ] Dashboard form: Select "FAN ON"
2. [ ] Watch serial for: "Manual fan command diupdate!"
3. [ ] Should see: "Mode: MANUAL | Fan: ON"
4. [ ] Should see: "→ FAN ON, LED MERAH"
5. [ ] Check relay: Should be ACTIVE (12V fan running)
6. [ ] Check LED: Should be RED

Repeat with "FAN OFF": 7. [ ] Dashboard form: Select "FAN OFF" 8. [ ] Should see: "→ FAN OFF, LED HIJAU" 9. [ ] Check relay: Should be INACTIVE 10. [ ] Check LED: Should be GREEN

**Test AUTO Control:**

1. [ ] Click AUTO button
2. [ ] Watch temperature in serial
3. [ ] If temp < 30°C: Fan should be OFF, LED GREEN
4. [ ] If temp ≥ 30°C: Fan should be ON, LED RED
5. [ ] Heat DHT11 (hold warm object near it)
6. [ ] Temperature should increase
7. [ ] When ≥ 30°C, fan should activate automatically

---

## ✅ PART 4: Integration Verification

### 4.1 Round-Trip Test

1. [ ] Dashboard open and showing latest sensor data
2. [ ] Submit manual control from dashboard
3. [ ] ESP32 receives command within 2 seconds (check serial)
4. [ ] Relay/LED responds
5. [ ] Dashboard doesn't error
6. [ ] Check database for new entry

### 4.2 Mode Persistence Test

1. [ ] Set to MANUAL mode with FAN ON
2. [ ] Refresh dashboard (F5)
3. [ ] Should still show MANUAL (last mode in DB)
4. [ ] ESP32 should execute MANUAL command
5. [ ] Change to AUTO mode
6. [ ] Refresh dashboard
7. [ ] ESP32 should execute AUTO mode

### 4.3 LED Synchronization Test

1. [ ] Activate fan via relay (any control)
2. [ ] Check LED is RED
3. [ ] Deactivate fan
4. [ ] Check LED is GREEN
5. [ ] In no scenario should LED be different color than fan state

---

## ✅ PART 5: Error Scenarios

### 5.1 Network Disconnection

1. [ ] Disconnect WiFi from ESP32 (power off router)
2. [ ] Serial should show no errors after 10 seconds
3. [ ] When WiFi restores, ESP8266 reconnects automatically
4. [ ] Control commands should work again

### 5.2 Invalid API Response

1. [ ] Dashboard still loads without errors
2. [ ] If API returns malformed JSON:
    - [ ] Should see "Gagal parse JSON" in serial
    - [ ] Mode/fan should NOT change
    - [ ] ESP32 should retry next second

### 5.3 Database Entry Creation

1. [ ] Submit multiple controls quickly
2. [ ] Database should have all entries (no lost data)
3. [ ] Timestamps should be accurate
4. [ ] Mode values should be correct

---

## ✅ PART 6: Performance & Stability

### 6.1 Responsiveness

-   [ ] Manual control response time: < 2 seconds
-   [ ] Dashboard updates: Immediate
-   [ ] Fan activation: Within 1 second of command
-   [ ] No noticeable lag in any operation

### 6.2 Reliability (Test for 5 minutes)

1. [ ] Submit 10 manual control commands

    - [ ] All should work
    - [ ] No commands lost
    - [ ] Database has all 10 entries

2. [ ] Toggle between AUTO and MANUAL 5 times

    - [ ] All transitions smooth
    - [ ] Modes logged correctly
    - [ ] Controls execute properly after each switch

3. [ ] Monitor serial output
    - [ ] No corruption in output
    - [ ] No repeated error messages
    - [ ] Polling intervals consistent

### 6.3 Serial Monitor Health

-   [ ] No garbage characters in output
-   [ ] Timestamps make sense
-   [ ] No memory error messages
-   [ ] No watchdog resets (no random restarts)

---

## ✅ PART 7: Documentation Verification

### 7.1 Documentation Files Exist

-   [ ] MODE_SYSTEM_DOCUMENTATION.md exists in project root
-   [ ] QUICK_REFERENCE.md exists in project root
-   [ ] ESP32_UPLOAD_GUIDE.md exists in project root
-   [ ] IMPLEMENTATION_COMPLETE.md exists in project root

### 7.2 Documentation Readable

-   [ ] All files open without errors
-   [ ] Code snippets are formatted correctly
-   [ ] Instructions are clear
-   [ ] Diagrams (if any) are visible

---

## ✅ PART 8: Code Quality Check

### 8.1 No Compilation Errors

-   [ ] No PHP errors in console
-   [ ] No JavaScript errors in browser (F12)
-   [ ] Laravel artisan commands run without errors
-   [ ] Database migrations complete successfully

### 8.2 Code Comments

-   [ ] Arduino code has comments explaining MODE logic
-   [ ] Key functions documented
-   [ ] Complex logic explained

### 8.3 Consistent Naming

-   [ ] Mode values: "AUTO" or "MANUAL" (uppercase)
-   [ ] Fan values: "ON" or "OFF" (uppercase) or null
-   [ ] Variable names: camelCase (JavaScript) or snake_case (PHP)

---

## ✅ PART 9: Security Check

-   [ ] No credentials in code (WiFi password only in ESP32 sketch)
-   [ ] No sensitive data logged to public locations
-   [ ] Forms have CSRF protection (@csrf in Blade)
-   [ ] Database queries use parameterized queries (Eloquent)
-   [ ] Input validation on form submissions

---

## ✅ PART 10: Academic Readiness

-   [ ] Code is well-structured and understandable
-   [ ] System demonstrates IoT concepts
-   [ ] Documentation is comprehensive
-   [ ] All features are working as intended
-   [ ] No obvious bugs or issues
-   [ ] Suitable for classroom presentation
-   [ ] Code comments explain the "why" not just "what"

---

## 📊 Verification Summary

Complete all checklists above. Count checkmarks:

```
Part 1 (Backend):      ___ / 14 checks
Part 2 (API):          ___ / 12 checks
Part 3 (ESP32):        ___ / 16 checks
Part 4 (Integration):  ___ / 12 checks
Part 5 (Errors):       ___ / 9 checks
Part 6 (Performance):  ___ / 15 checks
Part 7 (Docs):         ___ / 8 checks
Part 8 (Quality):      ___ / 9 checks
Part 9 (Security):     ___ / 5 checks
Part 10 (Academic):    ___ / 7 checks

TOTAL:                 ___ / 107 checks
```

### Success Criteria

-   ✅ **All checks passed**: System is fully functional
-   ⚠️ **90%+ checks passed**: Minor issues, still usable
-   ❌ **< 90% checks passed**: Issues need addressing

---

## 🔧 Troubleshooting Quick Links

If you find a failed check, consult:

-   **API issues**: See `QUICK_REFERENCE.md` - API Endpoints section
-   **ESP32 problems**: See `ESP32_UPLOAD_GUIDE.md` - Debugging section
-   **Dashboard issues**: See `QUICK_REFERENCE.md` - Troubleshooting section
-   **Database problems**: Check `MODE_SYSTEM_DOCUMENTATION.md` - Database Schema
-   **Mode logic**: See `IMPLEMENTATION_COMPLETE.md` - Operational Flow

---

## 📝 Notes

Use this space to record any deviations or special notes:

```
Date: ______________
Tester: _____________

Notes:
_________________________________________________
_________________________________________________
_________________________________________________

Issues Found:
_________________________________________________
_________________________________________________

Fixes Applied:
_________________________________________________
_________________________________________________

Status: [ ] PASS  [ ] CONDITIONAL PASS  [ ] FAIL
```

---

**Checklist Version**: 1.0  
**Last Updated**: 2025-12-16  
**Ready for Production**: When all checks pass ✅
