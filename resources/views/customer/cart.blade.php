@extends('layouts.kiosk')

@section('title', 'Keranjang - '.config('app.name'))

@section('content')
    <a href="{{ route('menu.index') }}" class="mb-4 inline-flex items-center gap-2 text-sm font-medium text-brand-600 hover:text-brand-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Menu
    </a>

    <div class="grid gap-6 lg:grid-cols-[1fr_theme(spacing.96)]">
        <div class="space-y-4">
            @if ($lines->isEmpty())
                <div class="card p-12 text-center">
                    <p class="text-gray-500">Keranjang masih kosong.</p>
                    <a href="{{ route('menu.index') }}" class="btn btn-primary mt-4">Lihat Menu</a>
                </div>
            @else
                <div class="card overflow-hidden">
                    <div class="border-b border-gray-200 px-5 py-4">
                        <h2 class="text-base font-semibold text-gray-900">Pesanan Anda</h2>
                    </div>

                    <ul class="divide-y divide-gray-100">
                        @foreach ($lines as $line)
                            <li class="flex items-center gap-4 px-5 py-4" @if (! $line['available']) :class="'opacity-50'" @endif>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-gray-900">{{ $line['product_name'] }}</p>
                                    @if ($line['notes'])
                                        <p class="text-xs text-gray-500">Catatan: {{ $line['notes'] }}</p>
                                    @endif
                                    @if (! $line['available'])
                                        <p class="text-xs font-medium text-red-600">Tidak tersedia</p>
                                    @elseif ($line['limited'] && $line['stock'] < $line['quantity'])
                                        <p class="text-xs font-medium text-red-600">Stok tersisa {{ $line['stock'] }}</p>
                                    @endif
                                </div>

                                <form method="POST" action="{{ route('cart.update', $line['product_id']) }}" class="flex items-center gap-1">
                                    @csrf
                                    <button type="submit" name="quantity" value="{{ $line['quantity'] - 1 }}" class="rounded-lg border border-gray-300 px-2 py-1 text-gray-600 hover:bg-gray-50">−</button>
                                    <input type="number" name="quantity" value="{{ $line['quantity'] }}" min="1" max="99" class="input w-16 text-center">
                                    <button type="submit" name="quantity" value="{{ $line['quantity'] + 1 }}" class="rounded-lg border border-gray-300 px-2 py-1 text-gray-600 hover:bg-gray-50">+</button>
                                </form>

                                <div class="w-24 text-right text-sm font-semibold text-gray-900">
                                    Rp {{ number_format($line['price'] * $line['quantity'], 0, ',', '.') }}
                                </div>

                                <form method="POST" action="{{ route('cart.remove', $line['product_id']) }}">
                                    @csrf
                                    <button type="submit" class="rounded-lg p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600" title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <form method="POST" action="{{ route('checkout.store') }}" id="checkout-form" class="card p-5">
                    @csrf
                    <input type="hidden" name="idempotency_key" value="{{ session('checkout.key') }}">

                    <div class="mb-4">
                        <label for="order_type" class="label">Tipe Order</label>
                        <select id="order_type" name="order_type" class="select" required>
                            <option value="take_away" {{ old('order_type', 'take_away') === 'take_away' ? 'selected' : '' }}>Take Away</option>
                            <option value="dine_in" {{ old('order_type') === 'dine_in' ? 'selected' : '' }}>Dine In</option>
                            <option value="room_service" {{ old('order_type') === 'room_service' ? 'selected' : '' }}>Room Service</option>
                        </select>
                    </div>

                    <div x-data="{ type: '{{ old('order_type', 'take_away') }}' }"
                         x-init="$watch('type', v => { document.getElementById('order_type').value = v })">
                        <div class="grid gap-4">
                            <div x-show="type === 'dine_in'" x-cloak class="space-y-2">
                                <label for="table_id" class="label">Pilih Meja</label>
                                <select id="table_id" name="table_id" class="select">
                                    <option value="">— Pilih area terlebih dahulu —</option>
                                    @foreach ($areas as $area)
                                        <optgroup label="{{ $area->name }}">
                                            @foreach ($area->diningTables as $table)
                                                <option value="{{ $table->id }}" {{ old('table_id') == $table->id ? 'selected' : '' }}>
                                                    {{ $table->name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>

                            <div x-show="type === 'room_service'" x-cloak class="space-y-2">
                                <label for="room_id" class="label">Pilih Room</label>
                                <select id="room_id" name="room_id" class="select">
                                    <option value="">— Pilih room —</option>
                                    @foreach ($rooms as $room)
                                        <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                                            {{ $room->area->name }} — {{ $room->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="customer_name" class="label">Nama</label>
                                <input id="customer_name" type="text" name="customer_name" value="{{ old('customer_name') }}" class="input" placeholder="Opsional">
                            </div>
                            <div>
                                <label for="customer_phone" class="label">No. HP</label>
                                <input id="customer_phone" type="text" name="customer_phone" value="{{ old('customer_phone') }}" class="input" placeholder="Opsional">
                            </div>
                        </div>

                        <div class="mt-4">
                            <label for="payment_method_id" class="label">Metode Pembayaran</label>
                            <select id="payment_method_id" name="payment_method_id" class="select">
                                <option value="">Bayar di Kasir / Tunai</option>
                                @foreach ($paymentMethods as $method)
                                    <option value="{{ $method->id }}" {{ old('payment_method_id') == $method->id ? 'selected' : '' }} {{ $method->type === 'online' ? 'data-online' : '' }}>
                                        {{ $method->name }}@if ($method->type === 'online') (Online)@endif
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-400">Jika memilih pembayaran online, Anda akan diarahkan ke halaman pembayaran setelah order dibuat.</p>
                        </div>

                        <div class="mt-4">
                            <label for="notes" class="label">Catatan Order</label>
                            <textarea id="notes" name="notes" rows="2" class="input" placeholder="Opsional">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-6 w-full">Buat Order</button>
                </form>
            @endif
        </div>

        <aside class="lg:sticky lg:top-24 h-fit space-y-4">
            <div class="card p-5">
                <h2 class="mb-4 text-base font-semibold text-gray-900">Ringkasan</h2>

                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Subtotal</dt>
                        <dd class="font-medium">Rp {{ number_format($pricing['subtotal'], 0, ',', '.') }}</dd>
                    </div>

                    @if ((float) $pricing['discount_amount'] > 0)
                        <div class="flex justify-between text-emerald-600">
                            <dt>Diskon</dt>
                            <dd class="font-medium">− Rp {{ number_format($pricing['discount_amount'], 0, ',', '.') }}</dd>
                        </div>
                    @endif

                    @if ((float) $pricing['tax_amount'] > 0)
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Pajak</dt>
                            <dd class="font-medium">Rp {{ number_format($pricing['tax_amount'], 0, ',', '.') }}</dd>
                        </div>
                    @endif

                    @if ((float) $pricing['service_charge_amount'] > 0)
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Service Charge</dt>
                            <dd class="font-medium">Rp {{ number_format($pricing['service_charge_amount'], 0, ',', '.') }}</dd>
                        </div>
                    @endif

                    <div class="flex justify-between border-t border-gray-200 pt-3 text-base font-bold">
                        <dt>Total</dt>
                        <dd class="text-brand-700">Rp {{ number_format($pricing['grand_total'], 0, ',', '.') }}</dd>
                    </div>
                </dl>

                <a href="#checkout-form" class="btn btn-primary mt-5 w-full">Lanjut Isi Data</a>
            </div>

            <div class="card p-5">
                <h2 class="mb-3 text-base font-semibold text-gray-900">Metode Pembayaran</h2>
                <ul class="space-y-2 text-sm text-gray-600">
                    @foreach ($paymentMethods as $method)
                        <li class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full {{ $method->type === 'online' ? 'bg-brand-500' : 'bg-emerald-500' }}"></span>
                            {{ $method->name }}
                        </li>
                    @endforeach
                </ul>
                <p class="mt-3 text-xs text-gray-400">Pilih metode saat checkout untuk pembayaran online otomatis.</p>
            </div>
        </aside>
    </div>
@endsection