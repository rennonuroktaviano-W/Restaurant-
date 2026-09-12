<?php

namespace Tests\Feature;

use App\Models\Discount;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingLookupFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedStaffRolesAndSettings();
    }

    public function test_lookup_page_renders_tracking_form(): void
    {
        $this->get(route('tracking.lookup'))
            ->assertOk()
            ->assertSee('Lacak Pesanan')
            ->assertSee('name="order_number"', false);
    }

    public function test_lookup_with_valid_number_redirects_to_tracking(): void
    {
        $order = Order::factory()->takeAway()->create();

        $this->post(route('tracking.find'), ['order_number' => $order->order_number])
            ->assertRedirect(route('tracking.show', ['order' => $order->order_number]));
    }

    public function test_lookup_with_unknown_number_uses_existing_missing_handler(): void
    {
        $response = $this->post(route('tracking.find'), ['order_number' => 'POS-20990101-XXXXX']);

        $response->assertRedirect(route('tracking.show', ['order' => 'POS-20990101-XXXXX']));

        // The existing missing() handler on tracking.show deals with unknown numbers.
        $this->get($response->headers->get('Location'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('error');
    }

    public function test_lookup_requires_order_number(): void
    {
        $this->post(route('tracking.find'), ['order_number' => ''])
            ->assertSessionHasErrors('order_number');
    }

    public function test_home_exposes_tracking_entry_point(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('tracking.lookup'));
    }

    public function test_cart_checkout_shows_visible_promo_section(): void
    {
        $product = Product::factory()->create(['stock_type' => 'unlimited', 'stock' => 99]);

        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1])
            ->assertRedirect();

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Kode Promo')
            ->assertSee('name="discount_code"', false)
            ->assertSee('name="discount_code" value=""', false);
    }

    public function test_landing_shows_only_active_promos_with_real_data(): void
    {
        $active = Discount::factory()->create([
            'name' => 'Promo Merdeka Rasa',
            'code' => 'MERDEKA10',
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addWeek(),
        ]);

        Discount::factory()->create([
            'name' => 'Promo Kedaluwarsa',
            'code' => 'BASISUDAH',
            'is_active' => true,
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->subDay(),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Promo Spesial')
            ->assertSee('Promo Merdeka Rasa')
            ->assertSee('MERDEKA10')
            ->assertDontSee('Promo Kedaluwarsa');
    }

    public function test_landing_hides_promo_section_when_no_active_promos(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Promo Spesial');
    }
}
