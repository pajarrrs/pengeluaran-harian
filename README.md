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

Detail: [`WA_SETUP.md`](WA_SETUP.md)

## Deploy ke Railway

### 1. Push ke GitHub

```bash
git add .
git commit -m "init"
git push
```

### 2. Deploy

1. Buka https://railway.app → **New Project** → **Deploy from GitHub repo**
2. Pilih repo `pengeluaran-harian`

### 3. Set Environment Variables

Di dashboard Railway project → **Variables**:

| Variable | Value |
|----------|-------|
| `APP_KEY` | `base64:...` (generate: `php artisan key:generate --show`) |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `DB_CONNECTION` | `sqlite` |
| `DB_DATABASE` | `/data/database.sqlite` |

### 4. Bikin Volume (biar data gak ilang)

Dashboard → **Volumes** → **New Volume**
- **Mount Path:** `/data`
- **Size:** 1 GB (free)

### 5. Redeploy

Klik **Deploy** → tunggu build selesai.

### 6. WhatsApp (optional)

Tambah env vars:

| Variable | Value |
|----------|-------|
| `WHATSAPP_VERIFY_TOKEN` | token bebas |
| `WHATSAPP_ACCESS_TOKEN` | dari Meta |
| `WHATSAPP_PHONE_NUMBER_ID` | dari Meta |

Webhook URL: `https://namaproject.railway.app/api/whatsapp/webhook`

## Stack

| Layer | Tech |
|-------|------|
| Backend | Laravel 13 |
| Database | SQLite (Railway Volume) |
| Frontend | Blade + Tailwind CSS + Chart.js |
| WhatsApp | Meta Cloud API (webhook) |
| Deploy | Railway (Nixpacks) |
