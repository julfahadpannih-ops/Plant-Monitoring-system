# 🌱 Plant Monitoring System

IoT-based plant monitoring system using ESP32, Blynk Cloud, NPK soil sensor, and a PHP/MySQL dashboard with Telegram commands.

## Project Structure

```
PLANT_MONITORING/
├── README.md
├── firmware/
│   └── plant_monitor.ino               # ESP32 sketch — soil, DHT11, NPK, relay, Blynk, Telegram
├── frontend/
│   ├── login.html                      # Login page
│   ├── css/
│   │   ├── login.css                   # Login page styles
│   │   └── dashboard.css               # Dashboard styles
│   └── js/
│       ├── tailwind.login.config.js    # Tailwind config for login page
│       ├── tailwind.config.js          # Tailwind config for dashboard
│       └── dashboard.js                # Dashboard logic — sensor display, pump control, logs
├── php/
│   ├── Plant.php                       # Main dashboard (session-protected)
│   ├── index.php                       # Entry point
│   ├── login.php                       # Login handler (bcrypt verify)
│   ├── logout.php                      # Session destroy
│   ├── api_get.php                     # Fetch recent records (session-protected)
│   └── api_save.php                    # Save pump log (session-protected)
└── sql/
    └── iot_plant_db.sql                # DB schema — system_records, users
```

## Setup Instructions

### 1. Database
- Import `sql/iot_plant_db.sql` in phpMyAdmin
- Default DB name: `iot_plant_db`

### 2. Web Files
- Place `php/` contents and `frontend/` folder into `htdocs/plant_monitoring/`
- Access via: `http://localhost/plant_monitoring/login.html`

### 3. ESP32 Firmware
- Open `firmware/plant_monitor.ino` in Arduino IDE
- Fill in `YOUR_*` placeholders
- Required libraries: Blynk, DHT, LiquidCrystal_I2C

## Credentials to Configure

**`firmware/plant_monitor.ino`**

| Placeholder | Where to get it |
|---|---|
| `YOUR_BLYNK_AUTH_TOKEN` | Blynk Console → Device Info |
| `YOUR_WIFI_SSID` | Your WiFi name |
| `YOUR_WIFI_PASSWORD` | Your WiFi password |
| `YOUR_TELEGRAM_BOT_TOKEN` | @BotFather on Telegram |
| `YOUR_TELEGRAM_CHAT_ID` | Your Telegram chat ID (numeric) |

**`php/Plant.php`** — also update `const AUTH_TOKEN` in the script section with your Blynk token.

## Features
- Soil moisture auto-pump with cooldown protection
- DHT11 temperature & humidity readings
- NPK sensor (RS485/Modbus) for N, P, K soil nutrients
- LCD 16x2 I2C real-time display
- Telegram commands: `/water_on`, `/water_off`, `/auto`
- AI lockout while auto-watering is active
- Session-protected web dashboard with activity log
