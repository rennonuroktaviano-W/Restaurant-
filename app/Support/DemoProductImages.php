<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Manages demo product photos: real food photography from the Pexels CDN,
 * downloaded once into local storage. Replaceable anytime via the admin form.
 */
class DemoProductImages
{
    protected array $images = [
        'NASGOR' => 'https://images.pexels.com/photos/37173549/pexels-photo-37173549.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'AYAM' => 'https://images.pexels.com/photos/5695611/pexels-photo-5695611.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'SATE' => 'https://images.pexels.com/photos/37106563/pexels-photo-37106563.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'ESTEH' => 'https://images.pexels.com/photos/18263138/pexels-photo-18263138.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'ESJER' => 'https://images.pexels.com/photos/12896835/pexels-photo-12896835.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'KOPISUSU' => 'https://images.pexels.com/photos/7937431/pexels-photo-7937431.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'PISGOR' => 'https://images.pexels.com/photos/5410407/pexels-photo-5410407.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'ESKRIM' => 'https://images.pexels.com/photos/16015237/pexels-photo-16015237.jpeg?auto=compress&cs=tinysrgb&w=1200',
    ];

    public function sourceUrl(string $sku): ?string
    {
        return $this->images[$sku] ?? null;
    }

    /**
     * Ensure the product has a downloaded photo. Skips harmful calls when the
     * product already has one unless $force is true; never throws so seeding
     * stays functional offline (the product simply keeps no image).
     */
    public function ensure(Product $product, bool $force = false): void
    {
        $url = $this->sourceUrl($product->sku);

        if ($url === null || (! $force && $product->image)) {
            return;
        }

        $relative = 'products/'.Str::lower($product->sku).'.jpg';

        if (! $force && Storage::disk('public')->exists($relative)) {
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
