<?php

namespace App\Providers;

use App\Services\CartService;
use App\Services\PricingService;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('layouts.kiosk', function (View $view) {
            $cart = app(CartService::class);
            $lines = $cart->lines();
            $subtotal = $cart->subtotal();

            $view->with('layoutCart', [
                'count' => $cart->count(),
                'subtotal' => $subtotal,
                'lines' => $lines,
                'pricing' => app(PricingService::class)->calculate($lines, $subtotal, session('cart.discount_code')),
            ]);
        });
    }
}
