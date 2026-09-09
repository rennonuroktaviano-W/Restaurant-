<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Product;
use App\Services\InventoryService;
use App\Services\OrderStatusService;
use App\Services\PricingService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class PricingDiscountInventoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedStaffRolesAndSettings();
    }

    private function cartLines(array $items): Collection
    {
        return collect($items)->map(fn ($row) => [
            'product_id' => $row[0],
            'product_name' => "Produk {$row[0]}",
            'price' => (float) $row[1],
            'quantity' => $row[2],
        ]);
    }

    public function test_fr_pri_004_tax_and_service_charge_are_applied_after_discount(): void
    {
        app(SettingsService::class)->set('pricing.tax_rate', 11);
        app(SettingsService::class)->set('pricing.service_charge_rate', 5);

        $lines = $this->cartLines([[1, 100000.0, 1]]);

        $pricing = app(PricingService::class)->calculate($lines, 100000.0);

        $this->assertSame(100000.0, $pricing['subtotal']);
        $this->assertSame(0.0, $pricing['discount_amount']);
        $this->assertSame(11000.0, $pricing['tax_amount']);
        $this->assertSame(5000.0, $pricing['service_charge_amount']);
        $this->assertSame(116000.0, $pricing['grand_total']);
    }

    public function test_fr_pri_003_only_one_best_automatic_discount_is_applied(): void
    {
        Discount::create([
            'name' => 'Auto 5%',
            'code' => null,
            'type' => 'percentage',
            'value' => 5,
            'min_amount' => 0,
            'is_automatic' => true,
            'is_active' => true,
        ]);

        Discount::create([
            'name' => 'Auto 10%',
            'code' => null,
            'type' => 'percentage',
            'value' => 10,
            'min_amount' => 0,
            'is_automatic' => true,
            'is_active' => true,
        ]);

        $lines = $this->cartLines([[1, 50000.0, 2]]);

        $pricing = app(PricingService::class)->calculate($lines, 100000.0);

        $this->assertSame(10000.0, $pricing['discount_amount']);
        $this->assertSame('Auto 10%', $pricing['discount']->name);
    }

    public function test_expired_discount_is_ignored(): void
    {
        Discount::create([
            'name' => 'Expired',
            'code' => null,
            'type' => 'percentage',
            'value' => 50,
            'min_amount' => 0,
            'is_automatic' => true,
            'is_active' => true,
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->subDay(),
        ]);

        $lines = $this->cartLines([[1, 100000.0, 1]]);

        $pricing = app(PricingService::class)->calculate($lines, 100000.0);

        $this->assertSame(0.0, $pricing['discount_amount']);
    }

    public function test_discount_cannot_make_subtotal_after_discount_negative(): void
    {
        Discount::create([
            'name' => 'Flat 200%',
            'code' => null,
            'type' => 'percentage',
            'value' => 200,
            'min_amount' => 0,
            'is_automatic' => true,
            'is_active' => true,
        ]);

        $lines = $this->cartLines([[1, 10000.0, 1]]);

        $pricing = app(PricingService::class)->calculate($lines, 10000.0);

        $this->assertSame(10000.0, $pricing['discount_amount']);
        $this->assertSame(0.0, $pricing['taxable_base']);
        $this->assertGreaterThanOrEqual(0, $pricing['grand_total']);
    }

    public function test_discount_targets_only_matching_category(): void
    {
        $category = Category::factory()->create();
        $otherCategory = Category::factory()->create();

        $discount = Discount::create([
            'name' => 'Spesial Kategori',
            'code' => null,
            'type' => 'percentage',
            'value' => 20,
            'min_amount' => 0,
            'is_automatic' => true,
            'is_active' => true,
        ]);

        $discount->items()->create([
            'target_type' => 'category',
            'target_id' => $category->id,
        ]);

        $matching = $this->cartLines([[101, 30000.0, 1]]);
        $matching->transform(function ($line) use ($category) {
            $product = Product::factory()->create(['category_id' => $category->id]);
            $line['product_id'] = $product->id;
            $line['product_name'] = $product->name;

            return $line;
        });

        $pricing = app(PricingService::class)->calculate($matching, 30000.0);

        $this->assertSame(6000.0, round($pricing['discount_amount'], 2));

        $nonMatching = $this->cartLines([[102, 30000.0, 1]]);
        $nonMatching->transform(function ($line) use ($otherCategory) {
            $product = Product::factory()->create(['category_id' => $otherCategory->id]);
            $line['product_id'] = $product->id;
            $line['product_name'] = $product->name;

            return $line;
        });

        $pricing = app(PricingService::class)->calculate($nonMatching, 30000.0);

        $this->assertSame(0.0, $pricing['discount_amount']);
    }

    public function test_inventory_never_goes_negative(): void
    {
        $product = Product::factory()->create([
            'stock_type' => 'limited',
            'stock' => 1,
        ]);

        app(InventoryService::class)->decrement($product, 1);

        $this->assertSame(0, $product->fresh()->stock);

        $this->expectException(\RuntimeException::class);

        app(InventoryService::class)->decrement($product, 1);

        $this->assertSame(0, $product->fresh()->stock);
    }

    public function test_inventory_adjustment_records_movement(): void
    {
        $product = Product::factory()->create([
            'stock_type' => 'limited',
            'stock' => 10,
        ]);

        app(InventoryService::class)->adjust($product, 25, 'Stock opname');

        $this->assertSame(25, $product->fresh()->stock);
        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'type' => 'ADJUSTMENT',
            'before' => 10,
            'after' => 25,
        ]);
    }

    public function test_order_flow_transitions_match_business_rules(): void
    {
        $status = app(OrderStatusService::class);

        $this->assertSame(
            [Order::STATUS_ACCEPTED, Order::STATUS_CANCELLED],
            $status->allowedTransitions(Order::STATUS_NEW),
        );
        $this->assertSame(
            [Order::STATUS_COOKING, Order::STATUS_CANCELLED],
            $status->allowedTransitions(Order::STATUS_ACCEPTED),
        );
        $this->assertSame(
            [Order::STATUS_COMPLETED],
            $status->allowedTransitions(Order::STATUS_READY),
        );
    }
}
