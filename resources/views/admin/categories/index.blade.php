@extends('layouts.app')

@section('title', 'Kategori Menu - ' . config('app.name'))
@section('header', 'Kategori Menu')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Kategori Menu</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">+ Tambah Produk</a>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-secondary">Tambah Kategori</a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.categories.index') }}" class="mb-5">
        <div class="flex items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kategori..." class="input max-w-xs">
            <button type="submit" class="btn btn-secondary">Cari</button>
            @if (request('search'))
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Reset</a>
            @endif
        </div>
    </form>

    <div class="space-y-4">
        @forelse ($categories as $category)
            <div class="card overflow-hidden" x-data="{ open: {{ in_array($category->id, array_map('intval', explode(',', request('open', ''))), true) ? 'true' : 'false' }} }">
                <div class="flex items-center gap-4 p-4 sm:p-5">
                    <div class="h-14 w-20 shrink-0 overflow-hidden rounded-lg border border-night-700 bg-night-800">
                        @if ($category->image)
                            <img src="{{ asset('storage/'.$category->image) }}" alt="{{ $category->name }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-lg font-bold text-gray-900">{{ $category->name }}</h2>
                            @if ($category->is_active)
                                <span class="badge bg-emerald-100 text-emerald-700">Aktif</span>
                            @else
                                <span class="badge bg-gray-100 text-gray-600">Nonaktif</span>
                            @endif
                        </div>
                        @if ($category->description)
                            <p class="mt-1 line-clamp-1 text-sm text-gray-500">{{ $category->description }}</p>
                        @endif
                        <p class="mt-1 text-xs text-gray-500">{{ $category->products_count }} produk</p>
                    </div>

                    <div class="flex shrink-0 flex-wrap items-center justify-end gap-2">
                        <button type="button"
                                class="btn btn-secondary btn-sm"
                                @click="open = !open">
                            <span x-show="!open">Lihat Produk</span>
                            <span x-show="open" x-cloak>Sembunyikan</span>
                        </button>
                        <a href="{{ route('admin.products.create', ['category_id' => $category->id]) }}" class="btn btn-success btn-sm">+ Produk</a>
                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-secondary btn-sm">Edit</a>
                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </div>
                </div>

                <div x-show="open" x-transition x-cloak>
                    <div class="border-t border-night-700 bg-night-800/40 px-4 py-4">
                        @forelse ($category->products as $product)
                            <div class="flex items-center gap-4 rounded-lg border border-night-700 bg-night-850 px-4 py-3 {{ ! $loop->last ? 'mb-2' : '' }}">
                                <div class="h-12 w-16 shrink-0 overflow-hidden rounded-md border border-night-700">
                                    @if ($product->image)
                                        <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-gray-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="truncate text-sm font-semibold text-gray-900">{{ $product->name }}</span>
                                        @if ($product->is_kitchen)
                                            <span class="badge bg-brand-100 text-brand-700">Dapur</span>
                                        @endif
                                        @if (! $product->is_active || ! $product->is_available)
                                            <span class="badge bg-gray-100 text-gray-600">Nonaktif</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500">SKU: {{ $product->sku }} · Harga: Rp {{ number_format((float) $product->sale_price, 0, ',', '.') }}</p>
                                </div>

                                <div class="flex shrink-0 items-center gap-2">
                                    @if ($product->isLimitedStock())
                                        <span class="badge {{ $product->isSoldOut() ? 'bg-red-100 text-red-700' : ($product->stock <= 10 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                            Stok: {{ $product->stock }}
                                        </span>
                                    @endif
                                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-secondary btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="py-4 text-center text-sm text-gray-500">
                                Belum ada produk di kategori ini.
                                <a href="{{ route('admin.products.create', ['category_id' => $category->id]) }}" class="font-medium text-brand-600 hover:underline">Tambah produk</a>.
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>
        @empty
            <div class="card p-8 text-center text-sm text-gray-500">Tidak ada data kategori.</div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $categories->links() }}
    </div>
@endsection