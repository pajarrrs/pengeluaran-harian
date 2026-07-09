# Pengeluaran Harian 💰

Aplikasi pencatat pengeluaran harian. Input lewat **WhatsApp** atau **Web Dashboard**.

Built with Laravel 13 + PostgreSQL.

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
# Setup PostgreSQL / SQLite
php artisan migrate --seed
php artisan serve
```

Buka `http://localhost:8000`

## WhatsApp Integration

Detail lengkap: [`WA_SETUP.md`](WA_SETUP.md)

## Deploy ke Render (Free, No CC)

### 1. Push ke GitHub

```bash
git init
git add .
git commit -m "init"
git remote add origin https://github.com/pajarr2/pengeluaran-harian.git
git push -u origin main
```

### 2. Deploy di Render

1. Buka https://dashboard.render.com → **New +** → **Blueprint**
2. Pilih repo `pengeluaran-harian`
3. Render auto-detect `render.yaml` → bikin Web Service + PostgreSQL
4. Klik **Apply**

### 3. Selesai

- Render generate `APP_KEY` otomatis
- PostgreSQL 1GB gratis otomatis kebikin
- Domain: `https://pengeluaran-harian.onrender.com`

**Catatan:** Free tier Render **spin down** setelah 15 menit idle. Request pertama setelah idle lambat ~5-10 detik. Untuk WA webhook, ini artinya mungkin timeout — tapi buat coba-coba gak masalah. Upgrade ke paid ($7/bulan) biar selalu nyala.

### 4. Setup WhatsApp (optional)

Set env vars di Render dashboard:

| Variable | Value |
|----------|-------|
| `WHATSAPP_VERIFY_TOKEN` | token bebas |
| `WHATSAPP_ACCESS_TOKEN` | dari Meta |
| `WHATSAPP_PHONE_NUMBER_ID` | dari Meta |

Webhook URL: `https://pengeluaran-harian.onrender.com/api/whatsapp/webhook`

## Stack

| Layer | Tech |
|-------|------|
| Backend | Laravel 13 |
| Database | PostgreSQL 16 |
| Frontend | Blade + Tailwind CSS + Chart.js |
| WhatsApp | Meta Cloud API (webhook) |
| Deploy | Render (Docker) |
