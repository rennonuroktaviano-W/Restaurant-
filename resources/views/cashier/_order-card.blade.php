@php
    $paymentLabels = ['pending' => 'Belum Bayar', 'paid' => 'Lunas', 'failed' => 'Gagal', 'expired' => 'Kadaluarsa', 'cancelled' => 'Dibatalkan'];
    $paymentBadge = [
        'pending' => 'bg-amber-100 text-amber-700',
        'paid' => 'bg-emerald-100 text-emerald-700',
        'failed' => 'bg-red-100 text-red-700',
        'expired' => 'bg-gray-100 text-gray-600',
    ];
    $pendingOnlinePayment = $order->payment_status === \App\Models\Order::PAYMENT_PENDING && $order->payments->where('type', 'online')->where('status', \App\Models\Payment::STATUS_PENDING)->isNotEmpty();
@endphp

<div class="card p-4">
    <div class="flex items-start justify-between gap-2">
        <div>
            <p class="text-sm font-bold text-gray-900">{{ $order->order_number }}</p>
            <p class="text-xs text-gray-500">{{ $order->ordered_at?->format('H:i') }}
                · {{ ['dine_in' => 'Dine In', 'take_away' => 'Take Away', 'room_service' => 'Room Service'][$order->order_type] }}
                @if ($order->area) · {{ $order->locationLabel() }} @endif
            </p>
        </div>
        @if ($order->payments->where('status', \App\Models\Payment::STATUS_PAID)->isNotEmpty())
            <span class="badge bg-emerald-100 text-emerald-700">Lunas</span>
        @else
            <span class="badge {{ $paymentBadge[$order->payment_status] ?? 'bg-gray-100 text-gray-600' }}">{{ $paymentLabels[$order->payment_status] ?? $order->payment_status }}</span>
        @endif
    </div>

    <ul class="mt-3 space-y-1 border-t border-gray-100 pt-3">
        @foreach ($order->items as $item)
            <li class="flex justify-between text-sm">
                <span class="text-gray-700">
                    {{ $item->quantity }}× {{ $item->product_name }}
                    @if ($item->notes) <span class="text-xs text-gray-400">({{ $item->notes }})</span> @endif
                </span>
                <span class="font-medium text-gray-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
            </li>
        @endforeach
    </ul>

    <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-3">
        <p class="text-sm text-gray-500">Total: <span class="font-bold text-gray-900">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span></p>

        <div class="flex gap-2">
            <a href="{{ route('cashier.orders.show', $order) }}" class="btn btn-secondary btn-sm">Detail</a>

            @if ($zone === 'new')
                @if ($pendingOnlinePayment)
                    <span class="self-center text-xs text-amber-600">Menunggu bayar online</span>
                @else
                    <form method="POST" action="{{ route('cashier.orders.accept', $order) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">Terima</button>
                    </form>
                @endif
            @endif

            @if ($zone === 'ready')
                @if ($order->payment_status === \App\Models\Order::PAYMENT_PAID)
                    <form method="POST" action="{{ route('cashier.orders.complete', $order) }}">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">Selesaikan</button>
                    </form>
                @else
                    <a href="{{ route('cashier.orders.show', $order) }}" class="btn btn-primary btn-sm">Bayar</a>
                @endif
            @endif

@if (in_array($zone, ['new', 'active', 'ready'], true))
                <form method="POST" action="{{ route('cashier.orders.cancel', $order) }}" id="cancel-{{ $order->id }}" x-data="{ open: false }">
                    @csrf
                    <button type="button" @click="open = true" class="btn btn-danger btn-sm">Batal</button>

                    <template x-teleport="body">
                        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @keydown.escape.window="open = false">
                            <div class="w-full max-w-md rounded-lg border border-night-600 bg-night-900 p-6 shadow-2xl" @click.outside="open = false" role="dialog" aria-modal="true" aria-labelledby="cancel-title-{{ $order->id }}">
                                <h3 id="cancel-title-{{ $order->id }}" class="text-base font-semibold text-stone-100">Batalkan {{ $order->order_number }}?</h3>
                                @if ($order->payments->where('status', \App\Models\Payment::STATUS_PAID)->isNotEmpty())
                                    <p class="mt-2 text-xs text-stone-400">Pembayaran yang sudah lunas akan otomatis di-refund.</p>
                                @endif
                                <label class="label mt-4" for="cancel-reason-{{ $order->id }}">Alasan pembatalan</label>
                                <textarea id="cancel-reason-{{ $order->id }}" name="reason" form="cancel-{{ $order->id }}" required rows="3" class="input w-full" placeholder="Contoh: pelanggan membatalkan pesanan"></textarea>
                                <div class="mt-4 flex justify-end gap-2">
                                    <button type="button" @click="open = false" class="btn btn-secondary">Tutup</button>
                                    <button type="submit" form="cancel-{{ $order->id }}" class="btn btn-danger">Ya, Batalkan</button>
                                </div>
                            </div>
                        </div>
                    </template>
                </form>
            @endif
        </div>
    </div>
</div>