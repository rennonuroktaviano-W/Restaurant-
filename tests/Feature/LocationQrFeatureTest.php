<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\Product;
use App\Models\Room;
use App\Services\LocationTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationQrFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedStaffRolesAndSettings();
    }

    public function test_token_roundtrip_for_table_and_room(): void
    {
        $service = app(LocationTokenService::class);

        $tableToken = $service->issue(LocationTokenService::TYPE_TABLE, 42);
        $this->assertSame(['type' => LocationTokenService::TYPE_TABLE, 'id' => 42], $service->verify($tableToken));

        $roomToken = $service->issue(LocationTokenService::TYPE_ROOM, 7);
        $this->assertSame(['type' => LocationTokenService::TYPE_ROOM, 'id' => 7], $service->verify($roomToken));
    }

    public function test_tampered_token_is_rejected(): void
    {
        $service = app(LocationTokenService::class);
        $token = $service->issue(LocationTokenService::TYPE_TABLE, 42);

        $this->assertNull($service->verify($token.'x'));
        $this->assertNull($service->verify('AA.AA'));
        $this->assertNull($service->verify('not-a-token'));
    }

    public function test_expired_token_is_rejected(): void
    {
        $service = app(LocationTokenService::class);
        $token = $service->issue(LocationTokenService::TYPE_TABLE, 42, ttl: -3600);

        $this->assertNull($service->verify($token));
    }

    public function test_valid_location_token_is_persisted_in_session_from_menu(): void
    {
        $service = app(LocationTokenService::class);
        $token = $service->issue(LocationTokenService::TYPE_TABLE, 42);

        $this->get(route('menu.index', ['location_token' => $token]))
            ->assertOk()
            ->assertSessionHas('location.token', $token);
    }

    public function test_invalid_location_token_clears_session(): void
    {
        session(['location.token' => 'kotori']);

        $this->get(route('menu.index', ['location_token' => 'rusak']))
            ->assertOk()
            ->assertSessionMissing('location.token');
    }

    public function test_checkout_resolves_table_from_qr_token(): void
    {
        $area = Area::factory()->create();
        $table = DiningTable::factory()->create(['area_id' => $area->id, 'is_active' => true]);
        $product = Product::factory()->create(['stock_type' => 'unlimited', 'stock' => 99, 'sale_price' => 10000]);

        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1])->assertRedirect();

        $token = app(LocationTokenService::class)->issue(LocationTokenService::TYPE_TABLE, $table->id);

        $this->post(route('checkout.store'), [
            'order_type' => Order::TYPE_DINE_IN,
            'table_token' => $token,
            'customer_name' => 'Tamu QR',
        ])->assertRedirect();

        $order = Order::query()->latest('id')->first();
        $this->assertSame($table->id, $order->table_id);
        $this->assertSame($area->id, $order->area_id);
    }

    public function test_checkout_resolves_room_from_qr_token(): void
    {
        $area = Area::factory()->create();
        $room = Room::factory()->create(['area_id' => $area->id, 'is_active' => true]);
        $product = Product::factory()->create(['stock_type' => 'unlimited', 'stock' => 99]);

        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1])->assertRedirect();

        $token = app(LocationTokenService::class)->issue(LocationTokenService::TYPE_ROOM, $room->id);

        $this->post(route('checkout.store'), [
            'order_type' => Order::TYPE_ROOM_SERVICE,
            'table_token' => $token,
        ])->assertRedirect();

        $order = Order::query()->latest('id')->first();
        $this->assertSame($room->id, $order->room_id);
        $this->assertSame($area->id, $order->area_id);
    }

    public function test_checkout_resolves_table_from_session_after_menu_scan(): void
    {
        $area = Area::factory()->create();
        $table = DiningTable::factory()->create(['area_id' => $area->id, 'is_active' => true]);
        $product = Product::factory()->create(['stock_type' => 'unlimited', 'stock' => 99, 'sale_price' => 10000]);

        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1])->assertRedirect();

        $token = app(LocationTokenService::class)->issue(LocationTokenService::TYPE_TABLE, $table->id);

        $this->get(route('menu.index', ['location_token' => $token]))->assertOk();

        $this->post(route('checkout.store'), [
            'order_type' => Order::TYPE_DINE_IN,
        ])->assertRedirect();

        $order = Order::query()->latest('id')->first();
        $this->assertSame($table->id, $order->table_id);
    }

    public function test_admin_can_view_qr_pages(): void
    {
        $table = DiningTable::factory()->create(['is_active' => true]);

        $this->actAsFresh($this->adminUser());

        $this->get(route('admin.tables.qr', $table))
            ->assertOk()
            ->assertSee('data:image/svg+xml;base64');
    }

    public function test_cashier_cannot_view_qr_pages(): void
    {
        $table = DiningTable::factory()->create(['is_active' => true]);

        $this->actAsFresh($this->cashierUser());

        $this->get(route('admin.tables.qr', $table))->assertForbidden();
    }
}
