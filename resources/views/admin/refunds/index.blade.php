@extends('layouts.app')

@section('title', 'Refund - '.config('app.name'))
@section('header', 'Refund')

@section('content')
    <h1 class="mb-5 text-2xl font-bold text-gray-900">Refund</h1>

    <div class="card mb-6 overflow-hidden">
        <form method="GET" action="{{ route('admin.refunds.index') }}" class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-6">
            <div class="lg:col-span-3">
                <label class="label" for="f-refund-q">Pencarian</label>
                <input id="f-refund-q" type="search" name="q" value="{{ request('q') }}" placeholder="Nomor order atau alasan..." class="input">
            </div>
            <div>
                <label class="label" for="f-refund-from">Dari Tanggal</label>
                <input id="f-refund-from" type="date" name="date_from" value="{{ request('date_from') }}" class="input">
            </div>
            <div>
                <label class="label" for="f-refund-to">Sampai Tanggal</label>
                <input id="f-refund-to" type="date" name="date_to" value="{{ request('date_to') }}" class="input">
            </div>
            <div>
                <label class="label" for="f-refund-status">Status</label>
                <select id="f-refund-status" name="status" class="select">
                    <option value="">Semua</option>
                    <option value="succeeded" @selected(request('status') === 'succeeded')>Berhasil</option>
                    <option value="failed" @selected(request('status') === 'failed')>Gagal</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('admin.refunds.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <div class="card p-5">
            <p class="text-sm text-gray-500">Total Refund Berhasil</p>
            <p class="mt-2 text-2xl font-bold text-red-600">Rp {{ number_format($succeededTotal, 0, ',', '.') }}</p>
        </div>
        <div class="card p-5">
            <p class="text-sm text-gray-500">Refund Berhasil</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $succeededCount }}</p>
        </div>
        <div class="card p-5">
            <p class="text-sm text-gray-500">Refund Gagal</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $failedCount }}</p>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-w">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Waktu</th>
                        <th class="px-4 py-3 font-medium">Order</th>
                        <th class="px-4 py-3 font-medium">Metode</th>
                        <th class="px-4 py-3 font-medium">Jumlah</th>
                        <th class="px-4 py-3 font-medium">Alasan</th>
                        <th class="px-4 py-3 font-medium">Oleh</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($refunds as $refund)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $refund->created_at?->format('d M Y H:i:s') }}</td>
                            <td class="px-4 py-3 text-sm text-brand-600">
                                <a href="{{ route('cashier.orders.show', $refund->order) }}">{{ $refund->order?->order_number }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $refund->payment?->paymentMethod?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">Rp {{ number_format($refund->amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                @if ($refund->reason_code)
                                    <span class="badge mr-1 bg-gray-100 text-gray-600">
                                        {{ ['customer' => 'Pelanggan', 'damaged' => 'Rusak', 'other' => 'Lainnya'][$refund->reason_code] ?? $refund->reason_code }}
                                    </span>
                                @endif
                                {{ $refund->reason }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $refund->creator?->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="badge {{ $refund->status === 'succeeded' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $refund->status === 'succeeded' ? 'Berhasil' : 'Gagal' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-400">Belum ada refund.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4">
            {{ $refunds->links() }}
        </div>
    </div>
@endsection