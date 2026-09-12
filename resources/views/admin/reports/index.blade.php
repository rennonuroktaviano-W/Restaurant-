@extends('layouts.app')

@section('title', 'Laporan Penjualan - '.config('app.name'))
@section('header', 'Laporan')

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Laporan Penjualan</h1>
            <p class="mt-1 text-sm text-gray-500">
                Periode {{ ($filters['date_from'] ?? 'Awal') }} s/d {{ ($filters['date_to'] ?? 'Sekarang') }}
                &middot; {{ $orderCount }} order selesai
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.reports.excel', request()->query()) }}" class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-1.5 h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M5 2a2 2 0 00-2 2v16a2 2 0 002 2h14a2 2 0 002-2V4a2 2 0 00-2-2H5zm1.2 5l2.3 3.4L6.2 13.7h1.9l1.2-1.9 1.2 1.9h2L10 10.4 12.4 7h-1.9L9.3 8.9 8.1 7H6.2zm7 6.7V12h2.4v1.7H15.4v-1.2h-1.3l.8 1.2h1.5l-1.9 2.2h1.9v1.3h-2.6v-1.2h-.8v1.2h-1.6v1.4H11v-9.4h1.6v1.5h.6V11h1.8v1.9zm-5.6 1.6v1.3h2v1.3H4.6v-9.4h2v5.5h2z"/></svg>
                Export Excel
            </a>
            <a href="{{ route('admin.reports.pdf', request()->query()) }}" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 17l-2 4h18l2-4m-13-6h4m-4 3h6M6 7V3h12v4M6 7h2m8 0h2"/></svg>
                Download PDF
            </a>
        </div>
    </div>

    <div class="card mb-6 p-5">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="label" for="f-report-search">Pencarian</label>
                <input id="f-report-search" type="search" name="search" value="{{ $filters['search'] }}" placeholder="Nomor order / nama pelanggan..." class="input">
            </div>
            <div>
                <label class="label" for="f-report-from">Dari Tanggal</label>
                <input id="f-report-from" type="date" name="date_from" value="{{ $filters['date_from'] }}" class="input">
            </div>
            <div>
                <label class="label" for="f-report-to">Sampai Tanggal</label>
                <input id="f-report-to" type="date" name="date_to" value="{{ $filters['date_to'] }}" class="input">
            </div>
            <div>
                <label class="label" for="f-report-area">Area</label>
                <select id="f-report-area" name="area_id" class="select">
                    <option value="">Semua Area</option>
                    @foreach ($areas as $area)
                        <option value="{{ $area->id }}" {{ $filters['area_id'] == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label" for="f-report-type">Tipe Order</label>
                <select id="f-report-type" name="order_type" class="select">
                    <option value="">Semua</option>
                    <option value="dine_in" {{ $filters['order_type'] === 'dine_in' ? 'selected' : '' }}>Dine In</option>
                    <option value="take_away" {{ $filters['order_type'] === 'take_away' ? 'selected' : '' }}>Take Away</option>
                    <option value="room_service" {{ $filters['order_type'] === 'room_service' ? 'selected' : '' }}>Room Service</option>
                </select>
            </div>
            <div>
                <label class="label" for="f-report-status">Status</label>
                <select id="f-report-status" name="status" class="select">
                    <option value="">Semua</option>
                    @foreach (\App\Models\Order::$flowLabels as $value => $label)
                        <option value="{{ $value }}" {{ $filters['status'] === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label" for="f-report-cashier">Kasir</label>
                <select id="f-report-cashier" name="cashier_id" class="select">
                    <option value="">Semua</option>
                    @foreach ($cashiers as $cashier)
                        <option value="{{ $cashier->id }}" {{ $filters['cashier_id'] == $cashier->id ? 'selected' : '' }}>{{ $cashier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label" for="f-report-method">Metode Bayar</label>
                <select id="f-report-method" name="payment_method_id" class="select">
                    <option value="">Semua</option>
                    @foreach ($paymentMethods as $method)
                        <option value="{{ $method->id }}" {{ $filters['payment_method_id'] == $method->id ? 'selected' : '' }}>{{ $method->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-7">
        <div class="card p-4">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2"/></svg>
                </span>
                <p class="text-xs text-gray-500">Penjualan Kotor</p>
            </div>
            <p class="mt-2 text-lg font-bold text-gray-900">Rp {{ number_format($grossSales, 0, ',', '.') }}</p>
        </div>
        <div class="card p-4">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-100 text-red-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h2m4 0h2M9 21h6a2 2 0 002-2V7a2 2 0 00-2-2h-1V3m-6 2h8"/></svg>
                </span>
                <p class="text-xs text-gray-500">Refund</p>
            </div>
            <p class="mt-2 text-lg font-bold text-red-600">- Rp {{ number_format($refundTotal, 0, ',', '.') }}</p>
        </div>
        <div class="card p-4">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5 0h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </span>
                <p class="text-xs text-gray-500">Penjualan Bersih</p>
            </div>
            <p class="mt-2 text-lg font-bold text-emerald-700">Rp {{ number_format($netSales, 0, ',', '.') }}</p>
        </div>
        <div class="card p-4">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
                </span>
                <p class="text-xs text-gray-500">Order Selesai</p>
            </div>
            <p class="mt-2 text-lg font-bold text-gray-900">{{ $orderCount }}</p>
        </div>
        <div class="card p-4">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-100 text-red-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/></svg>
                </span>
                <p class="text-xs text-gray-500">Dibatalkan</p>
            </div>
            <p class="mt-2 text-lg font-bold text-gray-900">{{ $cancelledCount }}</p>
        </div>
        <div class="card p-4">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-100 text-brand-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10"/></svg>
                </span>
                <p class="text-xs text-gray-500">Rata-rata Order</p>
            </div>
            <p class="mt-2 text-lg font-bold text-gray-900">Rp {{ number_format($avgOrderValue, 0, ',', '.') }}</p>
        </div>
        <div class="card p-4">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-100 text-purple-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h2m4 0h2m-8 6h6a2 2 0 002-2v-4H5v4a2 2 0 002 2z"/></svg>
                </span>
                <p class="text-xs text-gray-500">Metode Teratas</p>
            </div>
            <p class="mt-2 truncate text-lg font-bold text-gray-900">{{ $paymentMix->first()?->name ?? '-' }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="card overflow-hidden">
            <div class="border-b border-gray-200 px-5 py-4"><h2 class="text-base font-semibold text-gray-900">Status Order</h2></div>
            <div class="p-5">
                <ul class="space-y-3">
                    @php
                        $max = $statusDistribution->isNotEmpty() ? max($statusDistribution->values()->all()) : 0;
                        $barColors = [
                            'new' => 'bg-amber-500',
                            'accepted' => 'bg-blue-500',
                            'cooking' => 'bg-orange-500',
                            'ready' => 'bg-brand-500',
                            'completed' => 'bg-emerald-500',
                            'cancelled' => 'bg-red-500',
                        ];
                    @endphp
                    @foreach (\App\Models\Order::$flowLabels as $value => $label)
                        @if (isset($statusDistribution[$value]))
                            @php $pct = $max > 0 ? round(($statusDistribution[$value] / $max) * 100) : 0; @endphp
                            <li>
                                <div class="mb-1 flex justify-between text-sm">
                                    <span class="text-gray-700">{{ $label }}</span>
                                    <span class="font-semibold text-gray-900">{{ $statusDistribution[$value] }}</span>
                                </div>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200">
                                    <div class="h-full rounded-full {{ $barColors[$value] ?? 'bg-gray-400' }}" style="width: {{ $pct }}%"></div>
                                </div>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="card overflow-hidden">
            <div class="border-b border-gray-200 px-5 py-4"><h2 class="text-base font-semibold text-gray-900">Produk Teratas</h2></div>
            <div class="p-5">
                <table class="table-w">
                    <thead class="text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-2 py-2 font-medium">Produk</th>
                            <th class="px-2 py-2 text-right font-medium">Qty</th>
                            <th class="px-2 py-2 text-right font-medium">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($topProducts as $row)
                            <tr>
                                <td class="px-2 py-2 text-sm text-gray-700">{{ $row->product_name }}</td>
                                <td class="px-2 py-2 text-right text-sm text-gray-700">{{ $row->qty }}</td>
                                <td class="px-2 py-2 text-right text-sm font-medium text-gray-900">Rp {{ number_format($row->revenue, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-6 card overflow-hidden">
        <div class="border-b border-gray-200 px-5 py-4"><h2 class="text-base font-semibold text-gray-900">Daftar Order</h2></div>
        <div class="overflow-x-auto">
            <table class="table-w">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Order</th>
                        <th class="px-4 py-3 font-medium">Tanggal</th>
                        <th class="px-4 py-3 font-medium">Tipe / Lokasi</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Pembayaran</th>
                        <th class="px-4 py-3 text-right font-medium">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($orders as $order)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $order->order_number }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $order->ordered_at?->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ ['dine_in' => 'Dine In', 'take_away' => 'Take Away', 'room_service' => 'Room Service'][$order->order_type] }}
                                @if ($order->area)
                                    · {{ $order->area->name }} / {{ $order->locationLabel() }}
                                @endif
                            </td>
                            <td class="px-4 py-3"><span class="badge bg-blue-100 text-blue-700">{{ \App\Models\Order::$flowLabels[$order->order_status] ?? $order->order_status }}</span></td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                <span class="badge {{ $order->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">{{ ['paid' => 'Lunas', 'pending' => 'Belum', 'failed' => 'Gagal', 'expired' => 'Kadaluarsa'][$order->payment_status] ?? $order->payment_status }}</span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">Tidak ada order pada filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-4">{{ $orders->links() }}</div>
    </div>
@endsection