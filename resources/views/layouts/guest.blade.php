@php
    $siteName = \App\Models\Setting::where('key', 'business.name')->value('value') ?? config('app.name');
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
    <main class="flex flex-1">
        <div class="hidden w-1/2 lg:block">
            <div class="relative h-full">
                @php
                    $image = \App\Models\Product::query()->visible()->whereNotNull('image')->orderBy('sort_order')->value('image');
                @endphp
                @if ($image)
                    <img src="{{ asset('storage/'.$image) }}" alt="{{ $siteName }}" class="absolute inset-0 h-full w-full object-cover">
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-forest-950/90 via-forest-900/40 to-forest-950/30"></div>
                <div class="absolute bottom-10 left-10 right-10">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5 text-cream-50">
                        <span class="flex h-11 w-11 items-center justify-center rounded-full border border-gold-500/50 bg-forest-800 text-gold-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v10m0 0a4 4 0 100 8c2.21 0 4-1.79 4-4M12 14c.5 1 1.5 2.5 3 2.5a2.5 2.5 0 100-5c-1.5 0-2.5 1-3 2.5z"/></svg>
                        </span>
                        <span class="font-display text-xl font-semibold">{{ $siteName }}</span>
                    </a>
                    <p class="mt-4 max-w-sm text-sm leading-relaxed text-cream-200/85">
                        Sajian yang dibuat perlahan, dinikmati tanpa tergesa. Masuk untuk akses kasir, dapur, dan manajemen.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex w-full flex-col justify-center px-6 py-12 lg:w-1/2 lg:px-16">
            <div class="mb-8 flex items-center justify-between lg:hidden">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full border border-gold-500/50 bg-forest-800 text-gold-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v10m0 0a4 4 0 100 8c2.21 0 4-1.79 4-4M12 14c.5 1 1.5 2.5 3 2.5a2.5 2.5 0 100-5c-1.5 0-2.5 1-3 2.5z"/></svg>
                    </span>
                    <span class="font-display text-lg font-semibold text-ink-900">{{ $siteName }}</span>
                </a>
                <a href="{{ route('login') }}" class="text-sm font-medium text-forest-700 hover:underline">Masuk</a>
            </div>

            <div class="mx-auto w-full max-w-sm">
                @include('partials.customer-flash')
                @yield('content')
            </div>
        </div>
    </main>
</body>
</html>