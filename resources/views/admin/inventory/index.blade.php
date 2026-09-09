@extends('layouts.app')

@section('title', 'Inventori - '.config('app.name'))
@section('header', 'Inventori')

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-gray-900">Inventori</h1>
        <a href="{{ route('admin.inventory.movements') }}" class="btn btn-secondary">Riwayat Pergerakan</a>
    </div>

    <div class="card overflow-hidden">
        <form method="GET" action="{{ route('admin.inventory.index') }}" class="flex flex-wrap items-center gap-3 border-b border-gray-200 px-5 py-4">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari produk..." class="input max-w-56">
            <button type="submit" class="btn btn-secondary">Cari</button>
        </form>

        <div class="overflow-x-auto">
            <table class="table-w">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Produk</th>
                        <th class="px-5 py-3 font-medium">SKU</th>
                        <th class="px-5 py-3 font-medium">Kategori</th>
                        <th class="px-5 py-3 font-medium">Tipe Stok</th>
                        <th class="px-5 py-3 font-medium">Stok</th>
                        <th class="px-5 py-3 font-medium">Sesuaikan Stok</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($products as $product)
                        <tr>
                            <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ $product->name }}</td>
                            <td class="px-5 py-3 text-sm text-gray-600">{{ $product->sku }}</td>
                            <td class="px-5 py-3 text-sm text-gray-600">{{ $product->category?->name ?? '-' }}</td>
                            <td class="px-5 py-3 text-sm text-gray-600">{{ $product->stock_type === 'limited' ? 'Terbatas' : 'Tidak Terbatas' }}</td>
                            <td class="px-5 py-3">
                                @if ($product->stock_type === 'limited')
                                    <span class="badge {{ $product->stock <= 0 ? 'bg-red-100 text-red-700' : ($product->stock <= 10 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                        {{ $product->stock }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">∞</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <form method="POST" action="{{ route('admin.inventory.adjust', $product) }}" class="flex items-end gap-2">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <div>
                                        <input type="number" name="new_stock" min="0" value="{{ $product->stock }}" class="input w-28">
                                    </div>
                                    <div>
                                        <input type="text" name="note" placeholder="Catatan (opsional)" class="input w-40">
                                    </div>
                                    <button type="submit" class="btn btn-secondary !px-3 !py-2 text-xs">Simpan</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500">Tidak ada produk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4">{{ $products->links() }}</div>
    </div>
@endsection