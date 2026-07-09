# Setup WhatsApp Cloud API Integration

## Prasyarat
- Akun Facebook (personal)
- Nomor HP yang bisa nerima SMS/telepon untuk verifikasi
- **Ngrok** (buat tunnel localhost ke publik): `brew install ngrok`

---

## 1. Setup Ngrok (Development)

Webhook Meta WA wajib HTTPS & publik. Pas lagi develop, pake Ngrok:

```bash
ngrok http 8000
```

Copy URL Ngrok (misal: `https://abc123.ngrok.io`).

Di `bootstrap/app.php` udah di-exclude CSRF buat `/api/whatsapp/webhook`.

---

## 2. Buat Meta App + WhatsApp API

1. Buka https://developers.facebook.com
2. Login → **My Apps** → **Create App**
   - Pilih **Other** → **Business**
   - Isi nama app, kasih email, submit
3. Di dashboard app:
   - Cari **Add Product** → **WhatsApp** → **Set Up**
4. Di kiri klik **WhatsApp → Getting Started / API Setup**
   - Nanti ada **Phone Number ID** dan **Access Token** (temporary, expired 24 jam)

> **Penting:** Untuk production, Access Token harus permanent — nanti dituker pake **Permanent Token** via System User Facebook.

5. Di halaman yang sama ada **Send and receive messages → Configure Webhooks**
   - Klik **Edit** → isi:
     - **Callback URL:** `https://ngrok-url-anda.ngrok.io/api/whatsapp/webhook`
     - **Verify Token:** ketik sembarang token bebas, misal: `pengeluaran123`
   - Klik **Verify and Save** — bakal ngetes webhook lo

> Jika verify gagal: pastiin server Laravel nyala, ngrok nyala, dan route `/api/whatsapp/webhook` bisa diakses.

6. Setelah verify sukses, subscribe ke event **messages**:
   - Di halaman webhook config yang sama, ceklis **messages** → **Save**

---

## 3. Setup .env

Di `.env`, isi:

```env
WHATSAPP_VERIFY_TOKEN=pengeluaran123
WHATSAPP_ACCESS_TOKEN=EA...  (Access Token dari step 2.4)
WHATSAPP_PHONE_NUMBER_ID=123456789  (Phone Number ID dari step 2.4)
```

Refresh config:

```bash
php artisan config:clear
```

---

## 4. Hubungkan Nomor WhatsApp

1. Di dashboard Meta App → **WhatsApp → Getting Started**
2. Di bagian **Phone numbers** → **Manage phone number**
3. Tambah nomor (atau pake nomor test yang dikasih Meta)
4. Ikuti verifikasi (SMS / telepon)

---

## 5. Test

Kirim pesan WhatsApp ke nomor yang udah didaftarin:

| Input | Response |
|-------|----------|
| `25000 makan siang` | ✅ Makanan: Rp 25.000, siang |
| `50000` | Berapa kategorinya? (bot reply) |
| `belanja` | Berapa jumlahnya? (bot reply) |
| `15000 trans` | ✅ Transport: Rp 15.000 |

Cek juga di dashboard web: `http://localhost:8000`

---

## Troubleshooting

**Webhook verify gagal (403)**
- Pastiin `WHATSAPP_VERIFY_TOKEN` di `.env` sama persis sama yang lo isi di Meta Developer Console
- `bootstrap/app.php` — route `/api/whatsapp/webhook` udah di-exclude dari CSRF verify
- `php artisan config:clear` setelah ganti `.env`
- Cek log: `storage/logs/laravel.log`

**Pesan masuk tapi gak ada reply**
- `WHATSAPP_ACCESS_TOKEN` expired? Token temporary cuma 24 jam. Generate ulang di dashboard.
- `WHATSAPP_PHONE_NUMBER_ID` salah? Cek di dashboard.
- Cek log: `storage/logs/laravel.log`

**Ngrok kadang ganti URL tiap restart**
- Update Callback URL di Meta Developer Console tiap restart ngrok
- Atau upgrade Ngrok ke paid plan biar URL tetap

---

## Production

Buat production, lo perlu:

1. **Permanent Access Token:**
   - Dashboard Meta → **Settings → Users → System Users**
   - Add System User → kasih nama → role **Admin**
   - Pilih app → generate token
   - Pake token itu di `.env` (gak expired)

2. **Deploy ke server publik** (bisa pakai Laravel Forge, VPS, Railway, dll)
   - Ganti Callback URL ke domain production lo

3. **SSL** wajib (sudah include kalo pake Forge/Railway/etc)
