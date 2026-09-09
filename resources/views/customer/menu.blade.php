@extends('layouts.kiosk')

@section('title', isset($category) ? $category->name.' - '.config('app.name') : 'Menu - '.config('app.name'))

@section('content')
    @include('customer._cart-drawer', ['lines' => $drawerLines, 'subtotal' => $drawerSubtotal])

    <div class="grid gap-6 lg:grid-cols-[theme(spacing.64)_1fr]">
        <nav class="hidden lg:block">
            <div class="card overflow-hidden">
                <a href="{{ route('menu.index') }}" class="{{ ! isset($category) ? 'bg-brand-50 font-semibold text-brand-700' : 'text-gray-700' }} block border-b border-gray-100 px-4 py-3 text-sm hover:bg-gray-50">
                    Semua Menu
                </a>
                @foreach ($categories as $category)
                    <a href="{{ route('menu.category', $category) }}" class="{{ $category->slug === request()->route('category')?->slug ? 'bg-brand-50 font-semibold text-brand-700' : 'text-gray-700' }} block border-b border-gray-100 px-4 py-3 text-sm hover:bg-gray-50">
                        {{ $category->name }}
                        <span class="ml-1 text-xs text-gray-400">{{ $category->active_products_count }}</span>
                    </a>
                @endforeach
            </div>
        </nav>

        <div>
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-2xl font-bold text-gray-900">{{ isset($category) ? $category->name : 'Seluruh Menu' }}</h1>
                <form method="GET" action="{{ route('menu.index') }}" class="flex items-center gap-2">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari menu..." class="input max-w-56">
                    <button type="submit" class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                </form>
            </div>

            @if ($products->isEmpty())
                <div class="card p-10 text-center">
                    <p class="text-sm text-gray-500">Tidak ada menu tersedia saat ini.</p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($products as $product)
                        <div class="card overflow-hidden">
                            @if ($product->image)
                                <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="h-40 w-full object-cover">
                            @else
                                <div class="flex h-40 w-full items-center justify-center bg-gray-100 text-gray-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif

                            <div class="p-4">
                                <h3 class="text-sm font-semibold text-gray-900">{{ $product->name }}</h3>
                                @if ($product->description)
                                    <p class="mt-1 line-clamp-2 text-xs text-gray-500">{{ $product->description }}</p>
                                @endif

                                <div class="mt-3 flex items-center justify-between gap-2">
                                    <span class="text-base font-bold text-brand-600">Rp {{ number_format($product->sale_price, 0, ',', '.') }}</span>

                                    <form method="POST" action="{{ route('cart.add') }}" class="flex items-center gap-2">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit"
                                                @if ($product->isSoldOut()) disabled aria-label="Stok {{ $product->name }} habis" @else aria-label="Tambah {{ $product->name }} ke keranjang" @endif
                                                class="btn @if ($product->isSoldOut()) btn-secondary @else btn-primary @endif !px-3 !py-1.5 text-xs">
                                            @if ($product->isSoldOut())
                                                Habis
                                            @else
                                                + Tambah
                                            @endif
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>

    <script>
        if (window.EchoEnabled && window.Echo) {
            Echo.channel('order.new').listen('.OrderCreated', (e) => {
                if (e.order_number) {
                    const toast = document.createElement('div');
                    toast.className = 'fixed bottom-4 right-4 z-50 rounded-lg bg-emerald-600 px-4 py-3 text-sm text-white shadow-lg';
                    toast.textContent = 'Order baru: ' + e.order_number;
                    document.body.appendChild(toast);
                    setTimeout(() => toast.remove(), 3000);
                }
            });
        }
    </script>
@endsection