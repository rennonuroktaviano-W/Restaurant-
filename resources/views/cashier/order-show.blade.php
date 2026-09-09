@extends('layouts.app')

@section('title', $order->order_number.' - Kasir - '.config('app.name'))
@section('header', $order->order_number)

@section('content')
    @php
        $typeLabels = ['dine_in' => 'Dine In', 'take_away' => 'Take Away', 'room_service' => 'Room Service'];
        $paymentLabels = ['pending' => 'Belum Bayar', 'paid' => 'Lunas', 'failed' => 'Gagal', 'expired' => 'Kadaluarsa'];
        $totalPaid = $order->payments()->where('status', \App\Models\Payment::STATUS_PAID)->sum('amount');
        $remaining = max(0, $order->grand_total - $totalPaid);
        $pendingOnline = $order->payments->first(fn ($p) => $p->type === 'online' && $p->status === \App\Models\Payment::STATUS_PENDING);
        $isOverdueOnline = $pendingOnline && $pendingOnline->expires_at?->lt(now());
    @endphp

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ url()->previous() === route('cashier.orders.show', $order) ? route('cashier.dashboard') : url()->previous() }}" class="mb-2 inline-block text-sm text-brand-600 hover:underline">&larr; Kembali</a>
            <h1 class="text-2xl font-bold text-gray-900">{{ $order->order_number }}</h1>
            <p class="text-sm text-gray-500">{{ $order->ordered_at?->format('d M Y H:i') }}
                · {{ $typeLabels[$order->order_type] ?? $order->order_type }}
                @if ($order->area) · {{ $order->area->name }} / {{ $order->locationLabel() }} @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="badge bg-blue-100 text-blue-700">{{ \App\Models\Order::$flowLabels[$order->order_status] ?? $order->order_status }}</span>
            <span class="badge {{ in_array($order->payment_status, ['paid', 'cancelled'], true) ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">{{ $paymentLabels[$order->payment_status] ?? $order->payment_status }}</span>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="card overflow-hidden">
                <div class="border-b border-gray-200 px-5 py-4"><h2 class="text-base font-semibold text-gray-900">Item Pesanan</h2></div>
                <table class="table-w">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Item</th>
                            <th class="px-4 py-3 text-right font-medium">Harga</th>
                            <th class="px-4 py-3 text-right font-medium">Qty</th>
                            <th class="px-4 py-3 text-right font-medium">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="px-4 py-3">
                                    <span class="text-sm font-medium text-gray-900">{{ $item->product_name }}</span>
                                    @if ($item->notes)
                                        <p class="text-xs text-gray-500">Catatan: {{ $item->notes }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-gray-600">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-sm text-gray-600">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="space-y-1 border-t border-gray-200 px-5 py-4">
                    <p class="flex justify-between text-sm text-gray-600"><span>Subtotal</span><span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span></p>
                    @if ($order->discount_amount > 0)
                        <p class="flex justify-between text-sm text-gray-600"><span>Diskon</span><span class="text-emerald-600">−Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span></p>
                    @endif
                    @if ($order->tax_amount > 0)
                        <p class="flex justify-between text-sm text-gray-600"><span>Pajak</span><span>Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</span></p>
                    @endif
                    @if ($order->service_charge_amount > 0)
                        <p class="flex justify-between text-sm text-gray-600"><span>Service Charge</span><span>Rp {{ number_format($order->service_charge_amount, 0, ',', '.') }}</span></p>
                    @endif
                    <p class="flex justify-between border-t border-gray-200 pt-2 text-base font-bold text-gray-900"><span>Total</span><span>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span></p>
                </div>
            </div>

            <div class="card overflow-hidden">
                <div class="border-b border-gray-200 px-5 py-4"><h2 class="text-base font-semibold text-gray-900">Riwayat Status</h2></div>
                <ul class="divide-y divide-gray-100 px-5 py-2">
                    @forelse ($order->statusHistories->sortByDesc('created_at')->take(15) as $history)
                        <li class="flex flex-wrap items-center justify-between gap-2 py-2 text-sm">
                            <span class="text-gray-700">
                                {{ \App\Models\Order::$flowLabels[$history->from_status] ?? $history->from_status }}
                                &rarr; {{ \App\Models\Order::$flowLabels[$history->to_status] ?? $history->to_status }}
                                @if ($history->note) <span class="text-xs text-gray-400">({{ $history->note }})</span> @endif
                            </span>
                            <span class="text-xs text-gray-500">{{ $history->created_at?->format('d M H:i') }} · {{ $history->actor?->name ?? '-' }}</span>
                        </li>
                    @empty
                        <li class="py-4 text-sm text-gray-400">Belum ada riwayat.</li>
                    @endforelse
                </ul>
                @if ($order->cancellation)
                    <div class="border-t border-gray-100 bg-red-50 px-5 py-3">
                        <p class="text-sm text-red-700">
                            Pembatalan oleh {{ $order->cancellation->actor?->name ?? '-' }}:
                            {{ $order->cancellation->reason }}
                            @if ($order->cancellation->reason_code) <span class="text-xs">({{ $order->cancellation->reason_code }})</span> @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="card p-5">
                <h2 class="text-base font-semibold text-gray-900">Pembayaran</h2>
                <p class="mt-2 text-sm text-gray-500">Total: <span class="font-semibold text-gray-900">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span></p>
                <p class="mt-1 text-sm text-gray-500">Terbayar: <span class="font-semibold text-emerald-600">Rp {{ number_format($totalPaid, 0, ',', '.') }}</span></p>
                <p class="mt-1 text-sm text-gray-500">Sisa: <span class="font-semibold text-gray-900">Rp {{ number_format($remaining, 0, ',', '.') }}</span></p>

                @if ($order->payment_status !== \App\Models\Order::PAYMENT_PAID && ! $order->isTerminal())
                    @if ($isOverdueOnline)
                        <div class="mt-4 rounded bg-red-50 px-3 py-2 text-xs text-red-700">Pembayaran online telah kadaluarsa. Minta bayar ulang di kasir.</div>
                    @endif

                    <form method="POST" action="{{ route('cashier.orders.pay-cash', $order) }}" class="mt-4 space-y-3" x-data="{ open: false, amount: '{{ $remaining }}' }">
                        @csrf
                        <div>
                            <label class="label">Metode Tunai</label>
                            <select name="payment_method_id" class="select" required>
                                @foreach ($methods->where('type', 'cash') as $method)
                                    <option value="{{ $method->id }}">{{ $method->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label">Jumlah Diterima</label>
                            <input type="number" name="amount_received" x-model="amount" min="{{ $remaining }}" step="0.01" class="input" required>
                        </div>
                        <button type="button" @click="open = true" class="btn btn-success w-full">Bayar Tunai (Rp <span x-text="Number(amount).toLocaleString('id-ID')"></span>)</button>

                        <template x-teleport="body">
                            <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @keydown.escape.window="open = false">
                                <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl" @click.outside="open = false">
                                    <h3 class="text-base font-semibold text-gray-900">Konfirmasi Bayar Tunai</h3>
                                    <p class="mt-2 text-sm text-gray-600">Total: Rp {{ number_format($order->grand_total, 0, ',', '.') }} — Diterima: Rp <span x-text="Number(amount).toLocaleString('id-ID')"></span></p>
                                    <p class="mt-1 text-sm text-gray-600">Kembalian: <span class="font-bold text-emerald-600" x-text="'Rp ' + Math.max(0, Number(amount) - {{ $order->grand_total }}).toLocaleString('id-ID')"></span></p>
                                    <div class="mt-4 flex justify-end gap-2">
                                        <button type="button" @click="open = false" class="btn btn-secondary">Tutup</button>
                                        <button type="submit" class="btn btn-success">Konfirmasi</button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </form>

                    @if ($methods->where('type', 'online')->isNotEmpty())
                        <form method="POST" action="{{ route('cashier.orders.pay-online', $order) }}" class="mt-3 space-y-3">
                            @csrf
                            <div>
                                <label class="label">Bayar Online</label>
                                <select name="payment_method_id" class="select" required>
                                    @foreach ($methods->where('type', 'online') as $method)
                                        <option value="{{ $method->id }}">{{ $method->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-full">Minta Bayar Online</button>
                            <p class="text-xs text-gray-500">Pelanggan akan diarahkan ke halaman pembayaran untuk melunasi sisa.</p>
                        </form>
                    @endif
                @endif
            </div>

            <div class="card p-5">
                <h2 class="text-base font-semibold text-gray-900">Aksi</h2>
                <div class="mt-3 flex flex-wrap gap-2">
                    @if ($order->order_status === \App\Models\Order::STATUS_NEW && $order->payment_status !== \App\Models\Order::PAYMENT_FAILED)
                        <form method="POST" action="{{ route('cashier.orders.accept', $order) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">Terima Order</button>
                        </form>
                    @elseif ($order->order_status === \App\Models\Order::STATUS_READY)
                        @if ($order->payment_status === \App\Models\Order::PAYMENT_PAID)
                            <form method="POST" action="{{ route('cashier.orders.complete', $order) }}">
                                @csrf
                                <button type="submit" class="btn btn-success">Selesaikan Order</button>
                            </form>
                        @else
                            <p class="text-sm text-amber-600">Order belum lunas — selesaikan pembayaran terlebih dahulu.</p>
                        @endif
                    @endif

                    @if ($order->payment_status === \App\Models\Order::PAYMENT_PAID)
                        <a href="{{ route('cashier.receipt.show', $order) }}" class="btn btn-secondary">Lihat Struk</a>
                        <a href="{{ route('cashier.receipt.print', $order) }}" target="_blank" rel="noopener" class="btn btn-secondary">Cetak Struk</a>
                    @endif

                    @if (in_array(\App\Models\Order::STATUS_CANCELLED, \App\Models\Order::$orderFlow[$order->order_status] ?? [], true))
                        <form method="POST" action="{{ route('cashier.orders.cancel', $order) }}" x-data="{ open: false }">
                            @csrf
                            <button type="button" @click="open = true" class="btn btn-danger">Batalkan</button>
                            <template x-teleport="body">
                                <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @keydown.escape.window="open = false">
                                    <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl" @click.outside="open = false">
                                        <h3 class="text-base font-semibold text-gray-900">Batalkan {{ $order->order_number }}?</h3>
                                        <label class="label mt-4">Alasan pembatalan</label>
                                        <textarea name="reason" required rows="3" class="input w-full" placeholder="Contoh: pelanggan membatalkan pesanan"></textarea>
                                        <div class="mt-4 flex justify-end gap-2">
                                            <button type="button" @click="open = false" class="btn btn-secondary">Tutup</button>
                                            <button type="submit" class="btn btn-danger">Ya, Batalkan</button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection