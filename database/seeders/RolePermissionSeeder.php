<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * FR-AUTH-001..006, FR-ADM-001 — roles, permissions, default staff accounts.
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (Permissions::ALL as $permission) {
            Permission::findOrCreate($permission);
        }

        foreach (Permissions::rolePermissions() as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName);
            $role->syncPermissions($permissions);
        }

        $this->createUser('admin@pos.local', 'Administrator', Permissions::ROLE_ADMIN, env('SEED_ADMIN_PASSWORD', 'ChangeMe-1234!'));
        $this->createUser('manager@pos.local', 'Manager', Permissions::ROLE_MANAGER, env('SEED_MANAGER_PASSWORD', 'ChangeMe-1234!'));
        $this->createUser('cashier@pos.local', 'Kasir', Permissions::ROLE_CASHIER, env('SEED_CASHIER_PASSWORD', 'ChangeMe-1234!'));
        $this->createUser('kitchen@pos.local', 'Dapur', Permissions::ROLE_KITCHEN, env('SEED_KITCHEN_PASSWORD', 'ChangeMe-1234!'));
    }

    private function createUser(string $email, string $name, string $role, string $password): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make($password), 'is_active' => true]
        );

        $user->syncRoles([$role]);
    }
}
