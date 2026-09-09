<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50">
    <header class="sticky top-0 z-30 border-b border-stone-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-5xl items-center justify-between px-4">
            <a href="{{ route('menu.index') }}" class="flex items-center gap-2">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-600 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v10m0 0a4 4 0 100 8c2.21 0 4-1.79 4-4M12 14c.5 1 1.5 2.5 3 2.5a2.5 2.5 0 100-5c-1.5 0-2.5 1-3 2.5z"/></svg>
                </div>
                <span class="font-semibold text-gray-900">{{ \App\Models\Setting::where('key', 'business.name')->value('value') ?? config('app.name') }}</span>
            </a>

            <div class="flex items-center gap-3">
                @if (auth()->check())
                    <a href="{{ auth()->user()->can('order.view') ? route('cashier.dashboard') : route('dashboard') }}" class="hidden sm:block text-sm font-medium text-brand-600 hover:text-brand-700">
                        Kasir
                    </a>
                @endif
                <button type="button"
                        x-data
                        @click="$dispatch('open-cart')"
                        class="relative flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    Keranjang
                    <span x-text="$store.cartCount" x-show="$store.cartCount > 0" x-cloak class="flex h-5 min-w-5 items-center justify-center rounded-full bg-brand-600 px-1.5 text-xs font-semibold text-white">0</span>
                </button>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-6">
        @include('partials.flash')
        @yield('content')
    </main>

    <script>
        window.EchoEnabled = @json(config('broadcasting.default') === 'reverb');
    </script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('cartCount', 0);
        });
    </script>
    @stack('scripts')
</body>
</html>