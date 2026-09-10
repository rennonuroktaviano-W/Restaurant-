@extends('layouts.app')

@section('title', 'Kasir - '.config('app.name'))
@section('header', 'Kasir')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ url('/') }}" class="btn btn-secondary" aria-label="Kembali ke beranda">&larr;</a>
            <h1 class="text-2xl font-bold text-gray-900">Antrian Order</h1>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('cashier.history') }}" class="btn btn-secondary">Riwayat</a>
            <button type="button" class="btn btn-primary" onclick="window.location.reload()">Muat Ulang</button>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <section>
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-sm font-semibold uppercase text-gray-500">Menunggu</h2>
                <span class="badge bg-amber-100 text-amber-700">{{ $newOrders->count() }}</span>
            </div>
            <div class="space-y-3">
                @forelse ($newOrders as $order)
                    @include('cashier._order-card', ['order' => $order, 'zone' => 'new'])
                @empty
                    <div class="card p-6 text-center text-sm text-gray-400">Tidak ada order baru.</div>
                @endforelse
            </div>
        </section>

        <section>
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-sm font-semibold uppercase text-gray-500">Diproses</h2>
                <span class="badge bg-brand-100 text-brand-700">{{ $activeOrders->count() }}</span>
            </div>
            <div class="space-y-3">
                @forelse ($activeOrders as $order)
                    @include('cashier._order-card', ['order' => $order, 'zone' => 'active'])
                @empty
                    <div class="card p-6 text-center text-sm text-gray-400">Tidak ada order diproses.</div>
                @endforelse
            </div>
        </section>

        <section>
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-sm font-semibold uppercase text-gray-500">Siap Disajikan</h2>
                <span class="badge bg-emerald-100 text-emerald-700">{{ $readyOrders->count() }}</span>
            </div>
            <div class="space-y-3">
                @forelse ($readyOrders as $order)
                    @include('cashier._order-card', ['order' => $order, 'zone' => 'ready'])
                @empty
                    <div class="card p-6 text-center text-sm text-gray-400">Belum ada order siap.</div>
                @endforelse
            </div>
        </section>
    </div>

    @push('scripts')
        <script>
            window.startBoardPolling({
                url: @json(route('cashier.dashboard.freshness')),
                interval: 15000,
            });

            if (window.EchoEnabled && window.Echo) {
                let reloadTimer = null;
                const scheduleReload = () => {
                    clearTimeout(reloadTimer);
                    reloadTimer = setTimeout(() => window.location.reload(), 800);
                };

                Echo.channel('order.new')
                    .listen('.order.created', scheduleReload)
                    .listen('.order.status.updated', scheduleReload);

                Echo.channel('cooking').listen('.order.status.updated', scheduleReload);
                Echo.channel('kitchen').listen('.order.status.updated', scheduleReload);
                Echo.channel('order').listen('.payment.settled', scheduleReload);
            }
        </script>
    @endpush
@endsection