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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Optional demo master data. MUST NOT run in production (PRD §19).
 */
class DemoMasterDataSeeder extends Seeder
{
    /**
     * Real food photos (TheMealDB static assets / LoremFlickr keyword photos).
     * Downloaded once into storage; replaceable via admin product form.
     */
    protected array $productImages = [
        'NASGOR' => 'https://www.themealdb.com/images/media/meals/wuyd2h1765655837.jpg',
        'AYAM' => 'https://www.themealdb.com/images/media/meals/020z181619788503.jpg',
        'SATE' => 'https://www.themealdb.com/images/media/meals/dqxtlh1780153831.jpg',
        'ESTEH' => 'https://loremflickr.com/640/480/iced-tea',
        'ESJER' => 'https://loremflickr.com/640/480/orange-juice',
        'KOPISUSU' => 'https://loremflickr.com/640/480/coffee-latte',
        'PISGOR' => 'https://loremflickr.com/640/480/fried-banana',
        'ESKRIM' => 'https://www.themealdb.com/images/media/meals/1xscby1764790242.jpg',
    ];

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

            $product = Product::firstOrCreate(
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

            $this->downloadProductImage($product);
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

    /**
     * Seed a real food photo for the product. Skips when an image already
     * exists; seed stays functional offline (product simply has no photo).
     */
    private function downloadProductImage(Product $product): void
    {
        $url = $this->productImages[$product->sku] ?? null;

        if (! $url || $product->image) {
            return;
        }

        $filename = Str::lower($product->sku).'.jpg';
        $relative = "products/$filename";

        if (Storage::disk('public')->exists($relative)) {
            $product->update(['image' => $relative]);

            return;
        }

        try {
            $response = Http::timeout(10)->get($url);

            if ($response->ok() && str_starts_with((string) $response->header('Content-Type'), 'image/')) {
                Storage::disk('public')->put($relative, $response->body());
                $product->update(['image' => $relative]);
            }
        } catch (\Throwable $e) {
            Log::warning("Gagal mengunduh gambar produk {$product->sku}: {$e->getMessage()}");
        }
    }
}
