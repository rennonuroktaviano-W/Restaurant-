<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Support\DemoProductImages;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemoProductImagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_downloads_and_attaches_photo_when_product_has_none(): void
    {
        $product = Product::factory()->create(['sku' => 'NASGOR', 'image' => null]);

        Http::fake([
            'images.pexels.com/*' => Http::response('foto-nasgor', 200, ['Content-Type' => 'image/jpeg']),
        ]);

        app(DemoProductImages::class)->ensure($product);

        Storage::disk('public')->assertExists('products/nasgor.jpg');
        $this->assertSame('products/nasgor.jpg', $product->fresh()->image);
        Http::assertSentCount(1);
    }

    public function test_skips_download_when_photo_already_attached(): void
    {
        $product = Product::factory()->create(['sku' => 'NASGOR', 'image' => 'products/nasgor.jpg']);

        Http::fake([
            'images.pexels.com/*' => Http::response('foto-lama', 200, ['Content-Type' => 'image/jpeg']),
        ]);

        app(DemoProductImages::class)->ensure($product);

        Http::assertNothingSent();
        Storage::disk('public')->assertMissing('products/nasgor.jpg');
        $this->assertSame('products/nasgor.jpg', $product->fresh()->image);
    }

    public function test_force_refresh_replaces_existing_photo(): void
    {
        Storage::disk('public')->put('products/nasgor.jpg', 'foto-lama');
        $product = Product::factory()->create(['sku' => 'NASGOR', 'image' => 'products/nasgor.jpg']);

        Http::fake([
            'images.pexels.com/*' => Http::response('foto-baru', 200, ['Content-Type' => 'image/jpeg']),
        ]);

        app(DemoProductImages::class)->ensure($product, force: true);

        $this->assertSame('foto-baru', Storage::disk('public')->get('products/nasgor.jpg'));
    }

    public function test_tolerates_download_failure_keeping_product_functional(): void
    {
        $product = Product::factory()->create(['sku' => 'NASGOR', 'image' => null]);

        Http::fake([
            'images.pexels.com/*' => Http::response('error', 503, ['Content-Type' => 'text/html']),
        ]);

        app(DemoProductImages::class)->ensure($product);

        Storage::disk('public')->assertMissing('products/nasgor.jpg');
        $this->assertNull($product->fresh()->image);
    }

    public function test_unknown_sku_is_ignored(): void
    {
        $product = Product::factory()->create(['sku' => 'BARU', 'image' => null]);

        Http::fake();

        app(DemoProductImages::class)->ensure($product);

        Http::assertNothingSent();
        $this->assertNull($product->fresh()->image);
    }
}
