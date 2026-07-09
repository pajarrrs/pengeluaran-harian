# Pengeluaran Harian 💰

Aplikasi pencatat pengeluaran harian. Input lewat **WhatsApp** atau **Web Dashboard**.

Built with Laravel 13 + SQLite.

## Fitur

- 📊 Dashboard per-kategori dengan Chart.js
- 💬 Input pengeluaran via WhatsApp (Meta Cloud API)
- 🌐 CRUD pengeluaran & kategori via web
- 🔍 Search & filter per bulan
- 📱 Mobile responsive

## Local Dev

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

Buka `http://localhost:8000`

## WhatsApp Integration

1. Setup WhatsApp Cloud API di Meta Developer Console
2. Tunnel localhost: `ngrok http 8000`
3. Isi `.env`:
```
WHATSAPP_VERIFY_TOKEN=token_lo
WHATSAPP_ACCESS_TOKEN=EA...
WHATSAPP_PHONE_NUMBER_ID=123456789
```
4. Kirim pesan ke nomor WA terdaftar, contoh: `25000 makan siang`

Detail lengkap: [`WA_SETUP.md`](WA_SETUP.md)

## Deploy ke Railway

### 1. Push ke GitHub

```bash
git init
git add .
git commit -m "init"
gh repo create pengeluaran-harian --public --push
```

### 2. Deploy di Railway

1. Buka https://railway.app → **New Project** → **Deploy from GitHub repo**
2. Pilih repo `pengeluaran-harian`

### 3. Set Environment Variables

Railway otomatis pake `.env.example`. Set manual di dashboard:

| Variable | Value |
|----------|-------|
| `APP_KEY` | `base64:...` (generate: `php artisan key:generate --show`) |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | biarin kosong (keisi otomatis dari Railway) |

Opsional (WhatsApp):
| `WHATSAPP_VERIFY_TOKEN` | token bebas |
| `WHATSAPP_ACCESS_TOKEN` | dari Meta |
| `WHATSAPP_PHONE_NUMBER_ID` | dari Meta |

### 4. Setup Volume (biar data gak ilang tiap deploy)

1. Di dashboard Railway project → **Volumes** → **New Volume**
   - **Mount Path:** `/data`
   - **Size:** 1 GB (gratis)
2. Volume otomatis mount ke `/data` — SQLite DB nyimpen di `/data/database.sqlite`
3. Redeploy → data aman walau app restart

### 5. Domain

Railway kasih domain `.railway.app`. Bisa custom domain di Settings → Networking.

## Stack

| Layer | Tech |
|-------|------|
| Backend | Laravel 13 |
| Database | SQLite (Railway Volume) |
| Frontend | Blade + Tailwind CSS + Chart.js |
| WhatsApp | Meta Cloud API (webhook) |
| Deploy | Railway (Nixpacks) |
