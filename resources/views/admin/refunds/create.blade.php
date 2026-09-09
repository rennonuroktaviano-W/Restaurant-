@extends('layouts.app')

@section('title', 'Buat Refund - '.config('app.name'))
@section('header', 'Refund')

@section('content')
    <div class="mb-5">
        <a href="{{ route('admin.refunds.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">&larr; Kembali</a>
    </div>

    <form method="POST" action="{{ route('admin.refunds.store', $payment) }}" class="card mx-auto max-w-2xl p-6">
        @csrf

        <h1 class="mb-5 text-xl font-bold text-gray-900">Buat Refund</h1>

        <dl class="mb-6 grid gap-4 rounded-lg bg-gray-50 p-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs uppercase text-gray-500">Order</dt>
                <dd class="text-sm font-semibold text-gray-900">
                    <a href="{{ route('cashier.orders.show', $payment->order) }}" class="text-brand-600 hover:text-brand-700">{{ $payment->order?->order_number }}</a>
                </dd>
            </div>
            <div>
                <dt class="text-xs uppercase text-gray-500">Metode</dt>
                <dd class="text-sm font-semibold text-gray-900">{{ $payment->paymentMethod?->name }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase text-gray-500">Total Dibayar</dt>
                <dd class="text-sm font-semibold text-gray-900">Rp {{ number_format($payment->amount, 0, ',', '.') }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase text-gray-500">Sisa Dapat Direfund</dt>
                <dd class="text-sm font-semibold text-emerald-700">Rp {{ number_format($refundable, 0, ',', '.') }}</dd>
            </div>
        </dl>

        <div class="grid gap-4">
            <div>
                <label for="amount" class="label">Jumlah Refund (Rp)</label>
                <input id="amount" type="number" name="amount" value="{{ old('amount') }}" required min="0.01" max="{{ $refundable }}" step="0.01" class="input">
                @error('amount')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="reason_code" class="label">Kategori Alasan</label>
                <select id="reason_code" name="reason_code" class="select">
                    <option value="customer" @selected(old('reason_code') === 'customer')>Permintaan pelanggan</option>
                    <option value="damaged" @selected(old('reason_code') === 'damaged')>Barang tidak sesuai / rusak</option>
                    <option value="other" @selected(old('reason_code') === 'other')>Lainnya</option>
                </select>
            </div>

            <div>
                <label for="reason" class="label">Alasan Refund</label>
                <textarea id="reason" name="reason" rows="3" required maxlength="500" class="input">{{ old('reason') }}</textarea>
                @error('reason')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-6 flex items-center gap-3">
            <button type="submit" class="btn btn-primary">Proses Refund</button>
            <a href="{{ route('admin.refunds.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
@endsection