<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    public function __construct(protected AuditLogger $audit) {}

    public function create(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::broker()->reset(
            $data,
            function (User $user, string $password) {
                $user->update(['password' => $password]);
                $user->remember_token = null;
                $user->save();

                $this->audit->log('reset_password', 'auth', 'user', $user->id, [], ['channel' => 'email']);
            }
        );

        return match ($status) {
            Password::PASSWORD_RESET => redirect()->route('login')->with('success', 'Password berhasil direset. Silakan masuk.'),
            Password::INVALID_TOKEN => throw ValidationException::withMessages(['email' => 'Tautan reset password tidak valid atau sudah kedaluwarsa.']),
            default => throw ValidationException::withMessages(['email' => 'Email tidak ditemukan.']),
        };
    }
}
