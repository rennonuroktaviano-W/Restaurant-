@extends('layouts.kiosk')

@section('title', __('cart.title').' - '.config('app.name'))

@section('content')
    <a href="{{ route('menu.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-forest-700 hover:underline">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        {{ __('cart.back_to_menu') }}
    </a>

    <div class="mt-4 grid gap-6 lg:grid-cols-[1fr_24rem]">
        <div class="space-y-6">
            @if ($lines->isEmpty())
                <div class="empty-state py-16">
                    <p class="text-ink-500">{{ __('cart.empty') }}</p>
                    <a href="{{ route('menu.index') }}" class="btn btn-primary mt-5">{{ __('cart.view_menu') }}</a>
                </div>
            @else
                <div class="card overflow-hidden">
                    <div class="border-b border-ink-900/10 bg-cream-100/50 px-6 py-4">
                        <h2 class="font-display text-lg font-semibold text-ink-900">{{ __('cart.your_order') }}</h2>
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
                                        <p class="text-xs text-ink-500">{{ __('menu.notes_label') }} {{ $line['notes'] }}</p>
                                    @endif
                                    @if (! $line['available'])
                                        <p class="text-xs font-medium text-burgundy-600">{{ __('cart.unavailable') }}</p>
                                    @elseif ($line['limited'] && $line['stock'] < $line['quantity'])
                                        <p class="text-xs font-medium text-burgundy-600">{{ __('cart.low_stock', ['count' => $line['stock']]) }}</p>
                                    @endif
                                </div>

                                <form method="POST" action="{{ route('cart.update', $line['product_id']) }}" class="flex items-center gap-1" aria-label="{{ __('cart.update_quantity', ['name' => $line['product_name']]) }}" data-cart-ajax>
                                    @csrf
                                    <button type="submit" name="quantity" value="{{ $line['quantity'] - 1 }}" data-cart-minus="{{ $line['product_id'] }}" aria-label="{{ __('cart.decrease', ['name' => $line['product_name']]) }}" class="rounded-lg border border-ink-900/15 bg-cream-100/60 px-2 py-1 text-ink-600 transition hover:border-forest-600/40 hover:text-forest-700">−</button>
                                    <input type="text" name="quantity" value="{{ $line['quantity'] }}" data-cart-page-qty="{{ $line['product_id'] }}" data-cart-quantity-input inputmode="numeric" pattern="[0-9]*" maxlength="2" aria-label="{{ __('cart.quantity_label', ['name' => $line['product_name']]) }}" class="input w-14 text-center !min-h-0 !px-1 !py-1.5">
                                    <button type="submit" name="quantity" value="{{ $line['quantity'] + 1 }}" data-cart-plus="{{ $line['product_id'] }}"
                                            @if ($line['limited'] && $line['quantity'] >= $line['stock']) disabled @endif
                                            aria-label="{{ __('cart.increase', ['name' => $line['product_name']]) }}" class="rounded-lg border border-ink-900/15 bg-cream-100/60 px-2 py-1 text-ink-600 transition hover:border-forest-600/40 hover:text-forest-700 disabled:opacity-40">+</button>
                                </form>

                                <div class="w-24 shrink-0 text-right font-display text-base font-semibold text-ink-900" data-cart-row-total="{{ $line['product_id'] }}">
                                    Rp {{ number_format($line['price'] * $line['quantity'], 0, ',', '.') }}
                                </div>

<form method="POST" action="{{ route('cart.remove', $line['product_id']) }}" data-cart-ajax>
                                    @csrf
                                    <button type="submit" class="rounded-lg p-1.5 text-ink-400 transition hover:bg-burgundy-50 hover:text-burgundy-600" title="{{ __('cart.remove') }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <form method="POST" action="{{ route('checkout.store') }}" id="checkout-form" class="card p-6">
                    @csrf
                    <input type="hidden" name="idempotency_key" value="{{ session('checkout.key') }}">
                    <input type="hidden" name="discount_code" value="{{ $discountCode ?? '' }}">

                    <div x-data="{ type: '{{ old('order_type', 'take_away') }}', payment: '{{ (string) old('payment_method_id', $paymentMethods->first()?->id ?? '') }}' }">
                        <div class="mb-5">
                            <span class="label block">{{ __('cart.order_type') }}</span>
                            <div class="grid grid-cols-3 gap-2" role="radiogroup" aria-label="{{ __('cart.order_type') }}">
                                @foreach ([
                                    ['take_away', __('cart.take_away')],
                                    ['dine_in', __('cart.dine_in')],
                                    ['room_service', __('cart.room_service')],
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
                                <label for="table_id" class="label">{{ __('cart.select_table') }}</label>
                                <select id="table_id" name="table_id" class="select">
                                    <option value="">{{ __('cart.select_area_first') }}</option>
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
                                <label for="room_id" class="label">{{ __('cart.select_room') }}</label>
                                <select id="room_id" name="room_id" class="select">
                                    <option value="">{{ __('cart.select_room_option') }}</option>
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
                                <label for="customer_name" class="label">{{ __('cart.customer_name') }}</label>
                                <input id="customer_name" type="text" name="customer_name" value="{{ old('customer_name') }}"
                                       maxlength="100" pattern="[^0-9]*" title="{{ __('checkout.invalid_name') }}"
                                       placeholder="{{ __('cart.customer_name_placeholder') }}" class="input {{ $errors->has('customer_name') ? '!border-burgundy-500' : '' }}">
                                @error('customer_name')
                                    <p class="form-error mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="customer_phone" class="label">{{ __('cart.customer_phone') }}</label>
                                <input id="customer_phone" type="tel" name="customer_phone" value="{{ old('customer_phone') }}"
                                       inputmode="numeric" maxlength="15" pattern="[0-9]{8,15}" title="{{ __('checkout.invalid_phone') }}"
                                       placeholder="{{ __('cart.customer_phone_placeholder') }}" class="input {{ $errors->has('customer_phone') ? '!border-burgundy-500' : '' }}">
                                @error('customer_phone')
                                    <p class="form-error mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4">
                            <span class="label block">{{ __('cart.payment_method') }}</span>
                            <div class="grid gap-2 sm:grid-cols-3" role="radiogroup" aria-label="{{ __('cart.payment_method') }}">
                                @foreach ($paymentMethods as $method)
                                    @php
                                        $icon = match ($method->code) {
                                            'cash' => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                                            'qris' => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15V6a3 3 0 116 0v9M3 12a9 9 0 1018 0 9 9 0 00-18 0z"/></svg>',
                                            'debit_card' => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h2m4 0h4M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/></svg>',
                                            'gopay' => '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/></svg>',
                                            'ovo' => '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm5.5-9H7V9h10.5v2z"/></svg>',
                                            'dana' => '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9H9V9h6.5v2z"/></svg>',
                                            default => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h2m4 0h4M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/></svg>',
                                        };
                                    @endphp
                                    <button type="button"
                                            role="radio"
                                            @click="payment = '{{ $method->id }}'"
                                            :aria-checked="payment === '{{ $method->id }}'"
                                            :class="payment === '{{ $method->id }}' ? 'border-forest-700 bg-forest-700 text-cream-50 shadow-sm' : 'border-ink-900/15 bg-cream-50 text-ink-600 hover:border-forest-600/40'"
                                            class="rounded-lg border px-3 py-2.5 text-left transition flex items-center gap-3">
                                        <span class="flex-shrink-0 text-forest-700" style="color: inherit;">{!! $icon !!}</span>
                                        <div>
                                            <span class="block text-sm font-medium">{{ $method->name }}</span>
                                            <span class="mt-0.5 block text-xs" :class="payment === '{{ $method->id }}' ? 'text-cream-200/80' : 'text-ink-400'">
                                                {{ $method->type === 'online' ? __('cart.online_payment') : __('cart.cash_payment') }}
                                            </span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                            <input type="hidden" name="payment_method_id" :value="payment" value="{{ old('payment_method_id') }}">
                            @error('payment_method_id')
                                <p class="form-error mt-1">{{ $message }}</p>
                            @enderror
                            <p class="form-hint">{{ __('cart.payment_hint') }}</p>
                        </div>

                        <div class="mt-4">
                            <label for="notes" class="label">{{ __('cart.order_notes') }}</label>
                            <textarea id="notes" name="notes" rows="2" class="input" placeholder="{{ __('cart.order_notes_placeholder') }}">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-6 w-full">{{ __('cart.create_order') }}</button>
                </form>
            @endif
        </div>

        <aside class="h-fit space-y-4 lg:sticky lg:top-28">
            <div class="card p-6">
                <h2 class="mb-4 font-display text-lg font-semibold text-ink-900">{{ __('cart.summary') }}</h2>

                <form method="POST" action="{{ route('cart.discount') }}" class="mb-4 flex gap-2">
                    @csrf
                    <input type="text" name="discount_code" value="{{ $discountCode ?? '' }}" placeholder="Kode promo"
                           maxlength="50" class="input !py-2 flex-1 uppercase tracking-wider text-sm">
                    <button type="submit" class="btn !px-4 !py-2 bg-forest-700 text-cream-50 text-sm font-medium hover:bg-forest-800 whitespace-nowrap">
                        {{ $discountCode ? 'Ganti' : 'Terapkan' }}
                    </button>
                </form>
                @if ($discountCode)
                    <form method="POST" action="{{ route('cart.discount') }}" class="mb-4">
                        @csrf
                        <input type="hidden" name="discount_code" value="">
                        <button type="submit" class="text-xs text-burgundy-600 hover:underline">Hapus kode: {{ $discountCode }}</button>
                    </form>
                @endif

                <dl class="space-y-2 text-sm" data-cart-summary>
                    <div class="flex justify-between">
                        <dt class="text-ink-500">{{ __('cart.subtotal') }}</dt>
                        <dd class="font-medium text-ink-800">Rp {{ number_format($pricing['subtotal'], 0, ',', '.') }}</dd>
                    </div>

                    @if ((float) $pricing['discount_amount'] > 0)
                        <div class="flex justify-between text-forest-700">
                            <dt>{{ __('cart.discount') }}</dt>
                            <dd class="font-medium">− Rp {{ number_format($pricing['discount_amount'], 0, ',', '.') }}</dd>
                        </div>
                    @endif

                    @if ((float) $pricing['tax_amount'] > 0)
                        <div class="flex justify-between">
                            <dt class="text-ink-500">{{ __('cart.tax') }}</dt>
                            <dd class="font-medium text-ink-800">Rp {{ number_format($pricing['tax_amount'], 0, ',', '.') }}</dd>
                        </div>
                    @endif

                    @if ((float) $pricing['service_charge_amount'] > 0)
                        <div class="flex justify-between">
                            <dt class="text-ink-500">{{ __('cart.service_charge') }}</dt>
                            <dd class="font-medium text-ink-800">Rp {{ number_format($pricing['service_charge_amount'], 0, ',', '.') }}</dd>
                        </div>
                    @endif

                    <div class="flex justify-between border-t border-ink-900/10 pt-3 font-display text-base font-bold">
                        <dt>{{ __('cart.total') }}</dt>
                        <dd class="text-forest-700">Rp {{ number_format($pricing['grand_total'], 0, ',', '.') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="card p-6">
                <h2 class="mb-3 font-display text-base font-semibold text-ink-900">{{ __('cart.payment_methods_title') }}</h2>
                <ul class="space-y-2 text-sm text-ink-500">
                    @foreach ($paymentMethods as $method)
                        @php
                            $icon = match ($method->code) {
                                'cash' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                                'qris' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15V6a3 3 0 116 0v9M3 12a9 9 0 1018 0 9 9 0 00-18 0z"/></svg>',
                                'debit_card' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h2m4 0h4M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/></svg>',
                                'gopay' => '<svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/></svg>',
                                'ovo' => '<svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm5.5-9H7V9h10.5v2z"/></svg>',
                                'dana' => '<svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9H9V9h6.5v2z"/></svg>',
                                default => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h2m4 0h4M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/></svg>',
                            };
                        @endphp
                        <li class="flex items-center gap-2">
                            <span class="flex-shrink-0 text-forest-700" style="color: inherit;">{!! $icon !!}</span>
                            {{ $method->name }}
                        </li>
                    @endforeach
                </ul>
                <p class="mt-3 text-xs text-ink-400">{{ __('cart.payment_methods_hint') }}</p>
            </div>
        </aside>
    </div>
@endsection
