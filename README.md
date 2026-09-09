# POS Restoran Resort

Sistem Point of Sale terintegrasi untuk restoran & resort: kiosk mandiri, Kitchen Display System (KDS),
kasir, admin manajemen, pembayaran online, dan laporan. Dibangun dengan Laravel 13, Alpine.js, Tailwind CSS,
Reverb (realtime), dan Spatie Permission.

## Fitur Utama

- **Kiosk / Customer**: menu publik, keranjang, checkout dengan tipe dine-in / take-away / room service,
  diskon kode, lokasi via QR dinamis bertanda tangan (HMAC).
- **Kitchen Display (KDS)**: papan order realtime (Reverb + polling fallback), update status, notifikasi suara per perangkat.
- **Kasir**: terima/selesaikan/batalkan order, tunai & online, cetak struk, shift.
- **Admin**: katalog, lokasi (area/table/room), promo, stok & peringatan stok rendah, laporan (CSV/PDF),
  refund, audit log, pengguna & role, pengaturan.
- **Pembayaran online**: gateway pluggable (mock default, verifikasi webhook + idempotency + kedaluwarsa order),
  refund parsial/penuh.
- **Keamanan**: RBAC granular, audit trail, rate limit, reset password mandiri via email, token lokasi bertanda tangan.

## Stack

- PHP 8.3, Laravel 13
- MySQL (produksi) / SQLite in-memory (test)
- Tailwind CSS v4 (Vite), Alpine.js, Laravel Echo
- Laravel Reverb, Spatie Laravel Permission
- bacon/bacon-qr-code (QR dinamis)

## Persiapan Local

```bash
composer install
cp .env.example .env
php artisan key:generate
# sesuaikan kredensial DB di .env
php artisan migrate --seed
php artisan storage:link
npm install
npm run build        # atau: npm run dev (mode develop)
php artisan serve
```

> Catatan: `php artisan migrate --seed` memanggil `DemoMasterDataSeeder` yang mengunduh foto
> makanan asli (Pexels — food photography profesional) **sekali** ke `storage/app/public/products` —
> butuh internet saat seed perdana. Jika gagal/offline, produk dibuat tanpa foto dan bisa diisi lewat form produk
> admin. Untuk mengunduh ulang foto (mis. DB lama atau ganti kandidat foto):
> `php artisan products:refresh-images`.

Konten file `.env` yang penting:

| Key | Nilai contoh | Keterangan |
| --- | --- | --- |
| `APP_NAME` | `POS Restoran Resort` | Nama aplikasi |
| `BROADCAST_CONNECTION` | `reverb` | Aktifkan realtime (Reverb) |
| `REVERB_*` | - | Kredensial Reverb |
| `QUEUE_CONNECTION` | `database` | Antrian notifikasi email, dll |
| `MAIL_*` | SMTP | untuk reset password via email |

> Catatan: `npm run dev` tidak diperlukan untuk menjalankan test; aset dibangun dengan `npm run build`.

## Akun Default (Seeder)

| Role | Email | Password default |
| --- | --- | --- |
| Admin | `admin@pos.local` | `ChangeMe-1234!` |
| Manager | `manager@pos.local` | `ChangeMe-1234!` |
| Kasir | `cashier@pos.local` | `ChangeMe-1234!` |
| Dapur | `kitchen@pos.local` | `ChangeMe-1234!` |

Password dapat diubah via `SEED_*_PASSWORD` di `.env`.

## Menjalankan Test & Lint

```bash
php artisan test --compact          # seluruh suite (feature test, in-memory SQLite)
vendor/bin/pint --format agent      # perbaiki gaya PHP otomatis
```

## Arsitektur ringkas

- **Services** (`app/Services`): `CheckoutService` (transaksi order), `PaymentService` (settle/fail/expire,
  idempotensi), `RefundService` (refund parsial/penuh + label ulang status), `InventoryService`
  (pengurangan stok atomik + reversal saat cancel), `DiscountService`, `SettingsService`, `LocationTokenService`
  (token HMAC ber-TTL utk QR meja/room).
- **Payment Gateway** (`app/PaymentGateway`): driver berbasis `PaymentGateway` interface; `mock` bawaan
  dengan verifikasi tanda tangan webhook.
- **RBAC** (`app/Support/Permissions.php`): matriks role→permission, seeder di
  `database/seeders/RolePermissionSeeder.php`. Middleware: `active`, `role`, `permission`.
- **Realtime**: event broadcast ke channel publik (`order.new`, `order.{id}`, `cooking`, `kitchen`);
  tidak memerlukan `routes/channels.php`. Board KDS punya fallback polling bila Reverb mati.
- **Scheduling**: `orders:expire-payments` berjalan tiap jam (lihat `bootstrap/app.php`).

## Operasional

- **Scheduler**: jalankan `php artisan schedule:work` (atau cron `* * * * * php artisan schedule:run`).
- **Broadcast**: jalankan `php artisan reverb:start` (atau kelola via supervisor) bila `BROADCAST_CONNECTION=reverb`.
- **Backup**: backup database & `storage/app` (gambar produk) secara berkala.
- **Deploy produksi**: `composer install --no-dev`, `npm ci && npm run build`, `php artisan migrate --force`,
  `php artisan config:cache`, `php artisan optimize`.
- Checklist UAT & tanda tangan owner ada di `docs/UAT.md`.

## Keamanan & Kepatuhan

- Staff diwajibkan mereset password default; reset password mandiri via email tersedia publik.
- Endpoint webhook & tracking diberi rate limit (NFR-SEC).
- Alur pembayaran & refund dicatat di audit log.
- Aspek yang memerlukan tanda tangan/pemeriksaan manual: UAT owner, scan OWASP / pentest resmi,
  uji ras perlombaan (race) di MySQL produksi, dan drill backup/restore.