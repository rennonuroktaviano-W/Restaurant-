@extends('layouts.kiosk')

@section('title', 'Lacak Pesanan - '.config('app.name'))

@section('content')
    <div class="mx-auto max-w-md">
        <a href="{{ route('menu.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-forest-700 hover:underline">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke Menu
        </a>

        <div class="card mt-4 p-6 sm:p-8">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-forest-100 text-forest-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>

            <h1 class="text-center font-display text-2xl font-semibold text-ink-900">Lacak Pesanan</h1>
            <p class="mt-2 text-center text-sm leading-relaxed text-ink-500">
                Masukkan nomor order Anda untuk melihat status pesanan dan pembayaran.
            </p>

            <form method="POST" action="{{ route('tracking.find') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label for="order_number" class="label">Nomor Order</label>
                    <input id="order_number" type="text" name="order_number" value="{{ old('order_number') }}"
                           maxlength="32" autocomplete="off" placeholder="cth. POS-20250101-ABCDE" required
                           class="input uppercase tracking-wider {{ $errors->has('order_number') ? '!border-burgundy-500' : '' }}">
                    @error('order_number')
                        <p class="form-error mt-1">{{ $message }}</p>
                    @enderror
                    <p class="form-hint">Nomor order tertera pada struk dan halaman konfirmasi setelah checkout.</p>
                </div>

                <button type="submit" class="btn btn-primary w-full">Lacak</button>
            </form>
        </div>
    </div>
@endsection
