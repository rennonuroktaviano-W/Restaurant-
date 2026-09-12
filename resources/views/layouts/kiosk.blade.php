@php
    $siteName = \App\Models\Setting::where('key', 'business.name')->value('value') ?? config('app.name');
    $landingNav = trim($__env->yieldContent('landing-nav')) === '1';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $siteName)</title>
    @fonts
    @vite(['resources/css/customer.css', 'resources/js/app.js'])
    <noscript><style>.reveal { opacity: 1 !important; transform: none !important; }</style></noscript>
    @stack('styles')
</head>
<body class="flex min-h-screen flex-col bg-cream-100 font-sans text-ink-800 antialiased">
    <a href="#content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-lg focus:bg-ink-900 focus:px-4 focus:py-2 focus:text-cream-50">
        Lewati ke konten
    </a>

    <header x-data="{ menuOpen: false }" class="fixed inset-x-0 top-0 z-40">
        <nav aria-label="Navigasi utama"
             class="mx-auto flex h-20 max-w-[1280px] items-center justify-between gap-4 border-b border-cream-300/70 bg-cream-100/95 px-5 backdrop-blur sm:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5" aria-label="{{ $siteName }} — Beranda">
                <span class="flex h-10 w-10 items-center justify-center rounded-full border border-gold-500/50 bg-forest-800 text-gold-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v10m0 0a4 4 0 100 8c2.21 0 4-1.79 4-4M12 14c.5 1 1.5 2.5 3 2.5a2.5 2.5 0 100-5c-1.5 0-2.5 1-3 2.5z"/></svg>
                </span>
                <span class="font-display text-lg font-semibold tracking-tight text-ink-900">{{ $siteName }}</span>
            </a>

            <div class="hidden items-center gap-8 lg:flex">
                <a href="{{ route('menu.index') }}" class="text-sm font-medium text-ink-700 transition hover:text-forest-700">Menu</a>
                <a href="{{ route('tracking.lookup') }}" class="text-sm font-medium text-ink-700 transition hover:text-forest-700">Lacak Pesanan</a>
                @if ($landingNav)
                    <a href="#signature" class="text-sm font-medium text-ink-700 transition hover:text-forest-700">Menu Unggulan</a>
                    <a href="#story" class="text-sm font-medium text-ink-700 transition hover:text-forest-700">Tentang</a>
                    <a href="#location" class="text-sm font-medium text-ink-700 transition hover:text-forest-700">Lokasi</a>
                @endif
            </div>

            <div class="flex items-center gap-2">
                <button type="button"
                        x-data
                        @click="$dispatch('open-cart')"
                        class="relative inline-flex h-11 items-center gap-2 rounded-full border border-ink-900/15 bg-cream-50 px-4 text-sm font-medium text-ink-800 shadow-sm transition hover:border-forest-600/40 hover:text-forest-700"
                        aria-label="Buka keranjang">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span class="hidden sm:inline">Keranjang</span>
                    <span x-text="$store.cartCount" x-show="$store.cartCount > 0" x-cloak class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-gold-600 px-1.5 font-semibold text-cream-50">0</span>
                </button>

                <button type="button"
                        @click="menuOpen = !menuOpen"
                        :aria-expanded="menuOpen ? 'true' : 'false'"
                        aria-controls="mobile-menu"
                        aria-label="Buka menu navigasi"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-ink-900/15 bg-cream-50 text-ink-800 lg:hidden">
                    <svg x-show="!menuOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M3 12h18M3 18h18"/></svg>
                    <svg x-show="menuOpen" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </nav>

        <div id="mobile-menu" x-show="menuOpen" x-cloak x-transition
             class="border-b border-cream-300/70 bg-cream-100/95 backdrop-blur lg:hidden">
            <div class="mx-auto max-w-[1280px] space-y-1 px-5 py-4 sm:px-8">
                <a href="{{ route('menu.index') }}" @click="menuOpen = false" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-ink-800 hover:bg-cream-200/60">Menu</a>
                <a href="{{ route('tracking.lookup') }}" @click="menuOpen = false" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-ink-800 hover:bg-cream-200/60">Lacak Pesanan</a>
                @if ($landingNav)
                    <a href="#signature" @click="menuOpen = false" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-ink-800 hover:bg-cream-200/60">Menu Unggulan</a>
                    <a href="#story" @click="menuOpen = false" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-ink-800 hover:bg-cream-200/60">Tentang</a>
                    <a href="#location" @click="menuOpen = false" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-ink-800 hover:bg-cream-200/60">Lokasi</a>
                @endif
            </div>
        </div>
    </header>

    @yield('hero')

    <main id="content" class="flex-1 pt-20">
        <div class="mx-auto max-w-[1280px] px-5 py-8 sm:px-8">
            @include('partials.customer-flash')
            @yield('content')
        </div>
    </main>

    <footer class="border-t border-ink-900/10 bg-forest-900 text-cream-200">
        <div class="mx-auto grid max-w-[1280px] gap-10 px-5 py-14 sm:px-8 lg:grid-cols-[1.4fr_1fr_1.2fr]">
            <div>
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full border border-gold-500/50 bg-forest-800 text-gold-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v10m0 0a4 4 0 100 8c2.21 0 4-1.79 4-4M12 14c.5 1 1.5 2.5 3 2.5a2.5 2.5 0 100-5c-1.5 0-2.5 1-3 2.5z"/></svg>
                    </span>
                    <span class="font-display text-lg font-semibold text-cream-50">{{ $siteName }}</span>
                </a>
                <p class="mt-4 max-w-xs text-sm leading-relaxed text-cream-300/80">
                    Pengalaman bersantap hangat dengan hidangan pilihan, bahan berkualitas, dan suasana yang dirancang untuk dinikmati perlahan.
                </p>
            </div>

            <nav aria-label="Navigasi menu">
                <h2 class="text-sm font-semibold tracking-wide text-cream-50 uppercase">Menjelajah</h2>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('menu.index') }}" class="text-cream-300/80 transition hover:text-cream-50">Lihat Menu</a></li>
                    <li><a href="{{ route('tracking.lookup') }}" class="text-cream-300/80 transition hover:text-cream-50">Lacak Pesanan</a></li>
                    <li><a href="{{ route('menu.index') }}" class="text-cream-300/80 transition hover:text-cream-50">Pesan Sekarang</a></li>
                    <li><a href="{{ route('cart.index') }}" class="text-cream-300/80 transition hover:text-cream-50">Keranjang</a></li>
                </ul>
            </nav>

            <div>
                <h2 class="text-sm font-semibold tracking-wide text-cream-50 uppercase">Jam Buka</h2>
                <p class="mt-4 text-sm text-cream-300/80">Setiap hari</p>
                <p class="mt-1 text-sm text-cream-300/80">11.00 – 22.00 WIB</p>
                @php
                    $address = \App\Models\Setting::where('key', 'business.address')->value('value');
                    $phone = \App\Models\Setting::where('key', 'business.phone')->value('value');
                @endphp
                @if ($address)
                    <p class="mt-4 text-sm text-cream-300/80">{{ $address }}</p>
                @endif
                @if ($phone)
                    <a href="tel:{{ preg_replace('/\D+/', '', $phone) }}" class="mt-1 block text-sm text-gold-300 transition hover:text-gold-200">{{ $phone }}</a>
                @endif
            </div>
        </div>

        <div class="border-t border-cream-50/10">
            <div class="mx-auto flex max-w-[1280px] flex-col items-center justify-between gap-2 px-5 py-6 text-center sm:flex-row sm:px-8 sm:text-left">
                <p class="text-sm text-cream-300/70">&copy; {{ date('Y') }} {{ $siteName }}. Semua hak dilindungi.</p>
                <p class="text-xs text-cream-300/50">Menu dapat berubah sewaktu-waktu sesuai ketersediaan dapur.</p>
            </div>
        </div>
    </footer>

    <script type="application/json" id="cart-state">@json($layoutCart ?? null)</script>

    <script>
        window.EchoEnabled = @json(config('broadcasting.default') === 'reverb');
    </script>
    @stack('scripts')
</body>
</html>