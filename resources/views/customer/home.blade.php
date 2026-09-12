@extends('layouts.kiosk')

@section('landing-nav', '1')

@section('title', $siteName)

@php
    $heroImage = $heroImages->first();
    $heroImageUrl = $heroImage ? asset('storage/'.$heroImage) : null;
@endphp

@section('hero')
    <section class="relative isolate flex min-h-[92svh] items-center overflow-hidden bg-forest-950 text-cream-50">
        @if ($heroImageUrl)
            <img src="{{ $heroImageUrl }}" alt="" aria-hidden="true"
                 class="absolute inset-0 -z-20 h-full w-full object-cover opacity-90">
            <div class="absolute inset-0 -z-10 bg-gradient-to-r from-forest-950/95 via-forest-950/70 to-forest-900/30"></div>
            <div class="absolute inset-0 -z-10 bg-gradient-to-t from-forest-950/80 via-transparent to-forest-950/40"></div>
        @else
            <div class="absolute inset-0 -z-10 bg-gradient-to-br from-forest-900 via-forest-950 to-forest-950"></div>
        @endif

        <div class="mx-auto w-full max-w-[1280px] px-5 pb-24 pt-32 sm:px-8">
            <div class="max-w-2xl">
                <p class="eyebrow reveal text-gold-300">Selamat Datang di {{ $siteName }}</p>
                <h1 class="reveal mt-5 font-display text-4xl leading-[1.05] font-semibold text-cream-50 sm:text-5xl lg:text-6xl">
                    Sajian yang dibuat perlahan,<br>
                    dinikmati tanpa tergesa.
                </h1>
                <p class="reveal mt-6 max-w-xl text-base leading-relaxed text-cream-200/90 sm:text-lg">
                    Dari dapur kami ke meja Anda — pilih hidangan, kami siapkan dengan saksama, dan
                    antar saat sudah siap disantap.
                </p>

                <div class="reveal mt-9 flex flex-wrap items-center gap-4">
                    <a href="{{ route('menu.index') }}" class="btn-hero !bg-gold-600 !border-gold-600 hover:!bg-gold-500 !text-forest-950">
                        Lihat Menu
                    </a>
                    <a href="#signature" class="btn-hero">Menu Unggulan</a>
                    <a href="{{ route('cart.index') }}" class="btn-hero" aria-label="Buka keranjang">Keranjang</a>
                </div>

                <dl class="reveal mt-12 flex flex-wrap gap-x-10 gap-y-4 text-sm">
                    <div>
                        <dt class="text-cream-300/70">Jam Buka</dt>
                        <dd class="mt-0.5 font-medium text-cream-50">11.00 – 22.00 WIB</dd>
                    </div>
                    <div>
                        <dt class="text-cream-300/70">Area</dt>
                        <dd class="mt-0.5 font-medium text-cream-50">{{ $categories->isNotEmpty() ? $categories->pluck('name')->take(3)->join(' · ') : 'Aneka hidangan' }}</dd>
                    </div>
                    @if ($phone)
                        <div>
                            <dt class="text-cream-300/70">Reservasi</dt>
                            <dd class="mt-0.5 font-medium text-cream-50">{{ $phone }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <a href="#story" class="absolute bottom-6 left-1/2 hidden -translate-x-1/2 flex-col items-center gap-2 text-cream-300/60 transition hover:text-cream-100 md:flex" aria-label="Gulir ke bawah">
            <span class="text-[10px] tracking-[0.28em] uppercase">Jelajahi</span>
            <span class="h-8 w-px bg-gradient-to-b from-cream-300/70 to-transparent"></span>
        </a>
    </section>
@endsection

@section('content')
    @include('customer._cart-drawer', ['lines' => $drawerLines, 'subtotal' => $drawerSubtotal])

    <div class="space-y-24 py-14 md:space-y-32 md:py-20">

        {{-- Strip: why dine with us --}}
        <section aria-label="Keunggulan" class="reveal grid gap-5 sm:grid-cols-3">
            <div class="card card-pad flex items-start gap-4">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-forest-100 text-forest-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4 0-8-3-8-9 0-4 3-6 8-9 5 3 8 5 8 9 0 6-4 9-8 9z"/></svg>
                </span>
                <div>
                    <h3 class="font-display text-lg font-semibold text-ink-900">Bahan Pilihan</h3>
                    <p class="mt-1 text-sm leading-relaxed text-ink-500">Dipilih setiap pagi, diolah sesuai pesanan untuk rasa yang konsisten.</p>
                </div>
            </div>
            <div class="card card-pad flex items-start gap-4">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-forest-100 text-forest-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </span>
                <div>
                    <h3 class="font-display text-lg font-semibold text-ink-900">Resep Klasik</h3>
                    <p class="mt-1 text-sm leading-relaxed text-ink-500">Hidangan yang diracik dengan presisi dari resep teruji dapur kami.</p>
                </div>
            </div>
            <div class="card card-pad flex items-start gap-4">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-forest-100 text-forest-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <div>
                    <h3 class="font-display text-lg font-semibold text-ink-900">Disajikan Tepat Waktu</h3>
                    <p class="mt-1 text-sm leading-relaxed text-ink-500">Pesan dari meja atau kamar, pantau status, dan santap saat baru matang.</p>
                </div>
            </div>
        </section>

        {{-- Signature collection --}}
        <section id="signature" aria-labelledby="signature-heading" class="scroll-mt-28">
            <div class="reveal mb-10 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="eyebrow">Menu Unggulan</p>
                    <h2 id="signature-heading" class="section-title mt-3">Signature Collection</h2>
                </div>
                <a href="{{ route('menu.index') }}" class="link-subtle text-sm font-medium">Lihat semua menu &rarr;</a>
            </div>

            @if ($featured->isEmpty())
                <div class="empty-state text-center">
                    <p class="text-sm text-ink-500">Koleksi unggulan sedang disiapkan dapur kami.</p>
                    <a href="{{ route('menu.index') }}" class="btn btn-primary mt-5">Pesan dari Menu</a>
                </div>
            @else
                <ol class="divide-y divide-ink-900/10 border-y border-ink-900/15">
                    @foreach ($featured as $index => $product)
                        <li class="reveal group flex items-center gap-5 px-2 py-6 transition hover:bg-cream-50 sm:gap-8 sm:px-4">
                            <span class="w-10 shrink-0 font-display text-2xl font-medium text-gold-600/80">
                                {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <div class="media-frame hidden h-24 w-36 shrink-0 sm:block">
                                @if ($product->image)
                                    <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-ink-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="font-display text-lg font-semibold text-ink-900 sm:text-xl">
                                    {{ $product->name }}
                                    @if ($product->category)
                                        <span class="ml-1.5 align-middle text-xs font-normal tracking-wide text-gold-600 uppercase">{{ $product->category->name }}</span>
                                    @endif
                                </h3>
                                @if ($product->description)
                                    <p class="mt-1 line-clamp-1 text-sm text-ink-500 sm:line-clamp-2">{{ $product->description }}</p>
                                @endif
                            </div>
                            <div class="flex shrink-0 flex-col items-end gap-2">
                                @if ($weeklyPromo && $weeklyPromo->coversProduct($product->id, $product->category_id) && ! $product->isSoldOut())
                                    <span class="badge badge-gold">Sedang Diskon {{ (int) $weeklyPromo->value }}%</span>
                                @endif
                                <span class="font-display text-xl font-semibold text-forest-800">
                                    Rp {{ number_format($product->sale_price, 0, ',', '.') }}
                                </span>
                                @if ($product->isSoldOut())
                                    <span class="badge badge-gold">Habis</span>
                                @else
                                    <form method="POST" action="{{ route('cart.add') }}" aria-label="Tambah {{ $product->name }}" data-cart-ajax>
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-secondary btn-sm">+ Tambah</button>
                                    </form>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>

        {{-- Active promos --}}
        @if ($activePromos->isNotEmpty())
            <section aria-labelledby="promo-heading" class="scroll-mt-28">
                <div class="reveal mb-10 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="eyebrow">Sedang Berlangsung</p>
                        <h2 id="promo-heading" class="section-title mt-3">Promo Spesial</h2>
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($activePromos as $promo)
                        <article class="card card-pad group relative overflow-hidden">
                            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-gold-600 via-gold-400 to-gold-600"></div>
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <h3 class="font-display text-lg font-semibold text-ink-900">{{ $promo->name }}</h3>
                                    <p class="mt-1 text-sm text-gold-700">{{ $promo->displayLabel() }}</p>
                                </div>
                                <span class="badge badge-gold shrink-0">{{ $promo->code ?: 'OTOMATIS' }}</span>
                            </div>
                            @if ($promo->description)
                                <p class="mt-3 line-clamp-2 text-sm leading-relaxed text-ink-500">{{ $promo->description }}</p>
                            @elseif ($promo->min_amount)
                                <p class="mt-3 text-sm leading-relaxed text-ink-500">Berlaku untuk pembelian minimal Rp {{ number_format((float) $promo->min_amount, 0, ',', '.') }}.</p>
                            @endif
                            @if ($promo->ends_at)
                                <p class="mt-4 flex items-center gap-2 border-t border-ink-900/10 pt-3 text-xs text-ink-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Berlaku hingga {{ $promo->ends_at->format('d M Y') }}
                                </p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Story / dining --}}
        <section id="story" aria-labelledby="story-heading" class="scroll-mt-28">
            <div class="panel-forest overflow-hidden">
                <div class="grid lg:grid-cols-2">
                    <div class="relative min-h-72 lg:min-h-full">
                        <div class="absolute inset-0 bg-gradient-to-br from-forest-800 to-forest-950">
                            @if ($heroImages->count() > 1)
                                <img src="{{ asset('storage/'.$heroImages[1]) }}" alt="{{ $siteName }}" loading="lazy" class="h-full w-full object-cover opacity-80">
                            @endif
                        </div>
                        <span class="absolute bottom-0 left-0 h-1 w-full bg-gradient-to-r from-gold-500/0 via-gold-500/70 to-gold-500/0"></span>
                    </div>
                    <div class="reveal px-6 py-12 sm:px-10 md:py-14">
                        <p class="eyebrow text-gold-300">Kisah Kami</p>
                        <h2 id="story-heading" class="mt-3 font-display text-3xl font-semibold text-cream-50 sm:text-4xl">
                            Setiap piring adalah perayaan rasa yang tenang
                        </h2>
                        <p class="mt-5 max-w-xl text-sm leading-relaxed text-cream-200/85 sm:text-base">
                            Kami percaya hidangan terbaik lahir dari kesabaran — bahan yang dipilih
                            dengan teliti, bumbu yang meresap sesuai waktunya, dan penyajian yang
                            menghormati kerja keras di dapur.
                        </p>
                        <ul class="mt-6 flex flex-wrap gap-x-6 gap-y-2 text-sm text-cream-100/80">
                            <li class="flex items-center gap-2">
                                <span class="h-1.5 w-1.5 rounded-full bg-gold-400"></span> Bahan segar harian
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="h-1.5 w-1.5 rounded-full bg-gold-400"></span> Resep dapur sendiri
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="h-1.5 w-1.5 rounded-full bg-gold-400"></span> Disajikan hangat
                            </li>
                        </ul>
                        <a href="{{ route('menu.index') }}" class="btn-hero mt-8 !border-gold-500/40">Cicipi Sekarang</a>
                    </div>
                </div>
            </div>
        </section>

        {{-- Location --}}
        <section id="location" aria-labelledby="location-heading" class="scroll-mt-28">
            <div class="reveal">
                <p class="eyebrow">Kunjungi Kami</p>
                <h2 id="location-heading" class="section-title mt-3">Temukan kami di lokasi terdekat</h2>

                @if ($areas->isNotEmpty())
                    <p class="mt-5 max-w-xl text-sm leading-relaxed text-ink-600 sm:text-base">
                        Kunjungi kedai kami untuk menikmati hidangan — santap di tempat, di kamar, maupun bawa pulang.
                    </p>

                    <div class="mt-9 grid gap-6 lg:grid-cols-2">
                        @foreach ($areas as $area)
                            <article class="card card-pad flex flex-col overflow-hidden">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="font-display text-xl font-semibold text-ink-900">{{ $area->name }}</h3>
                                        @if ($area->description)
                                            <p class="mt-1 text-sm leading-relaxed text-ink-500">{{ $area->description }}</p>
                                        @endif
                                    </div>
                                    <span class="badge badge-gold shrink-0">{{ $area->type }}</span>
                                </div>

                                <dl class="mt-5 space-y-3 text-sm">
                                    @if ($area->address)
                                        <div class="flex items-start gap-2.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0 text-forest-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <div class="min-w-0">
                                                <dt class="font-medium text-ink-800">Lokasi</dt>
                                                @if (filter_var($area->address, FILTER_VALIDATE_URL))
                                                    <dd class="mt-0.5">
                                                        <a href="{{ $area->maps_url }}" target="_blank" rel="noopener" class="font-medium text-forest-700 hover:underline">Buka di Google Maps &rarr;</a>
                                                    </dd>
                                                @else
                                                    <dd class="mt-0.5 text-ink-500">{{ $area->address }}</dd>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                    @if ($area->hours_label)
                                        <div class="flex items-start gap-2.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0 text-forest-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <div>
                                                <dt class="font-medium text-ink-800">Jam Buka</dt>
                                                <dd class="mt-0.5 text-ink-500">Setiap hari, {{ $area->hours_label }}</dd>
                                            </div>
                                        </div>
                                    @endif
                                </dl>

                                @if ($area->embed_url)
                                    <div class="media-frame mt-5 h-52 overflow-hidden">
                                        <iframe src="{{ $area->embed_url }}" title="Peta {{ $area->name }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen class="h-full w-full border-0"></iframe>
                                    </div>
                                @endif

                                <div class="mt-5">
                                    <a href="{{ $area->maps_url }}" target="_blank" rel="noopener" class="btn btn-primary inline-flex w-full items-center justify-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 3H5a2 2 0 00-2 2v3m18 0V5a2 2 0 00-2-2h-3m0 18h3a2 2 0 002-2v-3M3 16v3a2 2 0 002 2h3"/></svg>
                                        Menuju Restaurant
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="mt-5 max-w-lg text-sm leading-relaxed text-ink-600 sm:text-base">
                        Temukan kami di alamat berikut untuk menikmati hidangan kami —
                        baik bersantap di ruangan, kamar, maupun membawa pulang.
                    </p>

                    <div class="mt-8 inline-flex items-center gap-3 rounded-2xl bg-cream-50 px-5 py-4 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-forest-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="text-ink-600">{{ $hasAddress ? $address : 'Resort area — di tengah kawasan' }}</span>
                    </div>

                    <div class="mt-6">
                        <a href="{{ $hasAddress ? 'https://www.google.com/maps/search/?api=1&query='.urlencode($address) : '#' }}" target="_blank" rel="noopener" class="btn btn-primary @if (! $hasAddress) pointer-events-none opacity-50 @endif">
                            Menuju Restaurant
                        </a>
                    </div>
                @endif

                @if ($hasPhone)
                    <p class="mt-8 flex items-center gap-3 text-sm text-ink-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-forest-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        Reservasi &amp; Info: <span class="font-medium text-ink-800">{{ $phone }}</span>
                    </p>
                @endif
            </div>
        </section>

        {{-- Call to order --}}
        <section aria-labelledby="cta-heading" class="reveal">
            <div class="panel-forest relative overflow-hidden px-6 py-14 text-center sm:px-10">
                <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gold-500/10 blur-2xl"></div>
                <p class="eyebrow text-gold-300">Siap dipesan</p>
                <h2 id="cta-heading" class="mx-auto mt-3 max-w-2xl font-display text-3xl font-semibold text-cream-50 sm:text-4xl">
                    Pilih hidangan Anda, dan biarkan kami mengurus sisanya.
                </h2>
                <p class="mx-auto mt-4 max-w-xl text-sm leading-relaxed text-cream-200/80 sm:text-base">
                    Order dapat dilakukan dari meja, kamar, atau untuk dibawa pulang. Pantau statusnya hingga siap dinikmati.
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-4">
                    <a href="{{ route('menu.index') }}" class="btn-hero !bg-gold-600 !border-gold-600 hover:!bg-gold-500 !text-forest-950">Mulai Pesan</a>
                    <a href="{{ route('cart.index') }}" class="btn-hero">Lihat Keranjang</a>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const reveals = document.querySelectorAll('.reveal');

        if (reduceMotion || !('IntersectionObserver' in window)) {
            reveals.forEach((el) => el.classList.add('is-visible'));
        } else {
            const io = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        io.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.15 });
            reveals.forEach((el) => io.observe(el));
        }
    </script>
@endpush