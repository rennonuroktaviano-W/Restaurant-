@extends('layouts.kiosk')

@section('title', 'Keranjang - '.config('app.name'))

@section('content')
    <a href="{{ route('menu.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-forest-700 hover:underline">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Menu
    </a>

    <div class="mt-4 grid gap-6 lg:grid-cols-[1fr_24rem]">
        <div class="space-y-6">
            @if ($lines->isEmpty())
                <div class="empty-state py-16">
                    <p class="text-ink-500">Keranjang masih kosong.</p>
                    <a href="{{ route('menu.index') }}" class="btn btn-primary mt-5">Lihat Menu</a>
                </div>
            @else
                <div class="card overflow-hidden">
                    <div class="border-b border-ink-900/10 bg-cream-100/50 px-6 py-4">
                        <h2 class="font-display text-lg font-semibold text-ink-900">Pesanan Anda</h2>
                    </div>

                    <ul class="divide-y divide-ink-900/10">
                        @foreach ($lines as $line)
                            <li class="flex flex-wrap items-center gap-4 px-6 py-4 sm:flex-nowrap" data-cart-line-row="{{ $line['product_id'] }}" @if (! $line['available']) :class="'opacity-50'" @endif>
                                <div class="media-frame h-16 w-16 shrink-0">
                                    @if ($line['image'])
                                        <img src="{{ asset('storage/'.$line['image']) }}" alt="{{ $line['product_name'] }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-ink-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-ink-900">{{ $line['product_name'] }}</p>
                                    @if ($line['notes'])
                                        <p class="text-xs text-ink-500">Catatan: {{ $line['notes'] }}</p>
                                    @endif
                                    @if (! $line['available'])
                                        <p class="text-xs font-medium text-burgundy-600">Tidak tersedia</p>
                                    @elseif ($line['limited'] && $line['stock'] < $line['quantity'])
                                        <p class="text-xs font-medium text-burgundy-600">Stok tersisa {{ $line['stock'] }}</p>
                                    @endif
                                </div>

                                <form method="POST" action="{{ route('cart.update', $line['product_id']) }}" class="flex items-center gap-1" aria-label="Ubah jumlah {{ $line['product_name'] }}" data-cart-ajax>
                                    @csrf
                                    <button type="submit" name="quantity" value="{{ $line['quantity'] - 1 }}" data-cart-minus="{{ $line['product_id'] }}" aria-label="Kurangi {{ $line['product_name'] }}" class="rounded-lg border border-ink-900/15 bg-cream-100/60 px-2 py-1 text-ink-600 transition hover:border-forest-600/40 hover:text-forest-700">−</button>
                                    <input type="text" name="quantity" value="{{ $line['quantity'] }}" data-cart-page-qty="{{ $line['product_id'] }}" data-cart-quantity-input inputmode="numeric" pattern="[0-9]*" maxlength="2" aria-label="Jumlah {{ $line['product_name'] }}" class="input w-14 text-center !min-h-0 !px-1 !py-1.5">
                                    <button type="submit" name="quantity" value="{{ $line['quantity'] + 1 }}" data-cart-plus="{{ $line['product_id'] }}"
                                            @if ($line['limited'] && $line['quantity'] >= $line['stock']) disabled @endif
                                            aria-label="Tambah {{ $line['product_name'] }}" class="rounded-lg border border-ink-900/15 bg-cream-100/60 px-2 py-1 text-ink-600 transition hover:border-forest-600/40 hover:text-forest-700 disabled:opacity-40">+</button>
                                </form>

                                <div class="w-24 shrink-0 text-right font-display text-base font-semibold text-ink-900" data-cart-row-total="{{ $line['product_id'] }}">
                                    Rp {{ number_format($line['price'] * $line['quantity'], 0, ',', '.') }}
                                </div>

                                <form method="POST" action="{{ route('cart.remove', $line['product_id']) }}" data-cart-ajax>
                                    @csrf
                                    <button type="submit" class="rounded-lg p-1.5 text-ink-400 transition hover:bg-burgundy-50 hover:text-burgundy-600" title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="card p-6">
                    <h2 class="font-display text-lg font-semibold text-ink-900">Kode Promo</h2>
                    <p class="mt-1 text-sm text-ink-500">Punya kode promo? Terapkan sebelum buat order — total di ringkasan dihitung otomatis oleh sistem.</p>

                    <form method="POST" action="{{ route('cart.discount') }}" class="mt-4 flex flex-col gap-2 sm:flex-row">
                        @csrf
                        <div class="min-w-0 flex-1">
                            <label for="discount_code" class="sr-only">Kode promo</label>
                            <input id="discount_code" type="text" name="discount_code" value="{{ $discountCode ?? '' }}"
                                   placeholder="cth. HEMAT10" maxlength="50" autocomplete="off"
                                   class="input uppercase tracking-wider">
                        </div>
                        <button type="submit" class="btn btn-primary w-full shrink-0 sm:w-auto">
                            {{ $discountCode ? 'Ganti' : 'Gunakan' }}
                        </button>
                    </form>

                    @if ($discountCode)
                        <form method="POST" action="{{ route('cart.discount') }}" class="mt-2">
                            @csrf
                            <input type="hidden" name="discount_code" value="">
                            <button type="submit" class="text-xs text-burgundy-600 hover:underline">Hapus kode: {{ $discountCode }}</button>
                        </form>
                        <p class="form-hint mt-2">Kode "{{ $discountCode }}" tersimpan dan akan dipakai saat checkout.</p>
                    @endif
                </div>

                <form method="POST" action="{{ route('checkout.store') }}" id="checkout-form" class="card p-6">
                    @csrf
                    <input type="hidden" name="idempotency_key" value="{{ session('checkout.key') }}">
                    <input type="hidden" name="discount_code" value="{{ $discountCode ?? '' }}">

                    <div x-data="{ type: '{{ old('order_type', 'take_away') }}', payment: '{{ (string) old('payment_method_id', $paymentMethods->first()?->id ?? '') }}' }">
                        <div class="mb-5">
                            <span class="label block">Tipe Order</span>
                            <div class="grid grid-cols-3 gap-2" role="radiogroup" aria-label="Tipe order">
                                @foreach ([
                                    ['take_away', 'Take Away'],
                                    ['dine_in', 'Dine In'],
                                    ['room_service', 'Room Service'],
                                ] as [$value, $label])
                                    <button type="button"
                                            role="radio"
                                            @click="type = '{{ $value }}'"
                                            :aria-checked="type === '{{ $value }}'"
                                            :class="type === '{{ $value }}' ? 'border-forest-700 bg-forest-700 text-cream-50 shadow-sm' : 'border-ink-900/15 bg-cream-50 text-ink-600 hover:border-forest-600/40'"
                                            class="rounded-lg border px-3 py-2.5 text-sm font-medium transition">
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                            <input type="hidden" name="order_type" :value="type" value="{{ old('order_type', 'take_away') }}">
                        </div>

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
                                <input id="customer_name" type="text" name="customer_name" value="{{ old('customer_name') }}"
                                       maxlength="100" pattern="[^0-9]*" title="Nama tidak boleh mengandung angka"
                                       placeholder="Opsional" class="input {{ $errors->has('customer_name') ? '!border-burgundy-500' : '' }}">
                                @error('customer_name')
                                    <p class="form-error mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="customer_phone" class="label">No. HP</label>
                                <input id="customer_phone" type="tel" name="customer_phone" value="{{ old('customer_phone') }}"
                                       inputmode="numeric" maxlength="15" pattern="[0-9]{8,15}" title="Nomor HP hanya angka, 8–15 digit"
                                       placeholder="Opsional" class="input {{ $errors->has('customer_phone') ? '!border-burgundy-500' : '' }}">
                                @error('customer_phone')
                                    <p class="form-error mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4">
                            <span class="label block">Metode Pembayaran</span>
                            <div class="grid gap-2 sm:grid-cols-3" role="radiogroup" aria-label="Metode pembayaran">
                                @foreach ($paymentMethods as $method)
                                    <button type="button"
                                            role="radio"
                                            @click="payment = '{{ $method->id }}'"
                                            :aria-checked="payment === '{{ $method->id }}'"
                                            :class="payment === '{{ $method->id }}' ? 'border-forest-700 bg-forest-700 text-cream-50 shadow-sm' : 'border-ink-900/15 bg-cream-50 text-ink-600 hover:border-forest-600/40'"
                                            class="rounded-lg border px-3 py-2.5 text-left transition">
                                        <span class="block text-sm font-medium">{{ $method->name }}</span>
                                        <span class="mt-0.5 block text-xs" :class="payment === '{{ $method->id }}' ? 'text-cream-200/80' : 'text-ink-400'">
                                            {{ $method->type === 'online' ? 'Online — langsung ke pembayaran' : 'Tunai saat order' }}
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                            <input type="hidden" name="payment_method_id" :value="payment" value="{{ old('payment_method_id') }}">
                            @error('payment_method_id')
                                <p class="form-error mt-1">{{ $message }}</p>
                            @enderror
                            <p class="form-hint">Jika memilih pembayaran online, Anda akan diarahkan ke halaman pembayaran setelah order dibuat.</p>
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

        <aside class="h-fit space-y-4 lg:sticky lg:top-28">
            <div class="card p-6">
                <h2 class="mb-4 font-display text-lg font-semibold text-ink-900">Ringkasan</h2>

                @if ($discountCode)
                    <p class="mb-3 rounded-lg bg-forest-50 px-3 py-2 text-xs text-forest-800">Kode promo "{{ $discountCode }}" aktif.</p>
                @endif

                <dl class="space-y-2 text-sm" data-cart-summary>
                    <div class="flex justify-between">
                        <dt class="text-ink-500">Subtotal</dt>
                        <dd class="font-medium text-ink-800">Rp {{ number_format($pricing['subtotal'], 0, ',', '.') }}</dd>
                    </div>

                    @if ((float) $pricing['discount_amount'] > 0)
                        <div class="flex justify-between text-forest-700">
                            <dt>Diskon</dt>
                            <dd class="font-medium">− Rp {{ number_format($pricing['discount_amount'], 0, ',', '.') }}</dd>
                        </div>
                    @endif

                    @if ((float) $pricing['tax_amount'] > 0)
                        <div class="flex justify-between">
                            <dt class="text-ink-500">Pajak</dt>
                            <dd class="font-medium text-ink-800">Rp {{ number_format($pricing['tax_amount'], 0, ',', '.') }}</dd>
                        </div>
                    @endif

                    @if ((float) $pricing['service_charge_amount'] > 0)
                        <div class="flex justify-between">
                            <dt class="text-ink-500">Service Charge</dt>
                            <dd class="font-medium text-ink-800">Rp {{ number_format($pricing['service_charge_amount'], 0, ',', '.') }}</dd>
                        </div>
                    @endif

                    <div class="flex justify-between border-t border-ink-900/10 pt-3 font-display text-base font-bold">
                        <dt>Total</dt>
                        <dd class="text-forest-700">Rp {{ number_format($pricing['grand_total'], 0, ',', '.') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="card p-6">
                <h2 class="mb-3 font-display text-base font-semibold text-ink-900">Metode Pembayaran</h2>
                <ul class="space-y-2 text-sm text-ink-500">
                    @foreach ($paymentMethods as $method)
                        <li class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full {{ $method->type === 'online' ? 'bg-gold-500' : 'bg-forest-600' }}"></span>
                            {{ $method->name }}
                        </li>
                    @endforeach
                </ul>
                <p class="mt-3 text-xs text-ink-400">Pilih metode saat checkout untuk pembayaran online otomatis.</p>
            </div>
        </aside>
    </div>
@endsection