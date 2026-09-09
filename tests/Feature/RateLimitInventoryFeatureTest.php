<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitInventoryFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedStaffRolesAndSettings();
    }

    public function test_webhook_endpoint_is_rate_limited(): void
    {
        $order = Order::factory()->takeAway()->create();
        $method = PaymentMethod::factory()->create(['type' => 'online']);
        $payment = $order->payments()->create([
            'payment_method_id' => $method->id,
            'type' => 'online',
            'status' => Payment::STATUS_PENDING,
            'amount' => $order->grand_total,
            'provider' => 'mock',
        ]);

        $response = null;

        for ($i = 0; $i < 61; $i++) {
            $response = $this->postJson(route('webhook.payment.mock', $payment), [
                'event' => 'payment.success',
                'payment_id' => $payment->id,
            ]);
        }

        $response->assertStatus(429);
    }

    public function test_tracking_page_is_rate_limited(): void
    {
        $order = Order::factory()->takeAway()->create();

        $response = null;

        for ($i = 0; $i < 121; $i++) {
            $response = $this->get(route('tracking.show', $order));
        }

        $response->assertStatus(429);
    }

    public function test_low_stock_alert_lists_below_threshold_products(): void
    {
        Product::factory()->create(['stock_type' => 'limited', 'stock' => 5, 'name' => 'Es Teh Rendah']);
        Product::factory()->create(['stock_type' => 'limited', 'stock' => 20, 'name' => 'Es Teh Aman']);
        Product::factory()->create(['stock_type' => 'unlimited', 'stock' => 3, 'name' => 'Nasi Tidak Terbatas']);

        $this->actAsFresh($this->adminUser());

        $this->get(route('admin.inventory.index'))
            ->assertOk()
            ->assertViewHas('lowStockCount', 1)
            ->assertViewHas('lowStockThreshold', 10)
            ->assertSee('1 produk di bawah ambang stok')
            ->assertSee('Es Teh Rendah — sisa')
            ->assertDontSee('Es Teh Aman — sisa');
    }

    public function test_low_stock_alert_respects_threshold_setting(): void
    {
        Setting::updateOrCreate(
            ['key' => 'inventory.low_stock_threshold'],
            ['value' => '25', 'label' => 'Ambang stok rendah', 'group' => 'inventory']
        );

        Product::factory()->create(['stock_type' => 'limited', 'stock' => 20, 'name' => 'Mie Rendah']);

        $this->actAsFresh($this->adminUser());

        $this->get(route('admin.inventory.index'))
            ->assertViewHas('lowStockCount', 1)
            ->assertViewHas('lowStockThreshold', 25)
            ->assertSee('Mie Rendah — sisa');
    }
}
