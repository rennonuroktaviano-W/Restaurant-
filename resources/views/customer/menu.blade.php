@extends('layouts.kiosk')

@section('title', isset($category) ? $category->name.' - '.config('app.name') : 'Menu - '.config('app.name'))

@section('content')
    @include('customer._cart-drawer', ['lines' => $drawerLines, 'subtotal' => $drawerSubtotal])

    <section class="mx-auto max-w-4xl text-center">
        <p class="eyebrow">Selamat datang</p>
        <h1 class="section-title mt-3">Pilih menu favorit Anda</h1>
        <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-ink-500 sm:text-base">
            Pesan dari meja, disiapkan dapur, dan diantar saat sudah siap disantap.
        </p>
    </section>

    <nav aria-label="Kategori menu" class="mt-10 -mx-4 overflow-x-auto px-4 pb-1 lg:mx-0 lg:px-0">
        <div class="flex gap-2">
            <a href="{{ route('menu.index') }}"
               class="{{ ! isset($category) ? 'chip chip-active' : 'chip chip-idle' }}">
                Semua Menu
            </a>
            @foreach ($categories as $cat)
                <a href="{{ route('menu.category', $cat) }}"
                   class="{{ $category?->slug === $cat->slug ? 'chip chip-active' : 'chip chip-idle' }}">
                    {{ $cat->name }}
                    <span class="{{ $category?->slug === $cat->slug ? 'text-cream-200/80' : 'text-ink-400' }}">{{ $cat->active_products_count }}</span>
                </a>
            @endforeach
        </div>
    </nav>

    <div class="mt-8">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-display text-2xl font-semibold text-ink-900">{{ isset($category) ? $category->name : 'Seluruh Menu' }}</h2>
            <form method="GET" action="{{ route('menu.index') }}" class="flex items-center gap-2" role="search">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari menu..." aria-label="Cari menu" class="input max-w-56">
                <button type="submit" class="btn btn-secondary !px-3.5" aria-label="Cari">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </button>
            </form>
        </div>

        @if (! $products->isEmpty() && request('q'))
            <p class="mb-3 text-xs text-ink-500">Hasil pencarian untuk "{{ request('q') }}".</p>
        @endif

        @if ($products->isEmpty())
            <div class="empty-state">
                <p class="text-sm text-ink-500">Tidak ada menu tersedia saat ini.</p>
                @if (request('q'))
                    <a href="{{ route('menu.index') }}" class="link-subtle mt-3 text-sm font-medium">Hapus pencarian</a>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($products as $product)
                    <article class="card group overflow-hidden transition duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                        <div class="media-frame relative aspect-[4/3] rounded-none border-0">
                            @if ($product->image)
                                <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-cream-200 text-ink-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif

                            @if ($product->isSoldOut())
                                <div class="absolute inset-0 flex items-center justify-center bg-ink-950/50 backdrop-blur-[1px]">
                                    <span class="badge bg-burgundy-700 text-cream-50 !px-4 !py-1.5 text-xs font-bold uppercase tracking-wide">Habis</span>
                                </div>
                            @endif
                        </div>

                        <div class="p-5">
                            @if ($product->category)
                                <p class="text-[10px] font-semibold tracking-[0.18em] text-gold-600 uppercase">{{ $product->category->name }}</p>
                            @endif
                            <h3 class="mt-1 font-display text-lg font-semibold text-ink-900">{{ $product->name }}</h3>
                            @if ($product->description)
                                <p class="mt-1 line-clamp-2 text-sm leading-relaxed text-ink-500">{{ $product->description }}</p>
                            @endif

                            <div class="mt-4 flex items-center justify-between gap-2 border-t border-ink-900/10 pt-4">
                                <span class="font-display text-xl font-semibold text-forest-800">Rp {{ number_format($product->sale_price, 0, ',', '.') }}</span>

                                @php $qty = $cartQuantities[$product->id] ?? 0; @endphp

                                @if ($product->isSoldOut())
                                    <button type="button" disabled aria-label="Stok {{ $product->name }} habis"
                                            class="btn btn-secondary btn-sm !min-h-0 opacity-60">Habis</button>
                                @else
                                    <form method="POST" action="{{ route('cart.update', $product->id) }}"
                                          data-cart-ajax data-menu-stepper="{{ $product->id }}"
                                          class="flex items-center rounded-lg border border-ink-900/15 bg-cream-50"
                                          @if ($qty === 0) hidden @endif>
                                        @csrf
                                        <button type="submit" name="quantity" value="{{ max(1, $qty) - 1 }}" data-menu-minus
                                                aria-label="Kurangi {{ $product->name }}" class="px-3 py-1.5 text-sm font-semibold text-ink-500 transition hover:text-forest-700">−</button>
                                        <span class="min-w-7 text-center text-sm font-semibold text-ink-800" data-menu-qty="{{ $product->id }}" aria-label="{{ $qty }} di keranjang">{{ $qty }}</span>
                                        <button type="submit" name="quantity" value="{{ $qty + 1 }}" data-menu-plus
                                                @if ($product->isLimitedStock() && $qty >= $product->stock) disabled @endif
                                                aria-label="Tambah {{ $product->name }}" class="px-3 py-1.5 text-sm font-semibold text-ink-500 transition hover:text-forest-700 disabled:opacity-40">+</button>
                                    </form>
                                    <form method="POST" action="{{ route('cart.add') }}" data-cart-ajax data-menu-add="{{ $product->id }}"
                                          class="flex items-center gap-2" @if ($qty > 0) hidden @endif>
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" aria-label="Tambah {{ $product->name }} ke keranjang"
                                                class="btn btn-primary btn-sm !min-h-0">+ Tambah</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $products->links('partials.pagination') }}
            </div>
        @endif
    </div>

    <script>
        if (window.EchoEnabled && window.Echo) {
            Echo.channel('order.new').listen('.OrderCreated', (e) => {
                if (e.order_number) {
                    const toast = document.createElement('div');
                    toast.className = 'fixed bottom-4 right-4 z-50 rounded-xl bg-forest-800 px-4 py-3 text-sm text-cream-50 shadow-lg';
                    toast.textContent = 'Order baru: ' + e.order_number;
                    document.body.appendChild(toast);
                    setTimeout(() => toast.remove(), 3000);
                }
            });
        }
    </script>
@endsection