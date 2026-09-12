@php
    $typeLabels = ['dine_in' => 'Dine In', 'take_away' => 'Take Away', 'room_service' => 'Room Service'];
    $isKitchenZone = $zone === 'new' || $zone === 'cooking';
    $ageMinutes = $order->ordered_at ? max(0, (int) $order->ordered_at->diffInMinutes(now())) : null;
    $ageVerb = $zone === 'cooking' ? 'Dimasak' : ($zone === 'ready' ? 'Siap' : 'Menunggu');
    $ageTone = $ageMinutes === null || $ageMinutes < 15
        ? 'bg-stone-500/15 text-stone-300'
        : ($ageMinutes < 30 ? 'bg-amber-500/15 text-amber-300' : 'bg-red-500/15 text-red-300');
@endphp

<div class="card p-4">
    <div class="flex items-start justify-between gap-2">
        <div>
            <p class="text-sm font-bold text-gray-900">{{ $order->order_number }}</p>
            <p class="text-xs text-gray-500">
                {{ $order->ordered_at?->format('H:i') }} · {{ $typeLabels[$order->order_type] ?? $order->order_type }}
                @if ($order->area) · {{ $order->area->name }} / {{ $order->locationLabel() }} @endif
            </p>
            @if ($ageMinutes !== null)
                <p class="mt-1.5">
                    <span class="badge {{ $ageTone }}" aria-label="{{ $ageVerb }} {{ $ageMinutes }} menit">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $ageVerb }} {{ $ageMinutes }} mnt
                    </span>
                </p>
            @endif
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
                <button type="submit" class="btn {{ $zone === 'new' ? 'btn-primary' : 'btn-success' }} btn-sm !min-h-11 !px-4">
                    {{ $zone === 'new' ? 'Mulai Masak' : 'Tandai Siap' }}
                </button>
            </form>
        @endif

        @if ($zone !== 'ready')
            <form method="POST" action="{{ route('kitchen.orders.cancel', $order) }}" id="kitchen-cancel-{{ $order->id }}" x-data="{ open: false }">
                @csrf
                <button type="button" @click="open = true" class="btn btn-danger btn-sm !min-h-11 !px-4">Batal</button>
<template x-teleport="body">
                    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @keydown.escape.window="open = false">
                        <div class="w-full max-w-md rounded-lg border border-night-600 bg-night-900 p-6 shadow-2xl" @click.outside="open = false" role="dialog" aria-modal="true" aria-labelledby="kitchen-cancel-title-{{ $order->id }}">
                            <h3 id="kitchen-cancel-title-{{ $order->id }}" class="text-base font-semibold text-stone-100">Batalkan {{ $order->order_number }}?</h3>
                            <label class="label mt-4" for="kitchen-cancel-reason-{{ $order->id }}">Alasan pembatalan</label>
                            <textarea id="kitchen-cancel-reason-{{ $order->id }}" name="reason" form="kitchen-cancel-{{ $order->id }}" required minlength="5" rows="3" class="input w-full" placeholder="Minimal 5 karakter"></textarea>
                            <div class="mt-4 flex justify-end gap-2">
                                <button type="button" @click="open = false" class="btn btn-secondary">Tutup</button>
                                <button type="submit" form="kitchen-cancel-{{ $order->id }}" class="btn btn-danger">Ya, Batalkan</button>
                            </div>
                        </div>
                    </div>
                </template>
            </form>
        @endif
    </div>
</div>