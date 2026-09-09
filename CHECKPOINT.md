## Checkpoint — 2026-09-09 (final, PRD closure)

### Summary
PRD P0 requirement implemented dan seluruh suite hijau: **74/74 tests, 230 assertions passing**.
Semua bug yang ditemukan saat audit PRD (Fase 1) sudah diperbaiki dan diuji. Repo sudah di-push ke `origin/main`.

---

### Bug Fixes (PRD Closure)

| File | Fix | PRD ref |
|---|---|---|
| `app/Services/DiscountService.php` | `targetsMatch()` resolve category_id via Product query; cap persentase diskon di subtotal. | FR-PRI-002 |
| `app/Services/DiscountService.php` | **Baru:** `applyCode()` kini memvalidasi periode aktif (`starts_at`/`ends_at`) — promo code kedaluwarsa tidak lagi terpasang. | FR-PRI-002 |
| `app/Services/PaymentService.php` | `settleOnline()` kini idempoten (PAID→return), hanya PENDING yang boleh settle, menolak payment yang `expires_at` sudah lewat, dan menolak order terminal (CANCELLED/COMPLETED). | FR-PAY-003/004/006, BR-009, Tabel 22 |
| `app/Http/Requests/CheckoutRequest.php` | `table_id`/`room_id` wajib `is_active=true` & belum didelete; `payment_method_id` wajib aktif. | FR-LOC-004, FR-PAY-001 |
| `app/Console/Commands/ExpirePayments.php` | **Baru:** command `orders:expire-payments` — expire attempt online yang lewat `expires_at` (dijadwalkan tiap jam di `bootstrap/app.php` via `withSchedule`). | Tabel 22, FR-PAY-006 |
| `bootstrap/app.php` | Registrasi schedule `orders:expire-payments` (hourly). | Tabel 22 |
| `tests/Feature/ExampleTest.php` | Kini `RefreshDatabase` + seed category/product → root `/` hijau (sebelumnya 500 di SQLite kosong). | — |

---

### Test Suite (74 tests / 230 assertions — semua hijau)

| File | Tests | Fokus |
|---|---|---|
| `tests/Feature/AuthFeatureTest.php` | 7 | Login aktif-only, rate-limit, redirect role, logout, reset+audit |
| `tests/Feature/PermissionFeatureTest.php` | 17 | RBAC matrix (AC-07 role dilarang, kategori, inventory, reset password) |
| `tests/Feature/CustomerOrderingFeatureTest.php` | 16 | Menu/cart/checkout, AC-01/02/03/04, snapshot, online payment, **lokasi & metode non-aktif ditolak** |
| `tests/Feature/OrderLifecycleFeatureTest.php` | 11 | Transisi KDS/kasir, AC-08/09, cash confirm, webhook (AC-05), retry |
| `tests/Feature/PricingDiscountInventoryTest.php` | 10 | Urutan hitung, best-single discount, capping negatif, target kategori, **code aktif & kedaluwarsa**, inventory |
| `tests/Feature/ConcurrencyHardeningTest.php` | 7 | **Baru:** webhook idempoten (AC-04), late-webhook ditolak, settle order CANCELLED ditolak, satu settlement aktif (FR-PAY-002), retry attempt baru (FR-PAY-006), duplicate checkout (AC-04), command expire |
| `tests/Feature/ReportExportFeatureTest.php` | 4 | **Baru:** filter laporan, CSV == ringkasan (AC-10), PDF printable, cashier dilarang export |
| `tests/Feature/ExampleTest.php` | 1 | Root `/` render |

---

### Residual (didokumentasikan, bukan bug)
- **Race stok multi-koneksi nyata** butuh MySQL + proses paralel; Varian deterministik sudah di-cover oleh `lockForUpdate` + `WHERE stock >= qty` + `test_ac_03_limited_stock_checkout_cannot_oversell` (SQLite tidak mendukung row-lock sebenarnya).
- **P1 yang tidak dikerjakan** (di luar scope MVP): refund workflow (FR-PAY-005), sound toggle KDS per-device (FR-KDS-005), stock reservations (Tabel 19).
- Net sales di dashboard = gross (belum ada refund, jadi identik).

### Environment
- `php artisan test --compact`: **74 passed** (230 assertions).
- `vendor/bin/pint --format agent`: bersih.
- Command & schedule terverifikasi: `php artisan schedule:list`.
- Remote: `origin/main` → `https://github.com/rennonuroktaviano-W/Restaurant-.git`.