@extends('layouts.app')

@section('title', 'Dashboard - '.config('app.name'))
@section('header', 'Dashboard')

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card p-5">
            <p class="text-sm text-gray-500">Penjualan Bulan Ini</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">Rp {{ number_format($grossSales, 0, ',', '.') }}</p>
        </div>
        <div class="card p-5">
            <p class="text-sm text-gray-500">Order Selesai</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $orderCount }}</p>
        </div>
        <div class="card p-5">
            <p class="text-sm text-gray-500">Rata-rata Nilai Order</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">Rp {{ number_format($avgOrderValue, 0, ',', '.') }}</p>
        </div>
        <div class="card p-5">
            <p class="text-sm text-gray-500">Produk Menipis</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $lowStock->count() }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="card overflow-hidden">
            <div class="border-b border-gray-200 px-5 py-4">
                <h2 class="text-base font-semibold text-gray-900">Distribusi Status Order</h2>
            </div>
            <div class="p-5">
                @if ($statusDistribution->isEmpty())
                    <p class="text-sm text-gray-500">Belum ada data bulan ini.</p>
                @else
                    <ul class="space-y-2">
                        @foreach (['new' => 'Menunggu', 'accepted' => 'Diterima', 'cooking' => 'Dimasak', 'ready' => 'Siap', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'] as $status => $label)
                            @if (isset($statusDistribution[$status]))
                                <li class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 text-sm">
                                    <span class="text-gray-700">{{ $label }}</span>
                                    <span class="font-semibold text-gray-900">{{ $statusDistribution[$status] }}</span>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="border-b border-gray-200 px-5 py-4">
                <h2 class="text-base font-semibold text-gray-900">Produk Terlaris (Bulan Ini)</h2>
            </div>
            <div class="p-5">
                @if ($topProducts->isEmpty())
                    <p class="text-sm text-gray-500">Belum ada data.</p>
                @else
                    <table class="table-w">
                        <thead class="text-left text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-2 py-2 font-medium">Produk</th>
                                <th class="px-2 py-2 text-right font-medium">Qty</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($topProducts as $row)
                                <tr>
                                    <td class="px-2 py-2 text-sm text-gray-700">{{ $row->product_name }}</td>
                                    <td class="px-2 py-2 text-right text-sm font-medium text-gray-900">{{ $row->qty }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="card overflow-hidden">
            <div class="border-b border-gray-200 px-5 py-4">
                <h2 class="text-base font-semibold text-gray-900">Mix Pembayaran</h2>
            </div>
            <div class="p-5">
                @if ($paymentMix->isEmpty())
                    <p class="text-sm text-gray-500">Belum ada transaksi.</p>
                @else
                    <ul class="space-y-2">
                        @foreach ($paymentMix as $row)
                            <li class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 text-sm">
                                <span class="text-gray-700">{{ $row->name }}</span>
                                <span class="font-semibold text-gray-900">Rp {{ number_format($row->total, 0, ',', '.') }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="border-b border-gray-200 px-5 py-4">
                <h2 class="text-base font-semibold text-gray-900">Order Terbaru</h2>
            </div>
            <div class="p-5">
                @if ($recentOrders->isEmpty())
                    <p class="text-sm text-gray-500">Belum ada order.</p>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach ($recentOrders as $order)
                            <li class="flex items-center justify-between gap-3 py-2">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $order->order_number }}</p>
                                    <p class="text-xs text-gray-500">{{ $order->ordered_at?->format('d M H:i') }} · {{ $order->locationLabel() }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-900">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</p>
                                    <span class="badge bg-blue-100 text-blue-700">{{ \App\Models\Order::$flowLabels[$order->order_status] ?? $order->order_status }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-6 card overflow-hidden">
        <div class="border-b border-gray-200 px-5 py-4">
            <h2 class="text-base font-semibold text-gray-900">Stok Menipis</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="table-w">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Produk</th>
                        <th class="px-5 py-3 font-medium">SKU</th>
                        <th class="px-5 py-3 font-medium">Stok</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($lowStock as $product)
                        <tr>
                            <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ $product->name }}</td>
                            <td class="px-5 py-3 text-sm text-gray-600">{{ $product->sku }}</td>
                            <td class="px-5 py-3">
                                <span class="badge {{ $product->stock > 0 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $product->stock }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-10 text-center text-sm text-gray-500">Semua stok aman.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection