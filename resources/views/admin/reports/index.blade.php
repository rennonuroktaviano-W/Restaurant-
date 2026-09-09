@extends('layouts.app')

@section('title', 'Laporan Penjualan - '.config('app.name'))
@section('header', 'Laporan')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Laporan Penjualan</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.reports.export', request()->query()) }}" class="btn btn-secondary">Export CSV</a>
            <a href="{{ route('admin.reports.pdf', request()->query()) }}" class="btn btn-primary">Cetak PDF</a>
        </div>
    </div>

    <div class="card mb-6 p-5">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="label">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="input">
            </div>
            <div>
                <label class="label">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="input">
            </div>
            <div>
                <label class="label">Area</label>
                <select name="area_id" class="select">
                    <option value="">Semua Area</option>
                    @foreach ($areas as $area)
                        <option value="{{ $area->id }}" {{ $filters['area_id'] == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">Tipe Order</label>
                <select name="order_type" class="select">
                    <option value="">Semua</option>
                    <option value="dine_in" {{ $filters['order_type'] === 'dine_in' ? 'selected' : '' }}>Dine In</option>
                    <option value="take_away" {{ $filters['order_type'] === 'take_away' ? 'selected' : '' }}>Take Away</option>
                    <option value="room_service" {{ $filters['order_type'] === 'room_service' ? 'selected' : '' }}>Room Service</option>
                </select>
            </div>
            <div>
                <label class="label">Status</label>
                <select name="status" class="select">
                    <option value="">Semua</option>
                    @foreach (\App\Models\Order::$flowLabels as $value => $label)
                        <option value="{{ $value }}" {{ $filters['status'] === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">Kasir</label>
                <select name="cashier_id" class="select">
                    <option value="">Semua</option>
                    @foreach ($cashiers as $cashier)
                        <option value="{{ $cashier->id }}" {{ $filters['cashier_id'] == $cashier->id ? 'selected' : '' }}>{{ $cashier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">Metode Bayar</label>
                <select name="payment_method_id" class="select">
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

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
        <div class="card p-4">
            <p class="text-xs text-gray-500">Penjualan Kotor</p>
            <p class="mt-1 text-lg font-bold text-gray-900">Rp {{ number_format($grossSales, 0, ',', '.') }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500">Refund</p>
            <p class="mt-1 text-lg font-bold text-red-600">- Rp {{ number_format($refundTotal, 0, ',', '.') }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500">Penjualan Bersih</p>
            <p class="mt-1 text-lg font-bold text-emerald-700">Rp {{ number_format($netSales, 0, ',', '.') }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500">Order Selesai</p>
            <p class="mt-1 text-lg font-bold text-gray-900">{{ $orderCount }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500">Dibatalkan</p>
            <p class="mt-1 text-lg font-bold text-gray-900">{{ $cancelledCount }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500">Rata-rata Order</p>
            <p class="mt-1 text-lg font-bold text-gray-900">Rp {{ number_format($avgOrderValue, 0, ',', '.') }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500">Metode Teratas</p>
            <p class="mt-1 truncate text-lg font-bold text-gray-900">{{ $paymentMix->first()?->name ?? '-' }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="card overflow-hidden">
            <div class="border-b border-gray-200 px-5 py-4"><h2 class="text-base font-semibold text-gray-900">Status Order</h2></div>
            <div class="p-5">
                <ul class="space-y-2">
                    @foreach (\App\Models\Order::$flowLabels as $value => $label)
                        @if (isset($statusDistribution[$value]))
                            <li class="flex justify-between rounded-lg bg-gray-50 px-3 py-2 text-sm">
                                <span class="text-gray-700">{{ $label }}</span>
                                <span class="font-semibold text-gray-900">{{ $statusDistribution[$value] }}</span>
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