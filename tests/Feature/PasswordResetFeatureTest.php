<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedStaffRolesAndSettings();
    }

    private function user(array $attributes = []): User
    {
        return User::factory()->create($attributes + [
            'is_active' => true,
            'password' => 'OldPassword-123!',
        ]);
    }

    public function test_forgot_password_sends_reset_link_to_registered_email(): void
    {
        Notification::fake();
        $user = $this->user();

        $this->post(route('password.forgot.store'), ['email' => $user->email])
            ->assertRedirect(route('password.forgot'))
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_forgot_password_does_not_leak_unregistered_email(): void
    {
        Notification::fake();

        $this->post(route('password.forgot.store'), ['email' => 'tidak-ada@pos.local'])
            ->assertSessionHas('status');

        Notification::assertNothingSent();
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = $this->user();
        $token = Password::broker()->createToken($user);

        $this->post(route('password.reset.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPassword-456!',
            'password_confirmation' => 'NewPassword-456!',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('NewPassword-456!', $user->fresh()->password));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'reset_password',
            'module' => 'auth',
            'target_type' => 'user',
            'target_id' => $user->id,
        ]);
    }

    public function test_invalid_token_is_rejected(): void
    {
        $user = $this->user();

        $this->from(route('password.reset.form', ['token' => 'invalid-token', 'email' => $user->email]))
            ->post(route('password.reset.store'), [
                'token' => 'invalid-token',
                'email' => $user->email,
                'password' => 'NewPassword-456!',
                'password_confirmation' => 'NewPassword-456!',
            ])
            ->assertRedirect(route('password.reset.form', ['token' => 'invalid-token', 'email' => $user->email]))
            ->assertSessionHasErrors('email');
    }

    public function test_password_must_be_confirmed_and_min_8(): void
    {
        $user = $this->user();
        $token = Password::broker()->createToken($user);

        $this->post(route('password.reset.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('OldPassword-123!', $user->fresh()->password));
    }

    public function test_forgot_password_page_is_public(): void
    {
        $this->get(route('password.forgot'))->assertOk();

        $this->get(route('password.reset.form', ['token' => 'abc', 'email' => 'x@y.test']))->assertOk();
    }
}
