<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * FR-ADM-005 — default business settings (tax, service charge, business identity, feature flags).
 */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['business.name', 'My Restaurant Resort', 'Nama bisnis', 'general'],
            ['business.address', '', 'Alamat bisnis', 'general'],
            ['business.phone', '', 'Telepon bisnis', 'general'],
            ['receipt.footer', 'Terima kasih atas kunjungan Anda', 'Footer struk', 'receipt'],
            ['pricing.tax_rate', '11', 'Tarif pajak (%)', 'pricing'],
            ['pricing.service_charge_rate', '5', 'Tarif service charge (%)', 'pricing'],
            ['pricing.pay_later', 'false', 'Izinkan bayar di kasir (pay-later)', 'pricing'],
            ['pricing.rounding', '0.01', 'Aturan pembulatan IDR', 'pricing'],
            ['order.timeout_minutes', '15', 'Batas waktu pembayaran online (menit)', 'general'],
            ['inventory.low_stock_threshold', '10', 'Ambang stok rendah', 'inventory'],
            ['feature.kds_enabled', 'true', 'Aktifkan Kitchen Display', 'features'],
            ['feature.soldout_label_product', 'true', 'Tampilkan label sold out pada produk', 'features'],
            ['timezone', 'Asia/Jakarta', 'Zona waktu laporan', 'general'],
            ['currency', 'IDR', 'Mata uang', 'general'],
        ];

        foreach ($settings as [$key, $value, $label, $group]) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'label' => $label, 'group' => $group]
            );
        }
    }
}
