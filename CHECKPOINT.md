## Checkpoint — 2026-09-09 (final, PRD closure 100%)

### Summary
Seluruh PRD P0/P1 tercapai, termasuk butir **P1 Item_(n) refund (FR-PAY-005)**, **P1 Project MSGE reset password**, **P1 Product QR** (kiosk lokasi), dan **NFR-SEC rate limit** + **FR-KDS-005 sound toggle** +
inventory notification. Dokumentasi final (README, UAT checklist) telah dibuat.
Suite hijau penuh: **102/102 tests, 316 assertions passing**. Repo di-push ke `origin/main`.

---

### Fase A — Refund Workflow (FR-PAY-005)

| File | Status |
|---|---|
| `database/migrations/..._create_refunds_table.php` | refunds: order/payment/creator FK, amount, reason_code/reason, status, provider_reference |
| `app/Models/Refund.php` + factory | STATUS_SUCCEEDED/FAILED, relations order/payment/creator |
| `app/Services/RefundService.php` | simplex guard (payment-owner, PAID, amount>0, reason wajib, kumulatif ≤ paid), transaksi, audit log `refund`, label ulang status payment & order (partial→PARTIALLY_REFUNDED, penuh→REFUNDED) |
| `app/Services/PaymentService.php` | `assertSettlable()` menolak order REFUNDED/PARTIALLY_REFUNDED |
| `app/Http/Controllers/Admin/RefundController.php` + routes (index/create/store) | guarded `payment.refund` (admin+manager) |
| `resources/views/admin/refunds/index.blade.php` + `create.blade.php`, tombol Refund di order-show kasir | UI refund |
| `app/Http/Controllers/Admin/ReportController.php` + views laporan | `refundTotal` & `netSales = gross − refund` di laporan & PDF |
| `tests/Feature/RefundFeatureTest.php` | 8 test |

### Fase B — Reset Password Mandiri (via email)

- `App\Notifications\ResetPasswordNotification` (MailMessage, queueable).
- `Auth\ForgotPasswordController` (enumeration-safe, audit `password_reset_request`), `Auth\ResetPasswordController`
  (broker reset, audit `reset_password`, token invalid → ValidationException).
- Routes guest: `password.forgot|password.forgot.store (throttle:5,60)|password.reset.form|password.reset.store`;
  views `auth/forgot-password.blade.php` + `auth/reset-password.blade.php`; link di halaman login.
- `tests/Feature/PasswordResetFeatureTest.php` — 6 test.

### Fase C — Dynamic Signed Location QR (P1 Product QR)

- Dep `bacon/bacon-qr-code` (v3.1.1).
- `app/Services/LocationTokenService.php`: token `base64(json{type,id,exp}).'.'.hash_hmac('sha256')`, TTL 12 jam,
  verifikasi dengan `hash_equals` + exp; TYPE_TABLE / TYPE_ROOM.
- `app/Http/Controllers/Admin/LocationQrController.php` (table/room) + pragar SVG data-URI;
  rute `admin.tables.qr|admin.rooms.qr` guarded `location.manage`; tombol QR di index dining-tables & rooms.
- `CheckoutRequest::prepareForValidation()` resolve token dari input `table_token` **atau** `session('location.token')`.
- `MenuController::index` menyimpan/menghapus `location.token` session.
- `tests/Feature/LocationQrFeatureTest.php` — 10 test.

### Fase D — NFR-SEC & KDS / Inventory

- Rate limit: `tracking.show` → `throttle:120,1`; `webhook.payment.mock` → `throttle:60,1`.
- KDS sound toggle per-device (localStorage `kds.sound`), beep 880Hz + reload pada `.OrderCreated`/`.OrderStatusUpdated`,
  sinkron `aria-pressed`.
- Low-stock alert: setting `inventory.low_stock_threshold` (default 10), `InventoryController` menghitung
  `lowStockProducts/lowStockCount/lowStockThreshold`, banner di index inventori.
- `tests/Feature/RateLimitInventoryFeatureTest.php` — 4 test (429 webhook & tracking, low-stock 2 skenario).

### Fase E — Aksesibilitas & Dokumentasi

- WCAG: focus-visible global (CSS), `prefers-reduced-motion` dihormati, `aria-label` tombol tambah produk kiosk,
  `aria-live` zona order baru + label section di kitchen board. Aset dibangun ulang.
- `README.md`: setup, seeder, test/pint, arsitektur, operasional, kepatuhan.
- `docs/UAT.md`: tabel skenario per blok (kiosk, dapur, kasir, admin, keamanan) + kolom sign-off owner.

---

### Test Suite (102 tests / 316 assertions — semua hijau)

| File | Tests | Fokus |
|---|---|---|
| `tests/Feature/AuthFeatureTest.php` | 7 | Login aktif-only, rate-limit, redirect role, logout |
| `tests/Feature/PasswordResetFeatureTest.php` | 6 | **Fase B** |
| `tests/Feature/PermissionFeatureTest.php` | 17 | RBAC matrix |
| `tests/Feature/CustomerOrderingFeatureTest.php` | 16 | Menu/cart/checkout, lokasi & metode non-aktif ditolak |
| `tests/Feature/OrderLifecycleFeatureTest.php` | 11 | Transisi, cash, webhook, retry |
| `tests/Feature/PricingDiscountInventoryTest.php` | 10 | Diskon, capping, inventory |
| `tests/Feature/ConcurrencyHardeningTest.php` | 7 | Idempotensi, expire, duplicate checkout |
| `tests/Feature/RefundFeatureTest.php` | 8 | **Fase A** |
| `tests/Feature/LocationQrFeatureTest.php` | 10 | **Fase C** |
| `tests/Feature/RateLimitInventoryFeatureTest.php` | 4 | **Fase D** |
| `tests/Feature/ReportExportFeatureTest.php` | 4 | Laporan CSV/PDF |
| `tests/Feature/ExampleTest.php` | 1 | Root `/` |

---

### Residual (didokumentasikan, butuh aksi manusia — bukan bug)
- **Owner sign-off UAT** di `docs/UAT.md` (item A–E).
- **Race stok multi-koneksi nyata**: uji di MySQL + proses paralel (varian deterministik sudah di-cover).
- **OWASP ASVS Level 2 pentest** resmi.
- **Browser E2E infra** (Playwright/Cypress) — deliberately ditangguhkan, di-cover feature test.
- **SMTP produksi** & verifikasi pengiriman email reset password.
- **Drill backup & restore** berkala di produksi.

### Environment
- `php artisan test --compact`: **102 passed** (316 assertions).
- `vendor/bin/pint --format agent`: bersih.
- Asset: `npm run build` sukses.
- Remote: `origin/main` → `https://github.com/rennonuroktaviano-W/Restaurant-.git`.