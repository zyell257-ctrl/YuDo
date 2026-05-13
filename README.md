# 🎲 Ludo Tracker

Website mobile-responsive modern untuk pencatatan skor permainan Ludo harian.
Dilengkapi fitur Admin dan Viewer, PWA, realtime skor, dan absensi pemain.

---

## ✨ Fitur Utama

| Fitur | Admin | Viewer |
|---|---|---|
| Login / Logout | ✅ | ❌ (tidak perlu) |
| Kelola absensi pemain | ✅ | 👁️ (lihat saja) |
| Tambah pertandingan | ✅ | ❌ |
| Update skor realtime | ✅ | 👁️ |
| Upload foto harian | ✅ | 👁️ |
| Hapus pertandingan | ✅ | ❌ |
| History pertandingan | ✅ | 👁️ |
| Filter & search history | ✅ | ✅ |
| PWA (install ke homescreen) | ✅ | ✅ |

---

## 🚀 Cara Instalasi

### Persyaratan
- PHP >= 8.2
- Composer
- MySQL >= 8.0
- Node.js (opsional, untuk asset build)
- Web server: Apache / Nginx

---

### Langkah 1 – Clone & Install

```bash
git clone <repo-url> ludo-tracker
cd ludo-tracker

composer install
```

---

### Langkah 2 – Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env`:

```env
APP_NAME="Ludo Tracker"
APP_URL=http://yourdomain.com

DB_DATABASE=ludo_tracker
DB_USERNAME=root
DB_PASSWORD=your_password
```

---

### Langkah 3 – Setup Database

```bash
# Buat database MySQL
mysql -u root -p -e "CREATE DATABASE ludo_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Jalankan migrasi
php artisan migrate

# Isi data awal (admin + 6 pemain contoh)
php artisan db:seed
```

---

### Langkah 4 – Storage Link

```bash
php artisan storage:link
```

Ini diperlukan agar foto yang diupload bisa diakses publik.

---

### Langkah 5 – Jalankan Server

```bash
# Development
php artisan serve

# Akses di: http://localhost:8000
```

---

## 🔐 Login Admin

Setelah seeder dijalankan, gunakan:

```
Username : admin
Password : admin123
```

> Ganti password setelah login pertama melalui database atau tambahkan fitur ganti password.

---

## 📱 Cara Install sebagai PWA (Android)

1. Buka website di Chrome Android
2. Tap menu titik tiga `⋮` di kanan atas
3. Pilih **"Add to Home Screen"** / **"Tambahkan ke Layar Utama"**
4. Konfirmasi → aplikasi muncul di homescreen seperti app Android

---

## 📁 Struktur Folder

```
ludo-tracker/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php        # Login/Logout
│   │   │   ├── AttendanceController.php  # Absensi pemain
│   │   │   ├── MatchController.php       # Pertandingan & skor
│   │   │   ├── HistoryController.php     # Riwayat pertandingan
│   │   │   └── PlayerController.php     # Kelola pemain
│   │   └── Middleware/
│   │       └── AdminMiddleware.php       # Guard route admin
│   └── Models/
│       ├── Admin.php
│       ├── Player.php
│       ├── Attendance.php
│       ├── GameMatch.php
│       ├── MatchScore.php
│       └── DailyPhoto.php
│
├── database/
│   ├── migrations/                       # Struktur tabel MySQL
│   └── seeders/DatabaseSeeder.php        # Data awal
│
├── public/
│   ├── css/app.css                       # Style utama (dark gaming UI)
│   ├── js/app.js                         # JavaScript global
│   ├── manifest.json                     # PWA manifest
│   ├── sw.js                             # Service Worker PWA
│   └── icons/                            # Icon PWA (192px & 512px)
│
├── resources/views/
│   ├── layouts/app.blade.php             # Layout utama + navbar
│   ├── auth/login.blade.php              # Halaman login
│   ├── admin/
│   │   ├── attendance.blade.php          # Absensi (admin)
│   │   ├── matches.blade.php             # Pertandingan (admin)
│   │   └── history.blade.php            # History (admin & viewer)
│   ├── viewer/
│   │   ├── attendance.blade.php          # Absensi (viewer)
│   │   └── matches.blade.php            # Pertandingan (viewer)
│   └── components/
│       └── match-card.blade.php         # Komponen card pertandingan
│
├── routes/web.php                        # Semua route
├── config/auth.php                       # Guard admin
└── bootstrap/app.php                    # Middleware registration
```

---

## 🗄️ Struktur Database

```sql
admins         -- Akun admin
players        -- Data pemain
attendance     -- Absensi harian (auto-reset tiap hari)
matches        -- Data pertandingan
match_scores   -- Skor per pemain per pertandingan
daily_photos   -- Foto harian (1 foto untuk semua pertandingan di hari yang sama)
```

---

## ⚙️ Konfigurasi Produksi

### Nginx Config

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/ludo-tracker/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~* \.(css|js|jpg|png|gif|ico|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### Optimasi Produksi

```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🎨 Desain UI

- **Tema**: Dark Gaming Mobile UI
- **Font**: Poppins (Google Fonts)
- **Warna**: Hitam (#0d0f1a), Biru Gelap (#4361ee), Emas (#f4c430)
- **Layout**: Mobile-first, card list, bottom navbar
- **Navigasi**: Fixed bottom navigation seperti app Android

---

## 📞 Teknologi

- **Backend**: Laravel 11 (PHP 8.2)
- **Frontend**: HTML5 + CSS3 + Bootstrap 5 + Vanilla JS
- **Database**: MySQL 8.0
- **Icons**: Bootstrap Icons
- **PWA**: Service Worker + Web App Manifest
- **Realtime**: AJAX polling ringan

---

## 🔒 Keamanan

- CSRF protection pada semua form
- Session-based authentication
- Middleware guard untuk route admin
- Input validation & sanitization
- Prepared statements via Eloquent ORM
