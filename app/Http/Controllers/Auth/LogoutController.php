<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $id = auth()->id();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        app(AuditLogger::class)->log('logout', 'auth', 'user', $id, [], []);

        return redirect()->route('login');
    }
}
