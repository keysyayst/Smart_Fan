# 📋 Panduan Setup Project Smart Fan & LED

Panduan lengkap untuk menjalankan project Smart Fan & LED dari awal hingga bisa beroperasi dengan baik.

---

## 📋 Daftar Isi

1. [Prasyarat](#prasyarat)
2. [Setup Backend (Laravel)](#setup-backend-laravel)
3. [Setup Database](#setup-database)
4. [Setup ESP32](#setup-esp32)
5. [Menjalankan Project](#menjalankan-project)
6. [Troubleshooting](#troubleshooting)

---

## ✅ Prasyarat

Pastikan Anda sudah memiliki:

### Software yang Harus Diinstall:

-   **PHP 8.3+** (atau sesuaikan dengan versi di project)
-   **MySQL/MariaDB** (server database)
-   **Composer** (PHP package manager)
-   **Node.js & npm** (untuk frontend tooling)
-   **Arduino IDE** (untuk upload code ke ESP32)
-   **Git** (opsional, untuk version control)

### Hardware:

-   **ESP32** (microcontroller)
-   **Sensor DHT22** (suhu & kelembaban)
-   **Relay Module** (untuk kontrol kipas)
-   **LED** (indikator status)
-   **Kabel & breadboard**

---

## 🔧 Setup Backend (Laravel)

### Step 1: Clone atau Copy Project

```bash
cd c:\laragon\www
# Jika sudah ada folder, skip step ini
```

### Step 2: Install Dependencies Laravel

Buka terminal di folder project dan jalankan:

```bash
composer install
```

### Step 3: Setup File `.env`

Copy file `.env.example` menjadi `.env`:

```bash
copy .env.example .env
```

**Edit file `.env` dan sesuaikan:**

```env
# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pirdas_mod6          # ⚠️ SESUAIKAN NAMA DATABASE
DB_USERNAME=root                 # ⚠️ SESUAIKAN USERNAME
DB_PASSWORD=                     # ⚠️ SESUAIKAN PASSWORD (jika ada)

# Session Configuration
SESSION_DRIVER=file              # ✅ JANGAN UBAH (sudah diperbaiki)

# APP Configuration
APP_URL=http://192.168.1.22:8000 # ⚠️ SESUAIKAN IP SERVER ANDA
```

**Poin Penting:**

-   `DB_DATABASE`: Nama database yang sudah dibuat di MySQL
-   `DB_USERNAME` & `DB_PASSWORD`: Sesuaikan dengan credential MySQL Anda
-   `APP_URL`: Ganti IP `192.168.1.22` dengan IP komputer/server Anda

### Step 4: Generate APP_KEY

```bash
php artisan key:generate
```

---

## 🗄️ Setup Database

### Step 1: Buat Database di MySQL

Buka MySQL client atau phpMyAdmin:

```sql
CREATE DATABASE pirdas_mod6;
```

**Atau jika Anda menggunakan Laragon:**

-   Buka Laragon
-   Klik menu → MySQL → Open phpMyAdmin
-   Buat database baru dengan nama `pirdas_mod6`

### Step 2: Jalankan Migration

```bash
php artisan migrate
```

✅ Database tables akan otomatis dibuat:

-   `users`
-   `sensor_data` (menyimpan data suhu & kelembaban)
-   `manual_control` (menyimpan perintah kontrol kipas)

---

## 🔌 Setup ESP32

### Step 1: Siapkan Arduino IDE

1. Buka Arduino IDE
2. Preferences → Additional Boards Manager URLs
3. Tambahkan: `https://dl.espressif.com/dl/package_esp32_index.json`
4. Tools → Boards Manager → Cari "esp32" → Install

### Step 2: Edit File `sketsamodul6.ino`

Buka file: `sketsamodul6/sketsamodul6.ino`

**Sesuaikan konfigurasi ini:**

```cpp
// WiFi Configuration
const char* ssid = "YOUR_WIFI_SSID";              // ⚠️ GANTI: Nama WiFi Anda
const char* password = "YOUR_WIFI_PASSWORD";      // ⚠️ GANTI: Password WiFi

// Server Configuration
const char* serverIP = "192.168.1.22";            // ⚠️ GANTI: IP Server Laravel
const int serverPort = 8000;                      // Port Laravel (default 8000)

// Pin Configuration (Sesuaikan dengan wiring Anda)
#define DHTPIN 4                                  // Pin DHT22 data
#define FAN_CONTROL_PIN 5                         // Pin relay kipas
#define LED_PIN 2                                 // Pin LED
```

### Step 3: Upload ke ESP32

1. Pilih Board: **Tools → Board → esp32 → ESP32 Dev Module**
2. Pilih Port: **Tools → Port → COM (pilih port ESP32)**
3. Klik **Upload** (panah ke kanan ➡️)

Tunggu sampai "Leaving... Hard resetting via RTS pin" muncul.

### Step 4: Monitor Serial Output

Tools → Serial Monitor (buka di baud rate 115200)

Anda seharusnya melihat:

```
Connecting to WiFi...
Connected!
IP: 192.168.1.2
Connecting to server...
Server connected!
Kirim data -> 200
```

---

## ▶️ Menjalankan Project

### Step 1: Jalankan Laravel Server

Di terminal, pastikan Anda di folder project, lalu:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

**Output yang diharapkan:**

```
Starting Laravel development server: http://127.0.0.1:8000
[2025-12-17 10:30:45] Processing GET /
[2025-12-17 10:30:45] Completed 200 OK
```

⚠️ **PENTING:** Gunakan `--host=0.0.0.0` agar ESP32 bisa akses dari IP berbeda!

### Step 2: Buka Dashboard di Browser

Akses: **http://192.168.1.22:8000**

Anda seharusnya melihat:

-   📊 Dashboard dengan 4 card: Suhu, Kelembaban, Status Kipas, Status LED
-   🎮 Kontrol manual untuk turn kipas ON/OFF
-   🔄 Auto-refresh setiap 2 detik (tanpa perlu refresh manual)

### Step 3: Monitor ESP32

Lihat serial monitor untuk memastikan ESP32 mengirim data:

-   `Kirim data -> 200` = Berhasil POST ke `/api/sensor`
-   `ERROR` = Ada masalah koneksi/server

---

## 🔧 Troubleshooting

### ❌ Error: "Database connection failed"

**Solusi:**

-   Cek MySQL sudah running
-   Cek credentials di `.env` (DB_HOST, DB_USERNAME, DB_PASSWORD)
-   Cek database sudah dibuat dengan `CREATE DATABASE pirdas_mod6`

### ❌ Error: "Port 8000 already in use"

**Solusi:**

```bash
# Gunakan port berbeda
php artisan serve --host=0.0.0.0 --port=8001
```

### ❌ Dashboard tidak muncul data (Status: -)

**Solusi:**

-   Cek ESP32 serial monitor, pastikan ada `Kirim data -> 200`
-   Cek database `sensor_data` table sudah ada data
-   Cek API route berhasil:
    ```bash
    php artisan route:list | findstr sensor
    ```

### ❌ ESP32 tidak bisa connect ke server

**Solusi:**

-   Cek WiFi SSID dan password di code ESP32
-   Cek IP address server (gunakan `ipconfig` untuk cek IP)
-   Pastikan firewall tidak memblokir port 8000
-   Pastikan server di-run dengan `--host=0.0.0.0`

### ❌ Dashboard tidak auto-refresh

**Solusi:**

-   Check browser console (F12 → Console)
-   Pastikan file `dashboard.blade.php` sudah ter-update dengan script polling
-   Clear browser cache (Ctrl+Shift+Delete)

---

## 📋 Checklist Sebelum Siap Pakai

Pastikan semua ini sudah done:

-   [ ] PHP, MySQL, Composer sudah installed
-   [ ] `.env` file sudah configured (DB & IP)
-   [ ] `php artisan key:generate` sudah dijalankan
-   [ ] Database sudah dibuat di MySQL
-   [ ] `php artisan migrate` sudah dijalankan
-   [ ] `sketsamodul6.ino` sudah disesuaikan (WiFi & IP)
-   [ ] ESP32 sudah di-upload dengan kode terbaru
-   [ ] ESP32 menunjukkan `Kirim data -> 200` di serial monitor
-   [ ] Laravel server running dengan `--host=0.0.0.0 --port=8000`
-   [ ] Dashboard dapat diakses di `http://[IP_SERVER]:8000`
-   [ ] Data suhu & kelembaban muncul di dashboard
-   [ ] Auto-refresh berfungsi (tidak perlu refresh manual)
-   [ ] Kontrol manual kipas berfungsi (status berubah sesuai perintah)

---

## 🚀 Penggunaan

### Mode AUTO (Default)

-   Kipas dikendalikan otomatis berdasarkan sensor suhu dari ESP32
-   Dashboard akan auto-update setiap 2 detik

### Mode MANUAL

1. Di dashboard, pilih **FAN OFF** atau **FAN ON**
2. Klik tombol **Kirim Kontrol**
3. ESP32 akan menerima perintah dan mengubah status kipas

### Reset ke AUTO

-   Klik tombol **Reset ke AUTO (Sensor)** untuk kembali ke mode otomatis

---

## 📞 Support

Jika ada masalah:

1. Check serial monitor ESP32 untuk melihat error messages
2. Check Laravel log: `storage/logs/laravel.log`
3. Check database sudah terisi data dengan benar

---

**Last Updated:** December 17, 2025  
**Project Version:** 1.0  
**Status:** ✅ Ready for Production
