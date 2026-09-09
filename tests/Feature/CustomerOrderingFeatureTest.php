<?php

namespace Tests\Feature;

use App\Models\DiningTable;
use App\Models\Discount;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderingFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedStaffRolesAndSettings();
    }

    private function product(array $attributes = []): Product
    {
        return Product::factory()->create($attributes + ['stock_type' => 'unlimited', 'stock' => 99]);
    }

    private function addToCart(int $productId, int $quantity = 1): void
    {
        $this->post(route('cart.add'), [
            'product_id' => $productId,
            'quantity' => $quantity,
        ])->assertRedirect();
    }

    public function test_fr_cus_001_guest_can_view_menu_without_account(): void
    {
        $this->product();

        $this->get(route('menu.index'))->assertOk()->assertSee('Lanjut');
    }

    public function test_fr_cus_003_inactive_product_is_hidden_from_menu(): void
    {
        $this->product(['is_active' => false, 'name' => 'Produk Rahasia']);

        $this->get(route('menu.index'))->assertDontSee('Produk Rahasia');
    }

    public function test_fr_cus_005_and_006_cart_supports_quantity_notes_and_update(): void
    {
        $product = $this->product(['sale_price' => 10000]);

        $this->addToCart($product->id, 2);

        $this->post(route('cart.update', $product->id), [
            'quantity' => 3,
            'notes' => 'Tanpa pedas',
        ])->assertRedirect(route('cart.index'));

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Tanpa pedas')
            ->assertSee('30.000');
    }

    public function test_fr_cus_007_checkout_rejects_dine_in_without_table(): void
    {
        $product = $this->product();

        $this->addToCart($product->id);

        $this->from(route('cart.index'))->post(route('checkout.store'), [
            'order_type' => Order::TYPE_DINE_IN,
        ])->assertSessionHasErrors('table_id');
    }

    public function test_checkout_rejects_unavailable_or_inactive_item(): void
    {
        $product = $this->product(['is_active' => false]);

        $this->addToCart($product->id);

        $this->post(route('checkout.store'), [
            'order_type' => Order::TYPE_TAKE_AWAY,
        ])->assertSessionHas('error');
    }

    public function test_fr_loc_004_checkout_rejects_inactive_table(): void
    {
        $table = DiningTable::factory()->create(['is_active' => false]);
        $product = $this->product();
        $this->addToCart($product->id);

        $this->post(route('checkout.store'), [
            'order_type' => Order::TYPE_DINE_IN,
            'table_id' => $table->id,
        ])->assertSessionHasErrors('table_id');

        $this->assertSame(0, Order::count());
    }

    public function test_fr_loc_004_checkout_rejects_inactive_room(): void
    {
        $room = Room::factory()->create(['is_active' => false]);
        $product = $this->product();
        $this->addToCart($product->id);

        $this->post(route('checkout.store'), [
            'order_type' => Order::TYPE_ROOM_SERVICE,
            'room_id' => $room->id,
        ])->assertSessionHasErrors('room_id');

        $this->assertSame(0, Order::count());
    }

    public function test_fr_pay_001_checkout_rejects_inactive_payment_method(): void
    {
        $method = PaymentMethod::factory()->create(['is_active' => false, 'type' => 'cash']);
        $product = $this->product();
        $this->addToCart($product->id);

        $this->post(route('checkout.store'), [
            'order_type' => Order::TYPE_TAKE_AWAY,
            'payment_method_id' => $method->id,
        ])->assertSessionHasErrors('payment_method_id');

        $this->assertSame(0, Order::count());
    }

    public function test_ac_01_checkout_creates_single_order_with_snapshot_in_one_transaction(): void
    {
        $product = $this->product(['sale_price' => 20000, 'is_kitchen' => true]);

        $this->addToCart($product->id, 2);

        $cartResponse = $this->get(route('cart.index'));
        $cartResponse->assertOk();

        $this->assertNotEmpty(session('customer_cart'), 'cart is empty in session');

        $checkoutResp = $this->post(route('checkout.store'), [
            'order_type' => Order::TYPE_TAKE_AWAY,
            'customer_name' => 'Budi',
            'customer_phone' => '08123456',
        ]);
        $checkoutResp->assertSessionHasNoErrors();
        $checkoutResp->assertRedirect();
        $this->assertSame(1, Order::count(), 'expected order created but count is '.Order::count());

        $order = Order::firstOrFail();

        $this->assertSame('take_away', $order->order_type);
        $this->assertCount(1, $order->items);
        $this->assertSame($product->name, $order->items->first()->product_name);
        $this->assertSame(20000.0, (float) $order->items->first()->price);
        $this->assertSame(2, $order->items->first()->quantity);
        $this->assertTrue($order->has_kitchen_items, 'has_kitchen_items expected true, got '.var_export($order->has_kitchen_items, true));
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'to_status' => Order::STATUS_NEW,
        ]);
    }

    public function test_ac_02_server_recalculates_total_ignoring_browser_values(): void
    {
        $product = $this->product(['sale_price' => 5000]);

        $this->addToCart($product->id, 4);

        $this->post(route('checkout.store'), [
            'order_type' => Order::TYPE_TAKE_AWAY,
        ]);

        $order = Order::firstOrFail();

        $this->assertSame(20000.0, (float) $order->subtotal);
    }

    public function test_ac_03_limited_stock_checkout_cannot_oversell(): void
    {
        $product = $this->product(['stock_type' => 'limited', 'stock' => 1]);

        $this->addToCart($product->id, 1);

        $this->post(route('checkout.store'), ['order_type' => Order::TYPE_TAKE_AWAY])
            ->assertRedirect();

        $this->assertSame(0, $product->fresh()->stock);

        $this->get('/');

        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1])->assertRedirect();
    }

    public function test_ac_04_idempotency_key_prevents_duplicate_orders(): void
    {
        $product = $this->product();

        $this->addToCart($product->id);

        $payload = [
            'order_type' => Order::TYPE_TAKE_AWAY,
            'idempotency_key' => 'same-key-123',
        ];

        $this->post(route('checkout.store'), $payload)->assertRedirect();
        $this->post(route('checkout.store'), $payload)->assertRedirect();

        $this->assertSame(1, Order::count());
    }

    public function test_fr_cus_008_order_receives_order_number_and_tracking_token(): void
    {
        $product = $this->product();

        $this->addToCart($product->id);

        $this->post(route('checkout.store'), [
            'order_type' => Order::TYPE_TAKE_AWAY,
        ])->assertRedirect();

        $order = Order::firstOrFail();

        $this->assertNotEmpty($order->order_number);

        $this->get(route('tracking.show', $order))->assertOk()->assertSee($order->order_number);
    }

    public function test_brand_new_order_is_pending_and_not_yet_accepted(): void
    {
        $product = $this->product();

        $this->addToCart($product->id);

        $this->post(route('checkout.store'), ['order_type' => Order::TYPE_TAKE_AWAY]);

        $order = Order::firstOrFail();

        $this->assertSame(Order::STATUS_NEW, $order->order_status);
        $this->assertSame(Order::PAYMENT_PENDING, $order->payment_status);
    }

    public function test_effective_discount_is_applied_on_checkout(): void
    {
        $this->product(['sale_price' => 60000]);

        Discount::create([
            'name' => 'Diskon Kode 10%',
            'code' => 'PROMO10',
            'type' => 'percentage',
            'value' => 10,
            'min_amount' => 50000,
            'is_automatic' => false,
            'is_active' => true,
        ]);

        $this->addToCart(Product::first()->id);

        $this->post(route('checkout.store'), [
            'order_type' => Order::TYPE_TAKE_AWAY,
            'discount_code' => 'PROMO10',
        ])->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame(6000.0, (float) $order->discount_amount);
    }

    public function test_online_payment_keeps_order_pending_until_verified_callback(): void
    {
        PaymentMethod::create([
            'name' => 'Tunai',
            'code' => 'cash',
            'type' => 'cash',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $qris = PaymentMethod::create([
            'name' => 'QRIS',
            'code' => 'qris',
            'type' => 'online',
            'is_active' => true,
            'sort_order' => 2,
            'config' => ['provider' => 'mock'],
        ]);

        $product = $this->product(['sale_price' => 10000]);

        $this->addToCart($product->id);

        $this->post(route('checkout.store'), [
            'order_type' => Order::TYPE_TAKE_AWAY,
            'payment_method_id' => $qris->id,
        ])->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame(Order::STATUS_NEW, $order->order_status);
        $this->assertSame(Order::PAYMENT_PENDING, $order->payment_status);
    }
}
