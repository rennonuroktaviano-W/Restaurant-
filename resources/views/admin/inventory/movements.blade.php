@extends('layouts.app')

@section('title', 'Riwayat Pergerakan Stok - '.config('app.name'))
@section('header', 'Inventori')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Riwayat Pergerakan Stok</h1>
        <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">&larr; Kembali ke Inventori</a>
    </div>

    <div class="card overflow-hidden">
        <form method="GET" action="{{ route('admin.inventory.movements') }}" class="flex flex-wrap items-end gap-3 border-b border-gray-200 px-5 py-4">
            <div>
                <label for="product" class="label">Produk</label>
                <input id="product" type="search" name="product" value="{{ request('product') }}" placeholder="Nama/sku produk..." class="input w-48">
            </div>
            <div>
                <label for="type" class="label">Tipe</label>
                <select id="type" name="type" class="select w-40">
                    <option value="">Semua</option>
                    @foreach (['IN', 'OUT', 'ADJUSTMENT', 'REVERSAL', 'TRANSFER'] as $type)
                        <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>
                            {{ $type === 'IN' ? 'Masuk' : ($type === 'OUT' ? 'Keluar' : ($type === 'TRANSFER' ? 'Transfer' : ($type === 'REVERSAL' ? 'Pengembalian' : 'Penyesuaian'))) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="warehouse" class="label">Gudang</label>
                <select id="warehouse" name="warehouse" class="select w-44">
                    <option value="">Semua</option>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" {{ request('warehouse') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="date" class="label">Tanggal</label>
                <input id="date" type="date" name="date" value="{{ request('date') }}" class="input w-44">
            </div>
            <button type="submit" class="btn btn-secondary">Filter</button>
            @if (request()->has('product') || request('type') || request('warehouse') || request('date'))
                <a href="{{ route('admin.inventory.movements') }}" class="btn btn-secondary">Reset</a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="table-w">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Waktu</th>
                        <th class="px-5 py-3 font-medium">Produk</th>
                        <th class="px-5 py-3 font-medium">Tipe</th>
                        <th class="px-5 py-3 font-medium">Perubahan</th>
                        <th class="px-5 py-3 font-medium">Stok Akhir</th>
                        <th class="px-5 py-3 font-medium">Gudang</th>
                        <th class="px-5 py-3 font-medium">Referensi</th>
                        <th class="px-5 py-3 font-medium">Catatan</th>
                        <th class="px-5 py-3 font-medium">Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($movements as $movement)
                        <tr>
                            <td class="px-5 py-3 text-sm text-gray-600">{{ $movement->created_at?->format('d M Y H:i') }}</td>
                            <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ $movement->product?->name ?? '#' . $movement->product_id }}</td>
                            <td class="px-5 py-3">
                                @php
                                    $typeMeta = [
                                        'IN' => ['Masuk', 'bg-emerald-100 text-emerald-700'],
                                        'OUT' => ['Keluar', 'bg-red-100 text-red-700'],
                                        'ADJUSTMENT' => ['Penyesuaian', 'bg-blue-100 text-blue-700'],
                                        'REVERSAL' => ['Pengembalian', 'bg-amber-100 text-amber-700'],
                                        'TRANSFER' => ['Transfer', 'bg-purple-100 text-purple-700'],
                                    ][$movement->type] ?? [$movement->type, 'bg-gray-100 text-gray-600'];
                                @endphp
                                <span class="badge {{ $typeMeta[1] }}">{{ $typeMeta[0] }}</span>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-700">
                                @if ($movement->type === 'OUT')
                                    −{{ $movement->quantity }}
                                @elseif ($movement->type === 'ADJUSTMENT')
                                    {{ $movement->quantity >= 0 ? '+' : '' }}{{ $movement->quantity }}
                                @else
                                    +{{ $movement->quantity }}
                                @endif
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-900">{{ $movement->after }}</td>
                            <td class="px-5 py-3 text-sm text-gray-600">
                                @if ($movement->type === 'TRANSFER' && $movement->toWarehouse)
                                    {{ $movement->warehouse?->name }} &rarr; {{ $movement->toWarehouse->name }}
                                @else
                                    {{ $movement->warehouse?->name ?? '-' }}
                                @endif
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-600">
                                @if ($movement->reference_type === 'Supplier' && $movement->supplier)
                                    {{ $movement->supplier->name }}
                                @elseif ($movement->reference_type !== 'NONE')
                                    {{ strtoupper($movement->reference_type) }} #{{ $movement->reference_id ?? '' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500">{{ $movement->note ?? '-' }}</td>
                            <td class="px-5 py-3 text-sm text-gray-600">{{ $movement->actor?->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-10 text-center text-sm text-gray-500">Belum ada pergerakan stok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4">{{ $movements->links() }}</div>
    </div>
@endsection