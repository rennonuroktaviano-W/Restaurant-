@extends('layouts.app')

@section('title', 'Dashboard - '.config('app.name'))
@section('header', 'Dashboard')

@section('content')
    @php
        $stats = [
            [
                'label' => 'Penjualan Bulan Ini',
                'value' => 'Rp '.number_format($grossSales, 0, ',', '.'),
                'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
                'iconBg' => 'bg-emerald-100 text-emerald-700',
                'trend' => $saleTrend,
                'prev' => 'Bulan lalu: Rp '.number_format($grossSalesPrev, 0, ',', '.'),
            ],
            [
                'label' => 'Order Selesai',
                'value' => number_format($orderCount),
                'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
                'iconBg' => 'bg-blue-100 text-blue-700',
                'trend' => $orderTrend,
                'prev' => 'Bulan lalu: '.number_format($orderCountPrev),
            ],
            [
                'label' => 'Rata-rata Nilai Order',
                'value' => 'Rp '.number_format($avgOrderValue, 0, ',', '.'),
                'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                'iconBg' => 'bg-brand-100 text-brand-700',
                'trend' => $avgTrend,
                'prev' => 'Bulan lalu: Rp '.number_format($avgOrderValuePrev, 0, ',', '.'),
            ],
            [
                'label' => 'Produk Menipis',
                'value' => number_format($lowStock->count()),
                'icon' => 'M12 9v2m0 4h.01M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
                'iconBg' => 'bg-red-100 text-red-700',
                'trend' => null,
                'prev' => 'Di bawah ambang 10',
            ],
        ];
    @endphp

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
            <div class="card p-5">
                <div class="flex items-start justify-between">
                    <span class="flex h-11 w-11 items-center justify-center rounded-lg {{ $stat['iconBg'] }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $stat['icon'] }}"/></svg>
                    </span>
                    @if ($stat['trend'] !== null)
                        <span class="badge {{ $stat['trend'] >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                            {{ $stat['trend'] >= 0 ? '▲' : '▼' }} {{ number_format(abs($stat['trend']), 1) }}%
                        </span>
                    @endif
                </div>
                <p class="mt-3 text-sm text-gray-500">{{ $stat['label'] }}</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $stat['value'] }}</p>
                <p class="mt-1 text-xs text-gray-500">{{ $stat['prev'] }}</p>
            </div>
        @endforeach
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
                    @php
                        $max = max($statusDistribution->values()->all());
                        $statusMeta = [
                            'new' => ['Menunggu', 'bg-amber-500'],
                            'accepted' => ['Diterima', 'bg-blue-500'],
                            'cooking' => ['Dimasak', 'bg-orange-500'],
                            'ready' => ['Siap', 'bg-brand-500'],
                            'completed' => ['Selesai', 'bg-emerald-500'],
                            'cancelled' => ['Dibatalkan', 'bg-red-500'],
                        ];
                    @endphp
                    <div class="space-y-3">
                        @foreach ($statusMeta as $status => [$label, $barColor])
                            @if (isset($statusDistribution[$status]))
                                @php
                                    $count = $statusDistribution[$status];
                                    $pct = $max > 0 ? round(($count / $max) * 100) : 0;
                                @endphp
                                <div>
                                    <div class="mb-1 flex items-center justify-between text-sm">
                                        <span class="text-gray-700">{{ $label }}</span>
                                        <span class="font-semibold text-gray-900">{{ $count }}</span>
                                    </div>
                                    <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200">
                                        <div class="h-full rounded-full {{ $barColor }} transition-all" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
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
                    @php $maxQty = max($topProducts->pluck('qty')->all()); @endphp
                    <div class="space-y-3">
                        @foreach ($topProducts as $row)
                            @php $pct = $maxQty > 0 ? round(($row->qty / $maxQty) * 100) : 0; @endphp
                            <div>
                                <div class="mb-1 flex items-center justify-between text-sm">
                                    <span class="font-medium text-gray-700">{{ $row->product_name }}</span>
                                    <span class="font-semibold text-gray-900">{{ $row->qty }} terjual</span>
                                </div>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200">
                                    <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-brand-400 transition-all" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
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
                    @php
                        $totalPaid = $paymentMix->sum('total');
                        $payColors = ['bg-emerald-100 text-emerald-700', 'bg-blue-100 text-blue-700', 'bg-amber-100 text-amber-700', 'bg-purple-100 text-purple-700', 'bg-rose-100 text-rose-700'];
                    @endphp
                    <div class="space-y-2">
                        @foreach ($paymentMix as $i => $row)
                            @php
                                $pct = $totalPaid > 0 ? round(($row->total / $totalPaid) * 100) : 0;
                                $color = $payColors[$i % count($payColors)];
                            @endphp
                            <li class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 text-sm">
                                <span class="badge {{ $color }}">{{ $row->name }}</span>
                                <span class="font-semibold text-gray-900">
                                    Rp {{ number_format($row->total, 0, ',', '.') }}
                                    <span class="ml-1 text-xs font-normal text-gray-500">({{ $pct }}%)</span>
                                </span>
                            </li>
                        @endforeach
                    </div>
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