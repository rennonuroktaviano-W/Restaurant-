@extends('layouts.kiosk')

@section('title', 'Status Order '.$order->order_number)

@section('content')
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('menu.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-forest-700 hover:underline">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke Menu
        </a>

        <div class="card mt-4 overflow-hidden">
            <div class="border-b border-ink-900/10 bg-cream-100/50 px-6 py-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="font-display text-xl font-semibold text-ink-900">Order {{ $order->order_number }}</h1>
                        <p class="mt-1 text-sm text-ink-500">
                            {{ $order->ordered_at?->format('d M Y H:i') }} ·
                            {{ ['dine_in' => 'Dine In', 'take_away' => 'Take Away', 'room_service' => 'Room Service'][$order->order_type] }}
                            · {{ $order->locationLabel() }}
                        </p>
                    </div>
                    <span class="badge {{ $order->order_status === 'cancelled' ? 'bg-burgundy-100 text-burgundy-700' : ($order->order_status === 'completed' ? 'bg-forest-100 text-forest-800' : 'bg-gold-100 text-gold-700') }}">
                        {{ \App\Models\Order::$flowLabels[$order->order_status] ?? ucwords($order->order_status) }}
                    </span>
                </div>
            </div>

            @if ($order->order_status !== 'completed')
                <div class="border-b border-ink-900/10 px-6 py-5">
                    @php
                        $flow = ['new' => 'Dibuat', 'accepted' => 'Diterima', 'cooking' => 'Dimasak', 'ready' => 'Siap', 'completed' => 'Selesai'];
                        if (! $order->has_kitchen_items) {
                            $flow = array_intersect_key($flow, array_flip(['new', 'accepted', 'ready', 'completed']));
                        }
                        $keys = array_keys($flow);
                        $currentIndex = array_search($order->order_status, $keys, true);
                        $currentIndex = $currentIndex === false ? count($keys) - 1 : $currentIndex;
                    @endphp

                    <ol class="flex items-start gap-2 text-xs">
                        @foreach ($flow as $step => $label)
                            @php
                                $stepIndex = array_search($step, $keys, true);
                                $done = $order->isTerminal() || $stepIndex <= $currentIndex;
                            @endphp
                            <li class="flex-1">
                                <div class="flex flex-col gap-1.5">
                                    <span class="h-1.5 w-full rounded-full {{ $done ? 'bg-forest-600' : 'bg-ink-200' }}"></span>
                                    <span class="{{ $done ? 'font-medium text-forest-700' : 'text-ink-400' }}">{{ $label }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ol>

                    @if ($order->order_status === 'cancelled')
                        <p class="mt-3 rounded-lg bg-burgundy-50 px-3 py-2 text-sm text-burgundy-700">
                            Dibatalkan: {{ $order->cancellation?->reason ?? '-' }}
                        </p>
                    @endif
                </div>
            @endif

            <div class="px-6 py-5">
                <h2 class="mb-3 font-display text-base font-semibold text-ink-900">Rincian Pesanan</h2>
                <ul class="divide-y divide-ink-900/10">
                    @foreach ($order->items as $item)
                        <li class="flex items-center justify-between gap-3 py-2 text-sm">
                            <span class="text-ink-700">
                                {{ $item->quantity }} &times; {{ $item->product_name }}
                                @if ($item->notes)
                                    <span class="block text-xs text-ink-500">— {{ $item->notes }}</span>
                                @endif
                            </span>
                            <span class="font-medium text-ink-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </li>
                    @endforeach
                </ul>

                <dl class="mt-4 space-y-1 border-t border-ink-900/10 pt-3 text-sm">
                    <div class="flex justify-between text-ink-500">
                        <dt>Subtotal</dt>
                        <dd>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</dd>
                    </div>
                    @if ((float) $order->discount_amount > 0)
                        <div class="flex justify-between text-forest-700">
                            <dt>Diskon</dt>
                            <dd>− Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</dd>
                        </div>
                    @endif
                    @if ((float) $order->tax_amount > 0)
                        <div class="flex justify-between text-ink-500">
                            <dt>Pajak</dt>
                            <dd>Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</dd>
                        </div>
                    @endif
                    @if ((float) $order->service_charge_amount > 0)
                        <div class="flex justify-between text-ink-500">
                            <dt>Service Charge</dt>
                            <dd>Rp {{ number_format($order->service_charge_amount, 0, ',', '.') }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between font-display text-base font-bold text-ink-900">
                        <dt>Total</dt>
                        <dd>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="border-t border-ink-900/10 bg-cream-100/50 px-6 py-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-ink-800">Status Pembayaran</p>
                        <p class="mt-0.5 text-sm">
                            <span class="badge {{ $order->payment_status === 'paid' ? 'bg-forest-100 text-forest-800' : ($order->payment_status === 'failed' ? 'bg-burgundy-100 text-burgundy-700' : 'bg-gold-100 text-gold-700') }}">
                                {{ ['paid' => 'Lunas', 'pending' => 'Belum Bayar', 'failed' => 'Gagal', 'expired' => 'Kadaluarsa'][$order->payment_status] ?? $order->payment_status }}
                            </span>
                            @if ($order->payments->where('status', 'paid')->first())
                                <span class="ml-2 text-xs text-ink-500">
                                    {{ $order->payments->where('status', 'paid')->first()->paymentMethod?->name }}
                                </span>
                            @endif
                        </p>
                    </div>

                    @if ($order->payment_status !== 'paid' && ! in_array($order->order_status, ['completed', 'cancelled']))
                        @if ($latestPendingPayment)
                            <a href="{{ route('payment.mock.pay', $latestPendingPayment) }}" class="btn btn-primary">Lanjutkan Pembayaran</a>
                        @else
                            <a href="{{ route('menu.index') }}" class="btn btn-secondary">Pesan Lagi</a>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        @if ($order->order_status !== 'completed' && $order->order_status !== 'cancelled')
            <a href="{{ route('tracking.show', $order) }}?refresh=1" class="mt-4 block text-center text-sm text-ink-500 hover:text-forest-700">
                Perbarui status
            </a>
        @endif
    </div>
@endsection