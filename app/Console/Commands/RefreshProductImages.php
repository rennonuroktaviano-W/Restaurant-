<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Support\DemoProductImages;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('products:refresh-images')]
#[Description('Unduh ulang foto demo dari sumber resmi (Pexels) untuk produk yang terpetakan.')]
class RefreshProductImages extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(DemoProductImages $images): int
    {
        $products = Product::all()->filter(fn (Product $product) => $images->sourceUrl($product->sku) !== null);

        if ($products->isEmpty()) {
            $this->info('Tidak ada produk demo dengan sumber foto.');

            return self::SUCCESS;
        }

        foreach ($products as $product) {
            $images->ensure($product, force: true);
            $this->line("Rendering foto {$product->sku} ... {$product->image}");
        }

        $this->info("Selesai. {$products->count()} produk diperbarui.");

        return self::SUCCESS;
    }
}
