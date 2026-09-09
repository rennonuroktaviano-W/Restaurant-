<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function __construct(protected AuditLogger $audit) {}

    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::query()->where('email', $data['email'])->where('is_active', true)->first();

        if ($user) {
            $token = Password::broker()->createToken($user);

            $user->notify(new ResetPasswordNotification($token));

            $this->audit->log('password_reset_request', 'auth', 'user', $user->id, [], ['channel' => 'email']);
        }

        return redirect()->route('password.forgot')->with('status', 'Jika email terdaftar, tautan reset password telah dikirim.');
    }
}
