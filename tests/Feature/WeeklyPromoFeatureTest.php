<?php

namespace Tests\Feature;

use App\Models\Discount;
use App\Models\Product;
use App\Services\CartService;
use App\Services\PricingService;
use App\Services\WeeklyPromoService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeeklyPromoFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedStaffRolesAndSettings();
    }

    public function test_ensure_current_creates_single_active_weekly_promo(): void
    {
        Product::factory()->count(3)->create();

        $service = app(WeeklyPromoService::class);

        $promo = $service->ensureCurrent();
        $again = $service->ensureCurrent();

        $this->assertSame($promo->id, $again->id, 'ensureCurrent must be idempotent');

        $this->assertSame(1, Discount::where('is_weekly', true)->where('is_active', true)->count());
        $this->assertSame(Discount::TYPE_PERCENTAGE, $promo->type);
        $this->assertTrue((int) $promo->value >= 10 && (int) $promo->value <= 30);
        $this->assertNotNull($promo->code);
        $this->assertSame(now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d H:i:s'), $promo->starts_at->format('Y-m-d H:i:s'));
        $this->assertSame(2, $promo->items()->where('target_type', 'product')->count());
    }

    public function test_ensure_current_deactivates_stale_weekly_promo_and_creates_new(): void
    {
        $weekStart = now()->startOfWeek(Carbon::MONDAY);

        Discount::create([
            'name' => 'Promo lama',
            'code' => 'LAMA10',
            'type' => 'percentage',
            'value' => 10,
            'is_weekly' => true,
            'is_active' => true,
            'starts_at' => $weekStart->copy()->subWeek(),
            'ends_at' => $weekStart,
        ]);

        $service = app(WeeklyPromoService::class);
        $promo = $service->ensureCurrent();

        $this->assertSame(1, Discount::where('is_weekly', true)->where('is_active', true)->count());
        $this->assertSame($promo->id, Discount::where('is_weekly', true)->where('is_active', true)->first()->id);
        $this->assertDatabaseHas('discounts', ['code' => 'LAMA10', 'is_active' => false]);
    }

    public function test_regenerate_replaces_current_weekly_promo_with_fresh_random_one(): void
    {
        Product::factory()->count(3)->create();

        $service = app(WeeklyPromoService::class);
        $original = $service->ensureCurrent();

        $replacement = $service->regenerate();

        $this->assertNotSame($original->id, $replacement->id);
        $this->assertNotSame($original->code, $replacement->code);
        $this->assertSame(1, Discount::where('is_weekly', true)->where('is_active', true)->count());
        $this->assertDatabaseHas('discounts', ['id' => $original->id, 'is_active' => false]);
    }

    public function test_covers_product_is_true_only_for_weekly_targets(): void
    {
        $products = Product::factory()->count(3)->create();

        $promo = app(WeeklyPromoService::class)->ensureCurrent();
        $promo->load('items');

        $targetIds = $promo->items()->pluck('target_id')->map(fn ($id) => (int) $id)->all();
        $untargeted = $products->filter(fn ($p) => ! in_array($p->id, $targetIds, true))->first();

        foreach ($targetIds as $targetId) {
            $this->assertTrue($promo->coversProduct($targetId));
        }

        $this->assertNotNull($untargeted, 'Expected a non-target product for the negative case');
        $this->assertFalse($promo->coversProduct($untargeted->id, $untargeted->category_id));
    }

    public function test_menu_page_shows_weekly_discount_badge_for_covered_product(): void
    {
        $product = Product::factory()->create(['name' => 'Ayam Bakar']);

        $promo = app(WeeklyPromoService::class)->ensureCurrent();

        $this->get(route('menu.index'))
            ->assertOk()
            ->assertSee('Ayam Bakar')
            ->assertSee('Sedang Diskon')
            ->assertSee('Sedang Diskon '.((int) $promo->value).'%');
    }

    public function test_cart_page_shows_weekly_promo_code_hint(): void
    {
        Product::factory()->create();

        $promo = app(WeeklyPromoService::class)->ensureCurrent();

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Promo mingguan aktif')
            ->assertSee($promo->code);
    }

    public function test_cart_page_hint_lists_weekly_target_products(): void
    {
        $products = Product::factory()->count(3)->create();

        $promo = app(WeeklyPromoService::class)->ensureCurrent();
        $targetIds = $promo->items()->where('target_type', 'product')->pluck('target_id');
        $targetNames = $products->whereIn('id', $targetIds)->pluck('name');

        $response = $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Promo mingguan aktif');

        foreach ($targetNames as $name) {
            $response->assertSee($name);
        }
    }

    public function test_weekly_code_shows_clear_error_without_target_product_in_cart(): void
    {
        $products = Product::factory()->count(3)->create(['stock_type' => 'unlimited']);

        $promo = app(WeeklyPromoService::class)->ensureCurrent();
        $targetIds = $promo->items()->where('target_type', 'product')->pluck('target_id')->map(fn ($id) => (int) $id);
        $nonTarget = $products->first(fn ($p) => ! $targetIds->contains($p->id));

        $this->assertNotNull($nonTarget);
        app(CartService::class)->add($nonTarget->id, 1);

        $this->post(route('cart.discount'), ['discount_code' => $promo->code])
            ->assertRedirect()
            ->assertSessionHas('error');

        $error = session('error');
        $this->assertStringContainsString('hanya berlaku', $error);
        $this->assertStringContainsString($promo->code, $error);
        $this->assertFalse(session()->has('cart.discount_code'));
    }

    public function test_weekly_code_applies_when_target_product_in_cart(): void
    {
        $products = Product::factory()->count(3)->create(['stock_type' => 'unlimited']);

        $promo = app(WeeklyPromoService::class)->ensureCurrent();
        $targetId = (int) $promo->items()->where('target_type', 'product')->first()->target_id;

        $cart = app(CartService::class);
        $cart->add($targetId, 1);
        $subtotal = $cart->subtotal();

        $this->post(route('cart.discount'), ['discount_code' => $promo->code])
            ->assertRedirect()
            ->assertSessionHas('cart.discount_code', $promo->code)
            ->assertSessionHas('success');

        $lines = $cart->lines();
        $pricing = app(PricingService::class)->calculate($lines, $subtotal, $promo->code);

        $this->assertSame($promo->code, $pricing['discount_code']);
        $this->assertEqualsWithDelta(
            $subtotal * ((int) $promo->value / 100),
            $pricing['discount_amount'],
            0.01,
        );
    }

    public function test_admin_discount_index_creates_and_shows_weekly_promo(): void
    {
        Product::factory()->create();

        $this->actAsFresh($this->adminUser())
            ->get(route('admin.discounts.index'))
            ->assertOk()
            ->assertSee('Mingguan');

        $this->assertSame(1, Discount::where('is_weekly', true)->where('is_active', true)->count());
    }

    public function test_admin_can_generate_new_weekly_promo(): void
    {
        Product::factory()->count(3)->create();

        $this->actAsFresh($this->adminUser())
            ->post(route('admin.discounts.weekly'))
            ->assertRedirect(route('admin.discounts.index'))
            ->assertSessionHas('success');

        $this->assertSame(1, Discount::where('is_weekly', true)->where('is_active', true)->count());
    }
}
