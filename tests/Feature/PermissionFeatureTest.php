<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedStaffRolesAndSettings();
    }

    public function test_ac_07_cashier_cannot_access_user_management(): void
    {
        $cashier = $this->cashierUser();

        $this->actingAs($cashier)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_kitchen_cannot_access_cashier_queue(): void
    {
        $kitchen = $this->kitchenUser();

        $this->actingAs($kitchen)
            ->get(route('cashier.dashboard'))
            ->assertForbidden();
    }

    public function test_kitchen_cannot_confirm_cash_payment(): void
    {
        $kitchen = $this->kitchenUser();
        $order = Order::factory()->takeAway()->create();

        $this->actingAs($kitchen)
            ->post(route('cashier.orders.pay-cash', $order), [
                'payment_method_id' => 1,
                'amount_received' => 10000,
            ])->assertForbidden();
    }

    public function test_cashier_cannot_manage_catalog(): void
    {
        $cashier = $this->cashierUser();
        $product = Product::factory()->create();

        $this->actingAs($cashier)
            ->get(route('admin.products.edit', $product))
            ->assertForbidden();
    }

    public function test_cashier_can_access_own_queue(): void
    {
        $cashier = $this->cashierUser();

        $this->actingAs($cashier)
            ->get(route('cashier.dashboard'))
            ->assertOk();
    }

    public function test_kitchen_can_access_kitchen_board(): void
    {
        $kitchen = $this->kitchenUser();

        $this->actingAs($kitchen)
            ->get(route('kitchen.dashboard'))
            ->assertOk();
    }

    public function test_manager_cannot_manage_users(): void
    {
        $manager = $this->managerUser();

        $this->actingAs($manager)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_cashier_cannot_export_reports(): void
    {
        $cashier = $this->cashierUser();

        $this->actingAs($cashier)
            ->get(route('admin.reports.export'))
            ->assertForbidden();
    }

    public function test_kitchen_cannot_view_audit_logs(): void
    {
        $kitchen = $this->kitchenUser();

        $this->actingAs($kitchen)
            ->get(route('admin.audit-logs.index'))
            ->assertForbidden();
    }

    public function test_ac_10_admin_can_list_and_create_users(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
        $this->actAsFresh($admin)->get(route('admin.users.create'))->assertOk();
    }

    public function test_manager_can_view_reports(): void
    {
        $this->actAsFresh($this->managerUser())
            ->get(route('admin.reports.index'))
            ->assertOk();
    }

    public function test_admin_can_view_reports(): void
    {
        $this->actAsFresh($this->adminUser())
            ->get(route('admin.reports.index'))
            ->assertOk();
    }

    public function test_kitchen_cannot_view_reports_but_cashier_can(): void
    {
        $this->actAsFresh($this->kitchenUser())
            ->get(route('admin.reports.index'))
            ->assertForbidden();

        $this->actAsFresh($this->cashierUser())
            ->get(route('admin.reports.index'))
            ->assertOk();
    }

    public function test_only_admin_can_manage_categories(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Minuman Baru',
                'slug' => 'minuman-baru',
                'is_active' => true,
            ])->assertRedirect();

        $this->assertDatabaseHas('categories', ['name' => 'Minuman Baru']);

        $empty = Category::factory()->create();

        $this->actAsFresh($this->managerUser())
            ->delete(route('admin.categories.destroy', $empty))
            ->assertRedirect();

        $this->assertSoftDeleted('categories', ['id' => $empty->id]);
    }

    public function test_manager_can_manage_categories(): void
    {
        $category = Category::factory()->create();

        $this->actAsFresh($this->managerUser())
            ->post(route('admin.categories.store'), ['name' => 'Baru', 'slug' => 'baru'])
            ->assertRedirect();

        $empty = Category::factory()->create();

        $this->actAsFresh($this->managerUser())
            ->delete(route('admin.categories.destroy', $empty))
            ->assertRedirect();

        $this->assertSoftDeleted('categories', ['id' => $empty->id]);
    }

    public function test_cashier_cannot_create_adjust_inventory(): void
    {
        $cashier = $this->cashierUser();
        $product = Product::factory()->create([
            'stock_type' => 'limited',
            'stock' => 5,
        ]);

        $this->actingAs($cashier)
            ->post(route('admin.inventory.adjust', $product), [
                'new_stock' => 10,
            ])->assertForbidden();

        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_reset_password_requires_admin_permission(): void
    {
        $cashier = $this->cashierUser();
        $target = User::factory()->create(['is_active' => true]);

        $this->actingAs($cashier)
            ->post(route('admin.users.reset-password', $target), [
                'password' => 'Rahasia123!',
                'password_confirmation' => 'Rahasia123!',
            ])->assertForbidden();
    }
}
