@extends('layouts.kiosk')

@section('title', 'Pembayaran '.$payment->external_id)

@section('content')
    <div class="mx-auto max-w-md">
        <div class="card overflow-hidden">
            <div class="border-b border-night-700 px-6 py-5 text-center">
                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-brand-500/15 text-brand-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h2m4 0h4M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/></svg>
                </div>
                <h1 class="text-lg font-bold text-stone-100">{{ $payment->paymentMethod?->name }}</h1>
                <p class="mt-1 text-sm text-stone-500">Order {{ $payment->order->order_number }}</p>
            </div>

            <div class="px-6 py-6">
                <div class="mb-2 flex items-center justify-between text-sm">
                    <span class="text-stone-400">Total Bayar</span>
                    <span class="text-2xl font-bold text-stone-100">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-stone-400">Status</span>
                    <span class="badge bg-amber-500/15 text-amber-300">Menunggu Pembayaran</span>
                </div>

                <div class="mt-6 space-y-3">
                    <form method="POST" action="{{ route('payment.mock.process', $payment) }}">
                        @csrf
                        <input type="hidden" name="outcome" value="paid">
                        <button type="submit" class="btn btn-success w-full">Simulasikan Pembayaran Berhasil</button>
                    </form>

                    <form method="POST" action="{{ route('payment.mock.process', $payment) }}">
                        @csrf
                        <input type="hidden" name="outcome" value="failed">
                        <button type="submit" class="btn btn-secondary w-full">Simulasikan Gagal</button>
                    </form>

                    <form method="POST" action="{{ route('payment.mock.process', $payment) }}">
                        @csrf
                        <input type="hidden" name="outcome" value="pending">
                        <button type="submit" class="btn btn-secondary w-full">Biarkan Menunggu</button>
                    </form>
                </div>

                <p class="mt-5 text-center text-xs text-stone-500">
                    Mode simulasi: gateway pembayaran tiruan untuk pengembangan/uji coba.
                </p>
            </div>
        </div>
    </div>
@endsection