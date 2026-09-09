<?php

namespace Tests;

use App\Models\User;
use App\Support\Permissions;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    /**
     * Seed the roles, permissions, and default settings shared across staff tests.
     */
    protected function seedStaffRolesAndSettings(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->seed([
            RolePermissionSeeder::class,
            SettingsSeeder::class,
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Create a staff user with an assigned role and optional permissions override.
     *
     * @param  array<int, string>|null  $permissions  when set, sync exactly these permissions
     */
    protected function staffUser(string $role, ?array $permissions = null, array $attributes = []): User
    {
        $user = User::factory()->create($attributes + ['is_active' => true]);

        $user->assignRole($role);

        if ($permissions !== null) {
            $user->syncPermissions($permissions);
        }

        return $user;
    }

    protected function adminUser(array $attributes = []): User
    {
        return $this->staffUser(Permissions::ROLE_ADMIN, Permissions::ALL, $attributes);
    }

    protected function cashierUser(array $attributes = []): User
    {
        return $this->staffUser(
            Permissions::ROLE_CASHIER,
            Permissions::rolePermissions()[Permissions::ROLE_CASHIER],
            $attributes,
        );
    }

    protected function kitchenUser(array $attributes = []): User
    {
        return $this->staffUser(
            Permissions::ROLE_KITCHEN,
            Permissions::rolePermissions()[Permissions::ROLE_KITCHEN],
            $attributes,
        );
    }

    protected function managerUser(array $attributes = []): User
    {
        return $this->staffUser(
            Permissions::ROLE_MANAGER,
            Permissions::rolePermissions()[Permissions::ROLE_MANAGER],
            $attributes,
        );
    }

    /**
     * Act as a different user while guaranteeing a fresh authenticated session,
     * avoiding session carry-over between sequential actingAs() calls.
     */
    protected function actAsFresh(User $user, ?string $guard = null): static
    {
        app('auth')->forgetGuards();
        $this->flushSession();

        return $this->actingAs($user, $guard);
    }
}
