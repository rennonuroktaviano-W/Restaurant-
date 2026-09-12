<div align="center">

# 🍜 POS Restoran Resort

**Fullstack RESTAURANT & RESORT COMBAT SYSTEM** — self-order kiosk, realtime
Kitchen Display System (KDS), kasir anti-kecolongan, admin **GOD MODE**, dan
promo acak berhadiah tiap minggu. 😎

</div>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3"/>
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 13"/>
  <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL"/>
  <img src="https://img.shields.io/badge/Tailwind-CSS%20v4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS v4"/>
  <img src="https://img.shields.io/badge/Alpine.js-3-8BC0D0?style=for-the-badge&logo=alpinedotjs&logoColor=white" alt="Alpine.js 3"/>
  <img src="https://img.shields.io/badge/Echo%20%26%20Reverb-Realtime-6366F1?style=for-the-badge" alt="Realtime via Laravel Echo & Reverb"/>
  <img src="https://img.shields.io/badge/tests-136%20passing-22c55e?style=for-the-badge" alt="136 tests passing"/>
  <img src="https://img.shields.io/badge/license-MIT-3B82F6?style=for-the-badge" alt="MIT license"/>
</p>

---

## 🎮 TL;DR — Apa Ini?

Sebuah **sistem fullstack** untuk restoran & resort: tamu bisa **self-order**
lewat kiosk / menu publik, dapur melihat order **real-time**, kasir **menerima
& menagih** (termasuk QRIS), dan admin memegang kendali penuh dari katalog
hingga laporan.

| Pertanyaan | Jawaban |
| --- | --- |
| **Fullstack?** | ✅ Iya. Backend Laravel + frontend Blade/Alpine/Tailwind + websocket Reverb dalam satu repo. |
| **Realtime?** | ✅ Order baru, status masak, papan KDS — plus fallback polling kalau Reverb down (anti-lag). |
| **Buat siapa?** | Restoran, kafe, resort — yang ingin ribet-nya ditangani sistem, bukan manusia. |
| **Bahasa antarmuka?** | 🇮🇩 Indonesia. |

```
User: tamu · kasir · dapur · admin · manager
                 │
                 ▼
┌──────────────────────────────────────────────────────┐
│      Laravel 13 (PHP 8.3) — backend brain            │
│      Services · PaymentGateway · RBAC · Queue        │
│      Blade + Alpine.js + Tailwind v4 — frontend      │
└──────────────────────────────────────────────────────┘
                 │
                 ▼
  MySQL ────────► Reverb (websocket) ────────► Queue + Scheduler
```

---

## 🧰 Tech Stack

| Group | Isi |
| --- | --- |
| **Backend** | PHP 8.3, Laravel 13, Spatie Permission (RBAC), bacon/bacon-qr-code, DomPDF, PhpSpreadsheet |
| **Frontend** | Blade (server-rendered), Alpine.js 3, Tailwind CSS v4 via Vite 8, Laravel Echo |
| **Database & Infra** | MySQL (produksi) · SQLite in-memory (test), Laravel Reverb, Queue database, Scheduler |

---

## ⚔️ Fitur — Dikemas Ala RPG

### 🎮 `public-lobby` — Self-Order Kiosk & Menu
- Menu publik + keranjang ala retail drop (langsung kebuka tanpa reload).
- Checkout 3 jalur: **Dine-in** / **Take-away** / **Room service**.
- QR meja & kamar di-*sign* HMAC + TTL (anti QR palsu).
- Kode diskon + locator lokasi QR dinamis.

### ⚡ `kitchen-raid-boss` — Kitchen Display System (KDS)
- Papan order **real-time** (Reverb + polling fallback).
- Alur status: diterima → masak → siap → selesai.
- Notifikasi suara per device.

### 💰 `cash-register-boss` — Kasir
- Terima, selesaikan, batalkan order; **tunai & online**.
- Cetak struk, tracking **shift**, **refund parsial/penuh** + audit trail.

### 🛡️ `god-mode` — Admin
- Katalog produk + foto makanan asli (Pexels) yang bisa di-refresh.
- Lokasi (area / table / room), promo & diskon, stok + peringatan low-stock.
- Laporan **CSV/PDF**, refund, **audit log**, manajemen user & role, pengaturan.

### 🀄 `weekly-event` — Weekly Promo (RNG)
- Tiap minggu promo **acak 10–30%** yang dikunci ke **2 produk target random**.
- Hint muncul di cart biar pemain tahu beli apa. Anti-habis-masa.

### 🔌 `payment-gateway-api` — Pembayaran Online Pluggable
- Driver **`mock`** (default): verifikasi webhook + idempotency + expire order.
- Driver **`qris`**: tampilkan QR merchant + nominal, **konfirmasi kasir** setelah bayar diterima.
- Refund parsial/penuh ikut tercatat di audit log.

---

## 🧠 Arsitektur

```
app/
├── Http/
│   ├── Controllers/      # 32 controllers (Admin · Auth · Cashier · Customer · Kitchen · Payment)
│   └── Requests/         # FormRequest — validasi terpusat
├── Models/               # 23 model Eloquent (Order, Payment, Product, Discount, Refund, …)
├── Services/             # lapisan business logic
│   ├── CheckoutService   #   transaksi order — atomik
│   ├── PaymentService    #   settle / fail / expire + idempotency
│   ├── RefundService     #   refund parsial/penuh + label ulang status
│   ├── InventoryService  #   stok atomik + reversal saat cancel
│   ├── DiscountService   #   kode diskon + cek cakupan produk
│   └── LocationTokenService # QR meja/room — HMAC signed, TTL
├── PaymentGateway/       # interface + driver pluggable (mock, qris)
├── Support/Permissions.php  # matriks role→permission (single source of truth)
└── Console/Commands/     # expire payments · weekly promo · refresh foto produk
```

**Realtime** — event di-broadcast ke channel publik (`order.new`, `order.{id}`,
`cooking`, `kitchen`) tanpa perlu `routes/channels.php`; KDS punya fallback
polling bila Reverb mati.

**Keamanan** — RBAC granular, audit trail, rate limit (webhook & tracking),
reset password mandiri via email, token lokasi bertanda tangan.

---

## 🚀 Setup Lokal (Speedrun)

```bash
composer install
cp .env.example .env
php artisan key:generate
# sesuaikan kredensial DB di .env
php artisan migrate --seed
php artisan storage:link
npm install
npm run build              # atau: npm run dev (hot reload)
php artisan serve          # jalankan Reverb: php artisan reverb:start
```

> **Catatan foto** — `migrate --seed` memanggil `DemoMasterDataSeeder` yang
> **mengunduh foto makanan asli (Pexels)** sekali ke `storage/app/public/products`
> (butuh internet saat seed pertama). Kalau gagal/offline, produk dibuat tanpa
> foto — tinggal isi lewat form admin, atau jalankan `php artisan products:refresh-images`.

### Akun Default (Seeder)

| Role | Email | Password |
| --- | --- | --- |
| 🛡️ Admin | `admin@pos.local` | `ChangeMe-1234!` |
| 🧠 Manager | `manager@pos.local` | `ChangeMe-1234!` |
| 💰 Kasir | `cashier@pos.local` | `ChangeMe-1234!` |
| 🍳 Dapur | `kitchen@pos.local` | `ChangeMe-1234!` |

Password bisa diubah via `SEED_*_PASSWORD` di `.env`.

---

## 🧪 Testing & Quality

```bash
php artisan test --compact      # 136 test · 482 assertion · SQLite in-memory
vendor/bin/pint --format agent  # rapikan gaya PHP otomatis
```

Cakupan test: lifecycle order, pricing + diskon + stok, RBAC/permission,
refund, report export, concurrency hardening, QR lokasi, QRIS, weekly promo,
admin CRUD, rate limit inventory, dan demo foto produk.
Checklist UAT & tanda tangan owner ada di `docs/UAT.md`.

---

## 🏗️ Deploy Produksi

```bash
composer install --no-dev
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan optimize
```

Jalankan scheduler (`php artisan schedule:work`) dan Reverb
(`php artisan reverb:start`, kelola via supervisor). Backup rutin: database
+ `storage/app` (gambar produk).

---

<div align="center">

*Dibangun dengan ☕ dan kesabaran se-level memberdayakan dapur yang nggak mau
ribet.*

**Fullstack Laravel · Realtime · RBAC · Food** — Bon Appétit. 🍽️

</div>