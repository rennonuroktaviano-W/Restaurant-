@php
    $typeLabels = ['dine_in' => 'Dine In', 'take_away' => 'Take Away', 'room_service' => 'Room Service'];
    $isKitchenZone = $zone === 'new' || $zone === 'cooking';
@endphp

<div class="card p-4">
    <div class="flex items-start justify-between gap-2">
        <div>
            <p class="text-sm font-bold text-gray-900">{{ $order->order_number }}</p>
            <p class="text-xs text-gray-500">
                {{ $order->ordered_at?->format('H:i') }} · {{ $typeLabels[$order->order_type] ?? $order->order_type }}
                @if ($order->area) · {{ $order->area->name }} / {{ $order->locationLabel() }} @endif
            </p>
            @if ($order->notes)
                <p class="mt-1 text-xs text-amber-700">Catatan order: {{ $order->notes }}</p>
            @endif
        </div>
        <span class="text-xs font-bold uppercase {{ $zone === 'new' ? 'text-amber-600' : ($zone === 'cooking' ? 'text-orange-600' : 'text-emerald-600') }}">
            {{ \App\Models\Order::$flowLabels[$order->order_status] ?? $order->order_status }}
        </span>
    </div>

    <ul class="mt-3 space-y-1 border-t border-gray-100 pt-3">
        @foreach ($order->items as $item)
            <li class="flex justify-between text-sm">
                <span class="font-medium text-gray-800">
                    {{ $item->quantity }}× {{ $item->product_name }}
                    @if ($item->notes)
                        <span class="block text-xs text-gray-500">Catatan: {{ $item->notes }}</span>
                    @endif
                </span>
                @if (isset($item->product) && $item->product->is_kitchen)
                    <span class="badge bg-brand-100 text-brand-700 self-start">Dapur</span>
                @endif
            </li>
        @endforeach
    </ul>

    <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-gray-100 pt-3">
        @if ($isKitchenZone)
            <form method="POST" action="{{ route('kitchen.orders.status', $order) }}">
                @csrf
                <input type="hidden" name="action" value="{{ $zone === 'new' ? 'start_cooking' : 'mark_ready' }}">
                <button type="submit" class="btn {{ $zone === 'new' ? 'btn-primary' : 'btn-success' }} btn-sm">
                    {{ $zone === 'new' ? 'Mulai Masak' : 'Tandai Siap' }}
                </button>
            </form>
        @endif

        @if ($zone !== 'ready')
            <form method="POST" action="{{ route('kitchen.orders.cancel', $order) }}" x-data="{ open: false }">
                @csrf
                <button type="button" @click="open = true" class="btn btn-danger btn-sm">Batal</button>
                <template x-teleport="body">
                    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @keydown.escape.window="open = false">
                        <div class="w-full max-w-md rounded-lg border border-night-600 bg-night-900 p-6 shadow-2xl" @click.outside="open = false">
                            <h3 class="text-base font-semibold text-stone-100">Batalkan {{ $order->order_number }}?</h3>
                            <label class="label mt-4">Alasan pembatalan</label>
                            <textarea name="reason" required minlength="5" rows="3" class="input w-full" placeholder="Minimal 5 karakter"></textarea>
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