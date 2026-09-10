@extends('layouts.kiosk')

@section('title', $product->name.' - '.config('app.name'))

@section('content')
    @include('customer._cart-drawer', ['lines' => $drawerLines, 'subtotal' => $drawerSubtotal])

    <a href="{{ route('menu.index') }}" class="link-subtle inline-flex items-center gap-1.5 text-sm font-medium">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        {{ __('menu.back') }}
    </a>

    <section class="mt-6 mx-auto max-w-4xl">
        <article class="card grid gap-8 overflow-hidden md:grid-cols-2">
            <div class="media-frame relative aspect-[4/3] rounded-none border-0">
                @if ($product->image)
                    <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover">
                @else
                    <div class="flex h-full w-full items-center justify-center bg-cream-200 text-ink-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                @endif

                @if ($product->isSoldOut())
                    <div class="absolute inset-0 flex items-center justify-center bg-ink-950/50 backdrop-blur-[1px]">
                        <span class="badge bg-burgundy-700 text-cream-50 !px-4 !py-1.5 text-xs font-bold uppercase tracking-wide">{{ __('menu.sold_out') }}</span>
                    </div>
                @endif
            </div>

            <div class="p-6 md:p-8">
                @if ($product->category)
                    <p class="text-[10px] font-semibold tracking-[0.18em] text-gold-600 uppercase">{{ $product->category->name }}</p>
                @endif

                <h1 class="mt-2 font-display text-3xl font-semibold text-ink-900">{{ $product->name }}</h1>

                @if ($product->description)
                    <p class="mt-3 text-sm leading-relaxed text-ink-500">{{ $product->description }}</p>
                @endif

                @php $qty = $cartQuantities[$product->id] ?? 0; @endphp

                <div class="mt-6 flex items-center justify-between gap-3 border-t border-ink-900/10 pt-6">
                    <span class="font-display text-3xl font-semibold text-forest-800">{{ __('menu.price_prefix') }}{{ number_format($product->sale_price, 0, ',', '.') }}</span>
                </div>

                <div class="mt-6">
                    @if ($product->isSoldOut())
                        <button type="button" disabled aria-label="{{ __('menu.sold_out', ['name' => $product->name]) }}"
                                class="btn btn-secondary w-full !min-h-0 opacity-60">{{ __('menu.sold_out') }}</button>
                    @else
                        <form method="POST" action="{{ route('cart.update', $product->id) }}"
                              data-cart-ajax data-menu-stepper="{{ $product->id }}"
                              class="flex w-full items-center justify-between rounded-lg border border-ink-900/15 bg-cream-50"
                              @if ($qty === 0) hidden @endif>
                            @csrf
                            <button type="submit" name="quantity" value="{{ max(1, $qty) - 1 }}" data-menu-minus
                                    aria-label="{{ __('menu.decrease', ['name' => $product->name]) }}" class="px-4 py-2 text-lg font-semibold text-ink-500 transition hover:text-forest-700">−</button>
                            <span class="min-w-8 text-center text-lg font-semibold text-ink-800" data-menu-qty="{{ $product->id }}" aria-label="{{ $qty }} {{ __('menu.in_cart') }}">{{ $qty }}</span>
                            <button type="submit" name="quantity" value="{{ $qty + 1 }}" data-menu-plus
                                    @if ($product->isLimitedStock() && $qty >= $product->stock) disabled @endif
                                    aria-label="{{ __('menu.increase', ['name' => $product->name]) }}" class="px-4 py-2 text-lg font-semibold text-ink-500 transition hover:text-forest-700 disabled:opacity-40">+</button>
                        </form>
                        <form method="POST" action="{{ route('cart.add') }}" data-cart-ajax data-menu-add="{{ $product->id }}"
                              class="mt-4" @if ($qty > 0) hidden @endif>
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" aria-label="{{ __('menu.add_to_cart', ['name' => $product->name]) }}"
                                    class="btn btn-primary w-full !min-h-0">{{ __('menu.add_to_cart') }}</button>
                        </form>
                    @endif
                </div>
            </div>
        </article>
    </section>

    <script>
        if (window.EchoEnabled && window.Echo) {
            Echo.channel('order.new').listen('.OrderCreated', (e) => {
                if (e.order_number) {
                    showToast('Order baru: ' + e.order_number, 'success');
                }
            });
        }
    </script>
@endsection