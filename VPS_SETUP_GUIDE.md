# VPS Deployment & Server Setup Guide (Professional)

Panduan ini menjelaskan cara mengatur VPS Anda agar bisa menjalankan proyek **MixuAuth** di balik Global Nginx menggunakan domain sementara `nip.io`.

## 1. Persiapan Server (Prerequisites)
Pastikan VPS Anda (Ubuntu/Debian) sudah terinstall:
- **Docker & Docker Compose**
- **Nginx (Host)**: `sudo apt update && sudo apt install nginx`
- **Git**: `sudo apt install git`

## 2. Struktur Folder di Server
Kami menyarankan meletakkan proyek di `/var/www/` agar rapi.

```text
/var/www/
└── mixuauth/                <-- Folder Proyek Anda (Hasil git clone / upload)
    ├── docker/
    ├── identity-server/
    ├── docker-compose.yml
    └── ...
```

### Langkah Setup Folder:
```bash
sudo mkdir -p /var/www/mixuauth
sudo chown -R $USER:$USER /var/www/mixuauth
cd /var/www/mixuauth
# Jalankan git clone atau upload file Anda ke sini
```

## 3. Konfigurasi Global Nginx (Host)
Buat file konfigurasi baru di host VPS untuk mengatur routing domain.

**File:** `/etc/nginx/sites-available/mixuauth`
*(Ganti `180.247.240.82` dengan IP VPS Anda)*

```nginx
# 1. Aplikasi Utama (Laravel)
server {
    listen 80;
    server_name auth.180.247.240.82.nip.io;

    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}

# 2. Dokumentasi (VitePress)
server {
    listen 80;
    server_name docs.180.247.240.82.nip.io;

    location / {
        proxy_pass http://127.0.0.1:8090;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}

# 3. Database Manager (phpMyAdmin)
server {
    listen 80;
    server_name pma.180.247.240.82.nip.io;

    location / {
        proxy_pass http://127.0.0.1:8081;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### Aktifkan Konfigurasi:
```bash
sudo ln -s /etc/nginx/sites-available/mixuauth /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

## 4. Konfigurasi Docker (.env)
Pastikan file `.env` di dalam `identity-server/` sudah disesuaikan dengan domain baru.

```env
APP_URL=http://auth.180.247.240.82.nip.io
TRUSTED_PROXIES=*
```

## 5. Menjalankan Aplikasi
Di dalam folder `/var/www/mixuauth/`:

```bash
# Build dan jalankan container di background
docker compose up -d --build

# Cek status
docker compose ps
```

## 6. Tips Keamanan (Firewall)
Pastikan hanya port 80 dan 443 yang terbuka untuk publik. Port lain (8080, 8081, 3306) biarkan tertutup atau hanya untuk localhost.

```bash
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

---
*Sekarang Anda bisa mengakses website Anda via browser menggunakan domain nip.io tersebut!*
