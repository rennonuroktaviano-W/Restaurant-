<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedStaffRolesAndSettings();
    }

    public function test_guest_is_redirected_to_login_when_accessing_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_fr_auth_001_active_staff_can_login_and_redirected_to_role_dashboard(): void
    {
        $admin = $this->adminUser(['password' => 'Password-1234']);

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'Password-1234',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_fr_auth_001_inactive_user_cannot_login(): void
    {
        $inactive = $this->adminUser(['password' => 'Password-1234', 'is_active' => false]);

        $this->from('/login')->post('/login', [
            'email' => $inactive->email,
            'password' => 'Password-1234',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_fr_auth_003_cashier_and_kitchen_are_redirected_to_their_dashboards(): void
    {
        $cashier = $this->cashierUser(['password' => 'Password-1234']);
        $kitchen = $this->kitchenUser(['password' => 'Password-1234']);

        $this->post('/login', ['email' => $cashier->email, 'password' => 'Password-1234'])
            ->assertRedirect(route('cashier.dashboard'));

        $this->post('/logout');

        $this->post('/login', ['email' => $kitchen->email, 'password' => 'Password-1234'])
            ->assertRedirect(route('kitchen.dashboard'));
    }

    public function test_fr_auth_005_failed_login_attempts_are_rate_limited(): void
    {
        $this->adminUser(['password' => 'Password-1234']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'nobody@example.com',
                'password' => 'Wrong-Password',
            ])->assertSessionHasErrors('email');
        }

        $this->post('/login', [
            'email' => 'nobody@example.com',
            'password' => 'Wrong-Password',
        ])->assertSessionHasErrors('email');
    }

    public function test_fr_auth_004_logout_invalidates_session(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)->post('/logout')->assertRedirect();

        $this->assertGuest();
    }

    public function test_fr_auth_006_password_reset_is_audited(): void
    {
        $admin = $this->adminUser();
        $target = $this->cashierUser();

        $this->actingAs($admin)
            ->post(route('admin.users.reset-password', $target), [
                'password' => 'NewPassword-1234',
                'password_confirmation' => 'NewPassword-1234',
            ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'reset_password',
            'module' => 'users',
            'target_type' => 'user',
            'target_id' => $target->id,
        ]);

        $this->assertTrue($target->fresh()->password !== 'NewPassword-1234');
    }
}
