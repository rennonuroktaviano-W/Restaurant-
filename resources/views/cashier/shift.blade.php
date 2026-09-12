@extends('layouts.app')

@section('title', 'Shift - Kasir - '.config('app.name'))
@section('header', 'Ringkasan Shift')

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-gray-900">Ringkasan Shift — {{ $cashier->name }}</h1>
        <form method="GET" action="{{ route('cashier.shift') }}" class="flex items-center gap-2">
            <input type="date" name="date" value="{{ request('date', today()->format('Y-m-d')) }}" class="input w-44">
            <button type="submit" class="btn btn-secondary">Lihat</button>
        </form>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card p-4">
            <p class="text-xs text-gray-500">Total Order</p>
            <p class="mt-1 text-lg font-bold text-gray-900">{{ $totalOrders }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500">Selesai</p>
            <p class="mt-1 text-lg font-bold text-emerald-600">{{ $completed }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500">Dibatalkan</p>
            <p class="mt-1 text-lg font-bold text-red-600">{{ $cancelled }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500">Penjualan Cash (Selesai)</p>
            <p class="mt-1 text-lg font-bold text-gray-900">Rp {{ number_format($cashSales, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="mt-6 card overflow-hidden">
        <div class="border-b border-gray-200 px-5 py-4"><h2 class="text-base font-semibold text-gray-900">Rekonsiliasi Tunai</h2></div>
        <div class="overflow-x-auto">
            <table class="table-w">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Metode</th>
                        <th class="px-4 py-3 text-right font-medium">Diterima (Lunas)</th>
                        <th class="px-4 py-3 text-right font-medium">Refund</th>
                        <th class="px-4 py-3 text-right font-medium">Bersih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($methods->where('type', 'cash') as $method)
                        @php $row = $reconciliation[$method->id] ?? ['received' => 0, 'refunds' => 0, 'net' => 0]; @endphp
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $method->name }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700">Rp {{ number_format($row['received'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-sm text-red-600">Rp {{ number_format($row['refunds'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">Rp {{ number_format($row['net'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="flex justify-end border-t border-gray-100 px-4 py-3">
            <p class="text-sm text-gray-600">
                Kas yang diharapkan (ekspektasi transfer):
                <span class="font-semibold text-gray-900">Rp {{ number_format($cashExpected, 0, ',', '.') }}</span>
            </p>
        </div>
    </div>
@endsection