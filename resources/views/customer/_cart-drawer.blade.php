@props([
    'lines' => collect(),
    'subtotal' => 0,
])

<div x-data="{ open: false }"
     @open-cart.window="open = true"
     @keydown.escape.window="open = false"
     x-cloak>
    <template x-teleport="body">
        <div x-show="open" x-transition.opacity class="fixed inset-0 z-40 bg-ink-950/60" @click="open = false"></div>
    </template>

    <template x-teleport="body">
        <aside x-show="open" x-transition class="fixed inset-y-0 right-0 z-50 flex w-full max-w-md flex-col border-l border-ink-900/10 bg-cream-50 shadow-2xl" role="dialog" aria-modal="true" aria-label="Keranjang Anda">
            <div class="flex items-center justify-between border-b border-ink-900/10 px-5 py-4">
                <div class="flex items-center gap-2">
                    <h2 class="font-display text-lg font-semibold text-ink-900">Keranjang Anda</h2>
                    <span x-text="$store.cartCount" x-show="$store.cartCount > 0" x-cloak class="badge badge-gold">{{ 0 }}</span>
                </div>
                <button type="button" @click="open = false" aria-label="Tutup keranjang" class="rounded-lg p-1 text-ink-400 transition hover:bg-cream-200/60 hover:text-ink-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-5 py-4">
                @if ($lines->isEmpty())
                    <div class="flex h-full flex-col items-center justify-center text-center text-ink-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mb-3 h-12 w-12 text-ink-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <p class="text-sm">Keranjang masih kosong.</p>
                        <a href="{{ route('menu.index') }}" class="link-subtle mt-3 text-sm font-medium">Lihat menu</a>
                    </div>
                @else
                    <ul class="space-y-5">
                        @foreach ($lines as $line)
                            <li class="flex items-start gap-3" data-cart-line-row="{{ $line['product_id'] }}">
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
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="truncate text-sm font-medium text-ink-900">{{ $line['product_name'] }}</p>
                                        <button type="submit" form="cart-remove-{{ $line['product_id'] }}" aria-label="Hapus {{ $line['product_name'] }} dari keranjang" class="rounded p-0.5 text-ink-400 transition hover:text-burgundy-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    @if (! $line['available'])
                                        <p class="mt-0.5 text-xs font-medium text-burgundy-600">Tidak tersedia</p>
                                    @else
                                        <p class="text-xs text-ink-500">{{ number_format($line['price'], 0, ',', '.') }} / item</p>
                                    @endif
                                    @if ($line['notes'])
                                        <p class="mt-0.5 truncate text-xs italic text-ink-500">Catatan: {{ $line['notes'] }}</p>
                                    @endif

                                    <div class="mt-2 flex items-center justify-between gap-2">
                                        @if ($line['available'])
                                            <div class="flex items-center rounded-lg border border-ink-900/15 bg-cream-100/70">
                                                @if ($line['quantity'] > 1)
                                                    <form method="POST" action="{{ route('cart.update', $line['product_id']) }}" data-cart-ajax>
                                                        @csrf
                                                        <button type="submit" name="quantity" value="{{ $line['quantity'] - 1 }}" data-cart-drawer-minus="{{ $line['product_id'] }}" aria-label="Kurangi {{ $line['product_name'] }}" class="px-2.5 py-1 text-xs font-semibold text-ink-500 transition hover:text-forest-700">−</button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ route('cart.remove', $line['product_id']) }}" id="cart-remove-{{ $line['product_id'] }}" data-cart-ajax>
                                                        @csrf
                                                        <button type="submit" aria-label="Kurangi {{ $line['product_name'] }}" class="px-2.5 py-1 text-xs font-semibold text-ink-500 transition hover:text-forest-700">−</button>
                                                    </form>
                                                @endif
                                                <span class="min-w-6 text-center text-xs font-semibold text-ink-800" data-cart-line-qty="{{ $line['product_id'] }}">{{ $line['quantity'] }}</span>
                                                <form method="POST" action="{{ route('cart.update', $line['product_id']) }}" data-cart-ajax>
                                                    @csrf
<button type="submit" name="quantity" value="{{ $line['quantity'] + 1 }}" data-cart-drawer-plus="{{ $line['product_id'] }}"
                                                        @if ($line['limited'] && $line['quantity'] >= $line['stock']) disabled @endif
                                                        aria-label="Tambah {{ $line['product_name'] }}" class="px-2.5 py-1 text-xs font-semibold text-ink-500 transition hover:text-forest-700 disabled:opacity-40">+</button>
                                                </form>
                                            </div>
                                        @endif
                                        <span class="text-sm font-semibold text-ink-900" data-cart-line-total="{{ $line['product_id'] }}">{{ number_format($line['price'] * $line['quantity'], 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="border-t border-ink-900/10 bg-cream-100/60 px-5 py-4">
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-sm text-ink-500">Subtotal</span>
                    <span class="font-display text-lg font-semibold text-ink-900" data-cart-subtotal>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                <a href="{{ route('cart.index') }}" class="btn btn-primary w-full" aria-label="Lanjut ke pembayaran">Lanjut ke Pembayaran</a>
            </div>
        </aside>
    </template>
</div>