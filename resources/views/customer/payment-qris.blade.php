@extends('layouts.kiosk')

@section('title', 'Pembayaran QRIS Order '.$payment->order->order_number)

@section('content')
    <div class="mx-auto max-w-md">
        <div class="card overflow-hidden">
            <div class="border-b border-ink-900/10 bg-cream-100/50 px-6 py-5 text-center">
                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-forest-100 text-forest-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 17m-1 0a1 1 0 100 2 1 1 0 000-2zm14 0a1 1 0 100 2 1 1 0 000-2zM5 5m-1 0a1 1 0 100 2 1 1 0 000-2zm8 0a1 1 0 100 2 1 1 0 000-2z"/></svg>
                </div>
                <h1 class="font-display text-lg font-semibold text-ink-900">{{ $payment->paymentMethod?->name }}</h1>
                <p class="mt-1 text-sm text-ink-500">Order {{ $payment->order->order_number }}</p>
            </div>

            <div class="px-6 py-6">
                <div class="mb-4 flex items-center justify-between text-sm">
                    <span class="text-ink-500">Nominal Dibayar</span>
                    <span class="font-display text-2xl font-semibold text-ink-900">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                </div>

                <div class="mx-auto w-fit rounded-xl border border-ink-900/10 bg-white p-4 shadow-sm">
                    @if (file_exists(public_path('storage/'.$imagePath)))
                        <img src="{{ asset('storage/'.$imagePath) }}" alt="QRIS {{ config('app.name') }}" class="block h-auto w-auto max-h-64 max-w-64 object-contain">
                    @else
                        <div class="flex aspect-square h-64 w-64 items-center justify-center bg-ink-100 text-ink-400">
                            <p class="text-center text-sm">Gambar QRIS tidak tersedia. Hubungi kasir.</p>
                        </div>
                    @endif
                </div>

                <ol class="mt-5 space-y-1.5 text-sm text-ink-600">
                    <li>1. Buka aplikasi bank / e-wallet Anda.</li>
                    <li>2. Pilih menu <strong>Bayar / Scan QRIS</strong> lalu scan QR di atas.</li>
                    <li>3. Masukkan nominal <strong>Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong> lalu selesaikan pembayaran.</li>
                </ol>

                <p class="mt-5 rounded-lg bg-gold-100/70 px-3 py-2.5 text-xs leading-relaxed text-gold-800">
                    Pembayaran dikonfirmasi oleh kasir setelah pembayaran diterima.
                </p>

                <a href="{{ route('tracking.show', $payment->order) }}" class="btn btn-primary mt-5 w-full">Kembali ke Status Order</a>
            </div>
        </div>
    </div>
@endsection