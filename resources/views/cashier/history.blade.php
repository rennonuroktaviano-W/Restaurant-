@extends('layouts.app')

@section('title', 'Riwayat - Kasir - '.config('app.name'))
@section('header', 'Riwayat Order')

@section('content')
    @php
        $typeLabels = ['dine_in' => 'Dine In', 'take_away' => 'Take Away', 'room_service' => 'Room Service'];
    @endphp

    <div class="card mb-6 p-5">
        <form method="GET" action="{{ route('cashier.history') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <label class="label">Nomor Order</label>
                <input type="search" name="order_number" value="{{ request('order_number') }}" placeholder="Cari nomor..." class="input">
            </div>
            <div>
                <label class="label">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="input">
            </div>
            <div>
                <label class="label">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="input">
            </div>
            <div>
                <label class="label">Status</label>
                <select name="status" class="select">
                    <option value="">Semua</option>
                    @foreach ([\App\Models\Order::STATUS_COMPLETED => 'Selesai', \App\Models\Order::STATUS_CANCELLED => 'Dibatalkan'] as $value => $label)
                        <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('cashier.history') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-w">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Order</th>
                        <th class="px-4 py-3 font-medium">Tanggal</th>
                        <th class="px-4 py-3 font-medium">Tipe / Lokasi</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Metode</th>
                        <th class="px-4 py-3 text-right font-medium">Total</th>
                        <th class="px-4 py-3 text-right font-medium">Struk</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($orders as $order)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $order->order_number }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $order->ordered_at?->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $typeLabels[$order->order_type] ?? $order->order_type }} @if ($order->area) · {{ $order->locationLabel() }} @endif</td>
                            <td class="px-4 py-3">
                                @if ($order->order_status === \App\Models\Order::STATUS_COMPLETED)
                                    <span class="badge bg-emerald-100 text-emerald-700">Selesai</span>
                                @else
                                    <span class="badge bg-red-100 text-red-700">Dibatalkan</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $order->payments->first()?->paymentMethod?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('cashier.receipt.show', $order) }}" class="btn btn-secondary btn-sm">Struk</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">Belum ada order selesai/dibatalkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-4">{{ $orders->links() }}</div>
    </div>
@endsection