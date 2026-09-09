@extends('layouts.kiosk')

@section('title', isset($category) ? $category->name.' - '.config('app.name') : 'Menu - '.config('app.name'))

@section('content')
    @include('customer._cart-drawer', ['lines' => $drawerLines, 'subtotal' => $drawerSubtotal])

    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-night-800 via-night-900 to-night-950 px-6 py-10 text-stone-100 shadow-lg ring-1 ring-night-700">
        <div class="relative z-10">
            <p class="text-xs font-semibold uppercase tracking-widest text-brand-400">Selamat datang</p>
            <h1 class="mt-1 text-2xl font-bold sm:text-3xl">Silakan pilih menu favorit Anda</h1>
            <p class="mt-2 text-sm text-stone-400">Pesan dari meja, disiapkan dapur, dan diantar saat sudah siap.</p>
        </div>
        <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute -bottom-6 -right-6 h-40 w-40 text-brand-500/20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v10m0 0a4 4 0 100 8c2.21 0 4-1.79 4-4M12 14c.5 1 1.5 2.5 3 2.5a2.5 2.5 0 100-5c-1.5 0-2.5 1-3 2.5z"/></svg>
    </section>

    <nav aria-label="Kategori menu" class="mt-6 -mx-4 overflow-x-auto px-4 pb-1 lg:mx-0 lg:px-0">
        <div class="flex gap-2">
            <a href="{{ route('menu.index') }}"
               class="{{ ! isset($category) ? 'bg-brand-600 text-white' : 'border border-night-600 bg-night-800 text-stone-200 hover:bg-night-700' }} shrink-0 rounded-full px-4 py-2 text-sm font-medium transition">
                Semua Menu
            </a>
            @foreach ($categories as $cat)
                <a href="{{ route('menu.category', $cat) }}"
                   class="{{ $category?->slug === $cat->slug ? 'bg-brand-600 text-white' : 'border border-night-600 bg-night-800 text-stone-200 hover:bg-night-700' }} shrink-0 rounded-full px-4 py-2 text-sm font-medium transition">
                    {{ $cat->name }}
                    <span class="ml-1 {{ $category?->slug === $cat->slug ? 'text-brand-100' : 'text-stone-500' }}">{{ $cat->active_products_count }}</span>
                </a>
            @endforeach
        </div>
    </nav>

    <div class="mt-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-bold text-stone-100">{{ isset($category) ? $category->name : 'Seluruh Menu' }}</h1>
            <form method="GET" action="{{ route('menu.index') }}" class="flex items-center gap-2" role="search">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari menu..." aria-label="Cari menu" class="input max-w-56">
                <button type="submit" class="btn btn-secondary" aria-label="Cari">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </button>
            </form>
        </div>

        @if (! $products->isEmpty() && request('q'))
            <p class="mb-3 text-xs text-stone-500">Hasil pencarian untuk "{{ request('q') }}".</p>
        @endif

        @if ($products->isEmpty())
            <div class="card p-10 text-center">
                <p class="text-sm text-stone-400">Tidak ada menu tersedia saat ini.</p>
                @if (request('q'))
                    <a href="{{ route('menu.index') }}" class="mt-3 inline-block text-sm font-medium text-brand-400 hover:text-brand-300">Hapus pencarian</a>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($products as $product)
                    <article class="card group overflow-hidden transition duration-200 hover:-translate-y-0.5 hover:shadow-lg hover:border-brand-500/40">
                        <div class="relative aspect-[4/3] overflow-hidden bg-night-800">
                            @if ($product->image)
                                <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-night-800 text-night-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif

                            @if ($product->isSoldOut())
                                <div class="absolute inset-0 flex items-center justify-center bg-night-950/60">
                                    <span class="rounded-full bg-brand-600 px-4 py-1.5 text-xs font-bold uppercase tracking-wide text-white">Habis</span>
                                </div>
                            @endif
                        </div>

                        <div class="p-4">
                            <h3 class="text-sm font-semibold text-stone-100">{{ $product->name }}</h3>
                            @if ($product->description)
                                <p class="mt-1 line-clamp-2 text-xs text-stone-500">{{ $product->description }}</p>
                            @endif

                            <div class="mt-4 flex items-center justify-between gap-2">
                                <span class="text-base font-bold text-brand-400">Rp {{ number_format($product->sale_price, 0, ',', '.') }}</span>

                                @php $qty = $cartQuantities[$product->id] ?? 0; @endphp

                                @if ($product->isSoldOut())
                                    <button type="button" disabled aria-label="Stok {{ $product->name }} habis"
                                            class="btn btn-secondary !px-3 !py-1.5 text-xs opacity-60">Habis</button>
                                @elseif ($qty > 0)
                                    <form method="POST" action="{{ route('cart.update', $product->id) }}" class="flex items-center rounded-lg border border-night-600 bg-night-900">
                                        @csrf
                                        <button type="submit" name="quantity" value="{{ $qty - 1 }}" aria-label="Kurangi {{ $product->name }}" class="px-3 py-1.5 text-sm font-semibold text-stone-300 hover:text-brand-400">−</button>
                                        <span class="min-w-7 text-center text-sm font-semibold text-stone-100" aria-label="{{ $qty }} di keranjang">{{ $qty }}</span>
                                        <button type="submit" name="quantity" value="{{ $qty + 1 }}"
                                                @if ($product->isLimitedStock() && $qty >= $product->stock) disabled @endif
                                                aria-label="Tambah {{ $product->name }}" class="px-3 py-1.5 text-sm font-semibold text-stone-300 hover:text-brand-400 disabled:opacity-40">+</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('cart.add') }}" class="flex items-center gap-2">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" aria-label="Tambah {{ $product->name }} ke keranjang"
                                                class="btn btn-primary !px-3 !py-1.5 text-xs">+ Tambah</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <script>
        if (window.EchoEnabled && window.Echo) {
            Echo.channel('order.new').listen('.OrderCreated', (e) => {
                if (e.order_number) {
                    const toast = document.createElement('div');
                    toast.className = 'fixed bottom-4 right-4 z-50 rounded-lg bg-emerald-700 px-4 py-3 text-sm text-white shadow-lg';
                    toast.textContent = 'Order baru: ' + e.order_number;
                    document.body.appendChild(toast);
                    setTimeout(() => toast.remove(), 3000);
                }
            });
        }
    </script>
@endsection