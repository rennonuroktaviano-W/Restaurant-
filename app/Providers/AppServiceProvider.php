<?php

namespace App\Providers;

use App\Services\CartService;
use App\Services\PricingService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        RateLimiter::for('kiosk-checkout', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->session()->getId());
        });

        RateLimiter::for('kiosk-cart', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->session()->getId());
        });

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
