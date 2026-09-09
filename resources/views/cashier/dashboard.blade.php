@extends('layouts.app')

@section('title', 'Kasir - '.config('app.name'))
@section('header', 'Kasir')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Antrian Order</h1>
        <div class="flex gap-2">
            <a href="{{ route('cashier.history') }}" class="btn btn-secondary">Riwayat</a>
            <a href="{{ route('cashier.shift') }}" class="btn btn-secondary">Shift</a>
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
            if (window.EchoEnabled && window.Echo) {
                let reloadTimer = null;
                const scheduleReload = () => {
                    clearTimeout(reloadTimer);
                    reloadTimer = setTimeout(() => window.location.reload(), 800);
                };

                Echo.channel('order.new')
                    .listen('.OrderCreated', scheduleReload)
                    .listen('.OrderStatusUpdated', scheduleReload);

                Echo.channel('cooking').listen('.OrderStatusUpdated', scheduleReload);
                Echo.channel('kitchen').listen('.OrderStatusUpdated', scheduleReload);
                Echo.channel('order').listen('.PaymentSettled', scheduleReload);
            }
        </script>
    @endpush
@endsection