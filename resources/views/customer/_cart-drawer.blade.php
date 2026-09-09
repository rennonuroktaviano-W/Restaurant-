@props([
    'lines' => collect(),
    'subtotal' => 0,
])

<div x-data="{ open: false }"
     @open-cart.window="open = true"
     @keydown.escape.window="open = false"
     x-cloak>
    <template x-teleport="body">
        <div x-show="open" x-transition.opacity class="fixed inset-0 z-40 bg-gray-900/40" @click="open = false"></div>
    </template>

    <template x-teleport="body">
        <aside x-show="open" x-transition class="fixed inset-y-0 right-0 z-50 flex w-full max-w-md flex-col bg-white shadow-xl" role="dialog" aria-modal="true">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                <h2 class="text-base font-semibold text-gray-900">Keranjang Anda</h2>
                <button type="button" @click="open = false" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-5 py-4">
                @if ($lines->isEmpty())
                    <div class="flex h-full flex-col items-center justify-center text-center text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mb-3 h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <p class="text-sm">Keranjang masih kosong.</p>
                        <a href="{{ route('menu.index') }}" class="mt-3 text-sm font-medium text-brand-600 hover:text-brand-700">Lihat menu</a>
                    </div>
                @else
                    <ul class="space-y-4">
                        @foreach ($lines as $line)
                            <li class="flex items-start gap-3">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-gray-900">{{ $line['product_name'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $line['quantity'] }} &times; {{ number_format($line['price'], 0, ',', '.') }}</p>
                                </div>
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ number_format($line['price'] * $line['quantity'], 0, ',', '.') }}
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="border-t border-gray-200 px-5 py-4">
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-sm text-gray-600">Subtotal</span>
                    <span class="text-base font-semibold text-gray-900">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                <a href="{{ route('cart.index') }}" class="btn btn-primary w-full">Lanjut ke Pembayaran</a>
            </div>
        </aside>
    </template>
</div>