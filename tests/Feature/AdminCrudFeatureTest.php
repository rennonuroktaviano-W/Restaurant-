<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCrudFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedStaffRolesAndSettings();
    }

    public function test_admin_can_edit_dining_table_without_changing_number(): void
    {
        $area = Area::factory()->create();
        $table = DiningTable::factory()->create([
            'area_id' => $area->id,
            'table_number' => 'T1',
        ]);

        $this->actAsFresh($this->adminUser())
            ->put(route('admin.dining-tables.update', $table), [
                'area_id' => $area->id,
                'table_number' => 'T1',
                'name' => 'Meja dekat jendela',
                'status' => 'available',
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.dining-tables.index'));

        $this->assertDatabaseHas('dining_tables', [
            'id' => $table->id,
            'table_number' => 'T1',
            'name' => 'Meja dekat jendela',
        ]);
    }

    public function test_admin_can_update_area_address_geo_and_hours(): void
    {
        $area = Area::factory()->create();

        $this->actAsFresh($this->adminUser())
            ->put(route('admin.areas.update', $area), [
                'name' => 'Restoran Utama',
                'slug' => 'restoran-utama',
                'type' => 'restaurant',
                'description' => 'Area dining utama',
                'address' => 'https://www.google.com/maps/search/?api=1&query=-6.9043,107.6181',
                'latitude' => -6.9043,
                'longitude' => 107.6181,
                'open_time' => '11:00',
                'close_time' => '22:00',
                'is_active' => true,
                'sort_order' => 0,
            ])
            ->assertRedirect(route('admin.areas.index'));

        $this->assertDatabaseHas('areas', [
            'id' => $area->id,
            'address' => 'https://www.google.com/maps/search/?api=1&query=-6.9043,107.6181',
            'latitude' => -6.9043,
            'longitude' => 107.6181,
            'open_time' => '11:00',
            'close_time' => '22:00',
        ]);
    }

    public function test_creating_category_with_soft_deleted_slug_gets_suffixed_slug(): void
    {
        $deleted = Category::factory()->create(['slug' => 'makanan']);
        $deleted->delete();

        $this->actAsFresh($this->adminUser())
            ->post(route('admin.categories.store'), [
                'name' => 'Makanan',
                'slug' => 'makanan',
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'name' => 'Makanan',
            'slug' => 'makanan-1',
        ]);
    }

    public function test_creating_product_with_soft_deleted_slug_gets_suffixed_slug(): void
    {
        $category = Category::factory()->create();
        $deleted = Product::factory()->create(['category_id' => $category->id, 'slug' => 'kopi-susu']);
        $deleted->delete();

        $this->actAsFresh($this->adminUser())
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'sku' => 'KOPISUSU2',
                'name' => 'Kopi Susu',
                'slug' => 'kopi-susu',
                'cost_price' => 8000,
                'sale_price' => 12000,
                'stock_type' => 'unlimited',
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'sku' => 'KOPISUSU2',
            'slug' => 'kopi-susu-1',
        ]);
    }

    public function test_product_search_and_category_filter_do_not_leak_rows_across_categories(): void
    {
        $categoryA = Category::factory()->create(['name' => 'Makanan']);
        $categoryB = Category::factory()->create(['name' => 'Minuman']);

        $inA = Product::factory()->create(['category_id' => $categoryA->id, 'name' => 'Nasi Goreng Spesial']);
        $inB = Product::factory()->create(['category_id' => $categoryB->id, 'name' => 'Es Teh Spesial']);

        $this->actAsFresh($this->adminUser())
            ->get(route('admin.products.index', ['search' => 'Spesial', 'category' => $categoryB->id]))
            ->assertOk()
            ->assertSee($inB->name)
            ->assertDontSee($inA->name);
    }

    public function test_cashier_cannot_access_admin_management_routes_but_can_view_reports(): void
    {
        $cashier = $this->cashierUser();

        $this->actAsFresh($cashier)
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        $this->actAsFresh($cashier)
            ->get(route('admin.inventory.index'))
            ->assertForbidden();

        $this->actAsFresh($cashier)
            ->get(route('admin.categories.index'))
            ->assertForbidden();

        $this->actAsFresh($cashier)
            ->get(route('admin.reports.index'))
            ->assertOk();
    }

    public function test_kitchen_cannot_access_admin_routes(): void
    {
        $this->actAsFresh($this->kitchenUser())
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        $this->actAsFresh($this->kitchenUser())
            ->get(route('admin.reports.index'))
            ->assertForbidden();
    }

    public function test_admin_crud_roundtrip_persists_in_database(): void
    {
        $admin = $this->adminUser();

        $area = Area::factory()->create();

        $this->actAsFresh($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Dessert',
                'slug' => 'dessert-ruang',
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.categories.index'));

        $category = Category::where('slug', 'dessert-ruang')->firstOrFail();

        $this->actAsFresh($admin)
            ->put(route('admin.categories.update', $category), [
                'name' => 'Dessert Premium',
                'slug' => 'dessert-ruang',
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Dessert Premium',
        ]);

        $this->actAsFresh($admin)
            ->get(route('admin.areas.index', ['search' => $area->name]))
            ->assertOk()
            ->assertSee($area->name);
    }
}
