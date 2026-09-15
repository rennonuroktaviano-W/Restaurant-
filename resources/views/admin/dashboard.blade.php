@extends('layouts.app')

@section('title', 'Dashboard - '.config('app.name'))
@section('header', 'Dashboard')

@section('content')
    @php
        $rangeLabels = [
            'today' => 'Hari Ini',
            'yesterday' => 'Kemarin',
            '7d' => '7 Hari Terakhir',
            '30d' => '30 Hari Terakhir',
            'month' => 'Bulan Ini',
            'custom' => 'Rentang Khusus',
        ];
        $currentLabel = $rangeLabels[$range] ?? $rangeLabels['month'];

        $periodStart = $period['start'] instanceof \Illuminate\Support\Carbon ? $period['start'] : \Illuminate\Support\Carbon::parse($period['start']);
        $periodEnd = $period['end'] instanceof \Illuminate\Support\Carbon ? $period['end'] : \Illuminate\Support\Carbon::parse($period['end']);

        $stats = [
            [
                'label' => 'Revenue',
                'value' => 'Rp '.number_format($grossSales, 0, ',', '.'),
                'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
                'iconBg' => 'bg-emerald-100 text-emerald-700',
                'trend' => $saleTrend,
                'prev' => $grossSalesPrev > 0 ? 'Rp '.number_format($grossSalesPrev, 0, ',', '.') : 'Tidak ada data sebelumnya',
            ],
            [
                'label' => 'Total Orders',
                'value' => number_format($orderCount),
                'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
                'iconBg' => 'bg-blue-100 text-blue-700',
                'trend' => $orderTrend,
                'prev' => $orderCountPrev > 0 ? number_format($orderCountPrev) : 'Tidak ada data sebelumnya',
            ],
            [
                'label' => 'Avg Order Value',
                'value' => 'Rp '.number_format($avgOrderValue, 0, ',', '.'),
                'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                'iconBg' => 'bg-brand-100 text-brand-700',
                'trend' => $avgTrend,
                'prev' => $avgOrderValuePrev > 0 ? 'Rp '.number_format($avgOrderValuePrev, 0, ',', '.') : 'Tidak ada data sebelumnya',
            ],
            [
                'label' => 'Cancelled',
                'value' => number_format($cancelledOrders),
                'icon' => 'M6 18L18 6M6 6l12 12',
                'iconBg' => 'bg-red-100 text-red-700',
                'trend' => null,
                'prev' => 'Order dibatalkan',
            ],
            [
                'label' => 'Pending',
                'value' => number_format($pendingOrders),
                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                'iconBg' => 'bg-amber-100 text-amber-700',
                'trend' => null,
                'prev' => 'Order diproses',
            ],
        ];
    @endphp

    <!-- Period Filter -->
    <div class="card mb-5 p-5">
        <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[180px]">
                <label for="range" class="label">Periode</label>
                <select id="range" name="range" class="select" onchange="this.form.submit()">
                    <option value="today" {{ $range === 'today' ? 'selected' : '' }}>Hari Ini</option>
                    <option value="yesterday" {{ $range === 'yesterday' ? 'selected' : '' }}>Kemarin</option>
                    <option value="7d" {{ $range === '7d' ? 'selected' : '' }}>7 Hari Terakhir</option>
                    <option value="30d" {{ $range === '30d' ? 'selected' : '' }}>30 Hari Terakhir</option>
                    <option value="month" {{ $range === 'month' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="custom" {{ $range === 'custom' ? 'selected' : '' }}>Rentang Khusus</option>
                </select>
            </div>

            @if ($range === 'custom')
                <div>
                    <label for="date_from" class="label">Dari</label>
                    <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" max="{{ now()->format('Y-m-d') }}" class="input" required>
                </div>
                <div>
                    <label for="date_to" class="label">Sampai</label>
                    <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" max="{{ now()->format('Y-m-d') }}" class="input" required>
                </div>
            @endif

            <div class="flex gap-2">
                @if ($range !== 'custom')
                    <a href="{{ route('admin.dashboard', ['range' => 'custom']) }}" class="btn btn-secondary">Custom Range</a>
                @endif
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Reset</a>
            </div>

            <div class="flex-1 min-w-[180px] text-sm text-gray-500">
                <p class="mt-1">Menampilkan: <span class="font-medium text-gray-900">{{ $currentLabel }}</span>
                    @if ($range === 'custom')
                        ({{ $periodStart->format('d M Y') }} - {{ $periodEnd->format('d M Y') }})
                    @else
                        ({{ $periodStart->format('d M Y') }} s/d {{ $periodEnd->format('d M Y') }})
                    @endif
                </p>
            </div>
        </form>
    </div>

    <!-- KPI Cards -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        @foreach ($stats as $stat)
            <div class="card p-5">
                <div class="flex items-start justify-between">
                    <span class="flex h-11 w-11 items-center justify-center rounded-lg {{ $stat['iconBg'] }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $stat['icon'] }}"/></svg>
                    </span>
                    @if ($stat['trend'] !== null)
                        <span class="badge {{ $stat['trend'] >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}" aria-label="{{ $stat['trend'] >= 0 ? 'Naik' : 'Turun' }} {{ abs($stat['trend']) }}%">
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

    <!-- Revenue Chart + Status Distribution -->
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="card overflow-hidden">
            <div class="border-b border-gray-200 px-5 py-4">
                <h2 class="text-base font-semibold text-gray-900">Revenue Harian</h2>
            </div>
            <div class="p-5">
                @if ($revenueByDay->isEmpty())
                    <p class="text-sm text-gray-500 text-center py-8">Belum ada data revenue untuk periode ini.</p>
                @else
                    @php
                        $maxRevenue = $revenueByDay->max('total');
                        $dates = [];
                        $current = $periodStart->copy();
                        while ($current->lte($periodEnd)) {
                            $dates[$current->format('Y-m-d')] = $current->format('d M');
                            $current->addDay();
                        }
                    @endphp
                    <div class="space-y-3" role="img" aria-label="Grafik revenue harian">
                        @foreach ($dates as $dateKey => $dateLabel)
                            @php
                                $dayData = $revenueByDay->get($dateKey);
                                $total = $dayData->total ?? 0;
                                $count = $dayData->count ?? 0;
                                $pct = $maxRevenue > 0 ? round(($total / $maxRevenue) * 100) : 0;
                            @endphp
                            <div>
                                <div class="mb-1 flex items-center justify-between text-sm">
                                    <span class="text-gray-700 w-20 truncate">{{ $dateLabel }}</span>
                                    <span class="font-semibold text-gray-900 whitespace-nowrap">Rp {{ number_format($total, 0, ',', '.') }}</span>
                                    <span class="text-xs text-gray-500">{{ $count }} order</span>
                                </div>
                                <div class="h-3 w-full overflow-hidden rounded-full bg-gray-200">
                                    <div class="h-full rounded-full bg-emerald-500 transition-all" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-3 text-center text-xs text-gray-500" aria-live="polite">Total revenue periode ini: <strong>Rp {{ number_format($grossSales, 0, ',', '.') }}</strong></p>
                @endif
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="border-b border-gray-200 px-5 py-4">
                <h2 class="text-base font-semibold text-gray-900">Distribusi Status Order</h2>
            </div>
            <div class="p-5">
                @if ($statusDistribution->isEmpty())
                    <p class="text-sm text-gray-500">Belum ada data untuk periode ini.</p>
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
    </div>

    <!-- Top Products + Payment Analytics -->
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="card overflow-hidden">
            <div class="border-b border-gray-200 px-5 py-4">
                <h2 class="text-base font-semibold text-gray-900">Top 5 Produk</h2>
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
                                    <span class="font-medium text-gray-700 truncate pr-2">{{ $row->product_name }}</span>
                                    <span class="font-semibold text-gray-900 whitespace-nowrap">{{ $row->qty }} terjual</span>
                                    <span class="text-xs text-gray-500 whitespace-nowrap">Rp {{ number_format($row->revenue ?? 0, 0, ',', '.') }}</span>
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

        <div class="card overflow-hidden">
            <div class="border-b border-gray-200 px-5 py-4">
                <h2 class="text-base font-semibold text-gray-900">Metode Pembayaran</h2>
            </div>
            <div class="p-5">
                @if ($paymentMix->isEmpty())
                    <p class="text-sm text-gray-500">Belum ada transaksi.</p>
                @else
                    @php
                        $totalPaid = $paymentMix->sum('total');
                        $totalCount = $paymentMix->sum('count');
                        $payColors = ['bg-emerald-100 text-emerald-700', 'bg-blue-100 text-blue-700', 'bg-amber-100 text-amber-700', 'bg-purple-100 text-purple-700', 'bg-rose-100 text-rose-700'];
                    @endphp
                    <div class="space-y-2">
                        @foreach ($paymentMix as $i => $row)
                            @php
                                $pctAmount = $totalPaid > 0 ? round(($row->total / $totalPaid) * 100) : 0;
                                $pctCount = $totalCount > 0 ? round(($row->count / $totalCount) * 100) : 0;
                                $color = $payColors[$i % count($payColors)];
                            @endphp
                            <li class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 text-sm">
                                <span class="badge {{ $color }}">{{ $row->name }}</span>
                                <div class="text-right">
                                    <span class="font-semibold text-gray-900">
                                        Rp {{ number_format($row->total, 0, ',', '.') }}
                                        <span class="ml-1 text-xs font-normal text-gray-500">({{ $pctAmount }}%)</span>
                                    </span>
                                    <span class="block text-xs text-gray-500">{{ $row->count }} transaksi ({{ $pctCount }}%)</span>
                                </div>
                            </li>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Peak Hours + Discount/Refund Insights -->
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="card overflow-hidden">
            <div class="border-b border-gray-200 px-5 py-4">
                <h2 class="text-base font-semibold text-gray-900">Jam Sibuk (Peak Hours)</h2>
            </div>
            <div class="p-5">
                @if ($peakHours->isEmpty())
                    <p class="text-sm text-gray-500 text-center py-8">Belum ada data order selesai untuk periode ini.</p>
                @else
                    @php
                        $maxHour = $peakHours->max();
                        $hours = range(0, 23);
                    @endphp
                    <div class="space-y-2" role="img" aria-label="Grafik jam sibuk">
                        @foreach ($hours as $h)
                            @php
                                $count = $peakHours->get($h, 0);
                                $pct = $maxHour > 0 ? round(($count / $maxHour) * 100) : 0;
                                $label = str_pad($h, 2, '0', STR_PAD_LEFT).':00';
                            @endphp
                            <div class="flex items-center gap-3">
                                <span class="w-14 text-xs text-gray-500 font-mono">{{ $label }}</span>
                                <div class="flex-1 h-3 overflow-hidden rounded-full bg-gray-200">
                                    <div class="h-full rounded-full bg-orange-500 transition-all" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="w-12 text-right text-sm font-medium text-gray-900">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="border-b border-gray-200 px-5 py-4">
                <h2 class="text-base font-semibold text-gray-900">Diskon & Refund</h2>
            </div>
            <div class="p-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="card bg-gray-50 p-4">
                        <p class="text-xs text-gray-500">Total Diskon</p>
                        <p class="mt-1 text-lg font-bold text-gray-900">Rp {{ number_format($discountAmount, 0, ',', '.') }}</p>
                        @if ($discountTrend !== null)
                            <p class="mt-1 text-xs {{ $discountTrend >= 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                {{ $discountTrend >= 0 ? '▲' : '▼' }} {{ number_format(abs($discountTrend), 1) }}% vs periode sebelumnya
                            </p>
                        @endif
                    </div>
                    <div class="card bg-gray-50 p-4">
                        <p class="text-xs text-gray-500">Jumlah Refund</p>
                        <p class="mt-1 text-lg font-bold text-gray-900">{{ $refundCount }}</p>
                        @if ($refundCountTrend !== null)
                            <p class="mt-1 text-xs {{ $refundCountTrend >= 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                {{ $refundCountTrend >= 0 ? '▲' : '▼' }} {{ number_format(abs($refundCountTrend), 1) }}%
                            </p>
                        @endif
                    </div>
                    <div class="card bg-gray-50 p-4">
                        <p class="text-xs text-gray-500">Total Refund</p>
                        <p class="mt-1 text-lg font-bold text-red-600">Rp {{ number_format($refundTotal, 0, ',', '.') }}</p>
                        @if ($refundTotalTrend !== null)
                            <p class="mt-1 text-xs {{ $refundTotalTrend >= 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                {{ $refundTotalTrend >= 0 ? '▲' : '▼' }} {{ number_format(abs($refundTotalTrend), 1) }}%
                            </p>
                        @endif
                    </div>
                    <div class="card bg-gray-50 p-4">
                        <p class="text-xs text-gray-500">Net Sales</p>
                        @php $netSales = $grossSales - $refundTotal; @endphp
                        <p class="mt-1 text-lg font-bold text-emerald-700">Rp {{ number_format($netSales, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock + Recent Orders -->
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="card overflow-hidden">
            <div class="border-b border-gray-200 px-5 py-4">
                <h2 class="text-base font-semibold text-gray-900">Stok Menipis (≤10)</h2>
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

        <div class="card overflow-hidden">
            <div class="border-b border-gray-200 px-5 py-4">
                <h2 class="text-base font-semibold text-gray-900">Order Terbaru (7 Hari)</h2>
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
@endsection