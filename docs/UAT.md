# UAT Checklist — POS Restoran Resort

Dokumen ini adalah daftar skenario UAT sesuai PRD. Setiap skenario dicentang di **Dev** (sudah
otomatis dijamin oleh feature test) lalu ditandatangani oleh **Owner** setelah verifikasi di **UAT**.
Kolom hasil diisi dengan: `PASS` / `FAIL` + inisial & tanggal.

## A. Kiosk & Order (Customer)

| # | Skenario | Expected | Dev | UAT (Owner) |
| --- | --- | --- | --- | --- |
| A1 | Tamu buka menu tanpa login | Menu tampil, produk aktif saja | ✅ test | |
| A2 | Tambah produk + catatan + ubah qty | Keranjang terupdate, subtotal benar | ✅ test | |
| A3 | Checkout dine-in tanpa meja | Ditolak validasi | ✅ test | |
| A4 | Checkout dine-in memakai meja aktif | Order terbuat, status pending | ✅ test | |
| A5 | Scan QR meja/room | Lokasi terisi otomatis, token valid | ✅ test | |
| A6 | Kode diskon valid/kedaluwarsa | Diskon benar / ditolak | ✅ test | |
| A7 | Produk stok habis | Tidak bisa ditambahkan (sold out) | ✅ test | |
| A8 | Order take-away tanpa lokasi | Berhasil tanpa meja/room | ✅ test | |

## B. Dapur / Kitchen Display

| # | Skenario | Expected | Dev | UAT (Owner) |
| --- | --- | --- | --- | --- |
| B1 | Order baru&nbsp;→ muncul di board | Order tampil di kolom Menunggu (realtime) | ✅ test | |
| B2 | Update status&nbsp;→ dimasak/siap | Pindah zona sesuai alur | ✅ test | |
| B3 | Notifikasi suara toggle | Suara hidup saat order baru; per-device | manual (JS) | |
| B4 | Reverb mati&nbsp;→ polling fallback | Board tetap refresh | manual | |

## C. Kasir

| # | Skenario | Expected | Dev | UAT (Owner) |
| --- | --- | --- | --- | --- |
| C1 | Kasir terima order | Status berubah, diizinkan sesui flow | ✅ test | |
| C2 | Bayar tunai (kurang / pas / lebih) | Ditolak jika kurang; kembalian benar | ✅ test | |
| C3 | Bayar online flow mock | Redirect, webhook, status lunas | ✅ test | |
| C4 | Order selesai & struk | Struk tampil & bisa dicetak | ✅ test | |
| C5 | Batalkan order | Stok dikembalikan, audit tercatat | ✅ test | |

## D. Admin & Laporan

| # | Skenario | Expected | Dev | UAT (Owner) |
| --- | --- | --- | --- | --- |
| D1 | CRUD kategori/produk/lokasi | Berfungsi; item nonaktif disembunyikan | ✅ test | |
| D2 | Refund penuh / parsial | Status refunded; net sales terpotong | ✅ test | |
| D3 | Mengekspor laporan CSV/PDF | Konsisten dengan filter | ✅ test | |
| D4 | Audit log aktivitas | Seluruh aksi tercatat | ✅ test | |
| D5 | Reset password via email | Tautan terkirim & dipakai | ✅ test | |
| D6 | Stok rendah | Banner tampil sesuai ambang | ✅ test | |

## E. Keamanan & non-fungsional

| # | Skenario | Expected | Dev | UAT (Owner) |
| --- | --- | --- | --- | --- |
| E1 | RBAC: kasir akses area admin | Ditolak 403 | ✅ test | |
| E2 | Rate limit webhook/tracking | 429 setelah limit | ✅ test | |
| E3 | Akses keyboard & kontras | Fokus terlihat; reduced-motion dihormati | ✅ test-CSS | |
| E4 | Uji ras (race) MySQL paralel | Tidak double-charge / stok negatif | manual-prod | |
| E5 | Scan/pe-t test OWASP level 2 | Tidak ada temuan kritikal | manual | |
| E6 | Drill backup & restore | Restore sukses | manual | |

## Catatan verifikasi Owner

| Item | Hasil | Inisial & Tanggal | Catatan |
| --- | --- | --- | --- |
| Penerimaan kiosk (blok A) | | | |
| Penerimaan dapur (blok B) | | | |
| Penerimaan kasir (blok C) | | | |
| Penerimaan admin/laporan (blok D) | | | |
| Penerimaan keamanan (blok E) | | | |
| **Keputusan akhir** | ☐ Terima&nbsp;&nbsp;☐ Terima dengan catatan&nbsp;&nbsp;☐ Tolak | | |