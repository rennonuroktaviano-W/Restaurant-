<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();

        if ($user->hasAnyRole(['admin', 'manager'])) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('kitchen')) {
            return redirect()->route('kitchen.dashboard');
        }

        if ($user->can('payment.confirm_cash')) {
            return redirect()->route('cashier.dashboard');
        }

        return redirect()->route('home');
    }
}
