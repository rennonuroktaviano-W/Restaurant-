@extends('layouts.kiosk')

@section('title', 'Hasil Pembayaran')

@section('content')
    <div class="mx-auto max-w-md">
        <div class="card overflow-hidden text-center">
            <div class="px-6 py-10">
                @if ($payment->status === 'paid')
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h1 class="text-xl font-bold text-stone-100">Pembayaran Berhasil</h1>
                    <p class="mt-2 text-sm text-stone-400">Order <strong>{{ $payment->order->order_number }}</strong> sudah lunas dan diproses.</p>
                @elseif ($payment->status === 'failed')
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-500/15 text-red-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <h1 class="text-xl font-bold text-stone-100">Pembayaran Gagal</h1>
                    <p class="mt-2 text-sm text-stone-400">Silakan ulangi pembayaran untuk order {{ $payment->order->order_number }}.</p>
                @else
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-amber-500/15 text-amber-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h1 class="text-xl font-bold text-stone-100">Pembayaran Kadaluarsa / Menunggu</h1>
                    <p class="mt-2 text-sm text-stone-400">Status: {{ $payment->status }}</p>
                @endif

                <div class="mt-8 flex justify-center gap-3">
                    <a href="{{ route('tracking.show', $payment->order) }}" class="btn btn-primary">Lihat Status Order</a>
                    <a href="{{ route('menu.index') }}" class="btn btn-secondary">Kembali ke Menu</a>
                </div>
            </div>
        </div>
    </div>
@endsection