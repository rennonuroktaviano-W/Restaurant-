<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Discount;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Room;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Optional demo master data. MUST NOT run in production (PRD §19).
 */
class DemoMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('Demo master data skipped in production.');

            return;
        }

        $categories = [
            ['name' => 'Makanan Utama', 'slug' => 'makanan-utama', 'sort_order' => 1],
            ['name' => 'Minuman', 'slug' => 'minuman', 'sort_order' => 2],
            ['name' => 'Dessert', 'slug' => 'dessert', 'sort_order' => 3],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['slug' => $category['slug']], $category + ['is_active' => true]);
        }

        $products = [
            ['Nasi Goreng', 'NASGOR', 25000, 28000, 'Makanan Utama'],
            ['Ayam Bakar', 'AYAM', 30000, 35000, 'Makanan Utama'],
            ['Sate Ayam', 'SATE', 22000, 25000, 'Makanan Utama'],
            ['Es Teh Manis', 'ESTEH', 5000, 7000, 'Minuman'],
            ['Es Jeruk', 'ESJER', 6000, 8000, 'Minuman'],
            ['Kopi Susu', 'KOPISUSU', 12000, 15000, 'Minuman'],
            ['Pisang Goreng', 'PISGOR', 10000, 12000, 'Dessert'],
            ['Es Krim', 'ESKRIM', 15000, 18000, 'Dessert'],
        ];

        foreach ($products as [$name, $sku, $cost, $price, $categoryName]) {
            $category = Category::where('slug', 'makanan-utama')->first();
            if ($categoryName === 'Minuman') {
                $category = Category::where('slug', 'minuman')->first();
            } elseif ($categoryName === 'Dessert') {
                $category = Category::where('slug', 'dessert')->first();
            }

            Product::firstOrCreate(
                ['sku' => $sku],
                [
                    'category_id' => $category->id,
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'cost_price' => $cost,
                    'sale_price' => $price,
                    'stock_type' => 'unlimited',
                    'stock' => 100,
                    'is_active' => true,
                    'is_available' => true,
                ]
            );
        }

        $area = Area::firstOrCreate(
            ['slug' => 'restoran-utama'],
            [
                'name' => 'Restoran Utama',
                'type' => 'restaurant',
                'description' => 'Area dining utama',
                'is_active' => true,
            ]
        );

        for ($i = 1; $i <= 12; $i++) {
            DiningTable::firstOrCreate(
                ['table_number' => "T$i"],
                ['area_id' => $area->id, 'name' => "Meja $i", 'status' => 'available', 'is_active' => true]
            );
        }

        $villaArea = Area::firstOrCreate(
            ['slug' => 'villa'],
            ['name' => 'Villa', 'type' => 'villa', 'is_active' => true]
        );

        for ($i = 1; $i <= 6; $i++) {
            Room::firstOrCreate(
                ['room_number' => "V$i"],
                ['area_id' => $villaArea->id, 'name' => "Villa $i", 'status' => 'available', 'is_active' => true]
            );
        }

        PaymentMethod::firstOrCreate(['code' => 'cash'], ['name' => 'Tunai', 'type' => 'cash', 'is_active' => true, 'sort_order' => 1]);
        PaymentMethod::firstOrCreate(['code' => 'qris'], ['name' => 'QRIS', 'type' => 'online', 'is_active' => true, 'sort_order' => 2, 'config' => ['provider' => 'mock']]);

        Discount::firstOrCreate(
            ['code' => 'GRATIS10'],
            [
                'name' => 'Diskon 10%',
                'type' => 'percentage',
                'value' => 10,
                'min_amount' => 50000,
                'is_automatic' => false,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addYear(),
                'is_active' => true,
            ]
        );
    }
}
