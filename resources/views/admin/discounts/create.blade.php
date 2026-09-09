@extends('layouts.app')

@section('title', 'Tambah Promo - '.config('app.name'))
@section('header', 'Promo & Diskon')

@section('content')
    <div class="mb-5">
        <a href="{{ route('admin.discounts.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">&larr; Kembali</a>
    </div>

    <form method="POST" action="{{ route('admin.discounts.store') }}" class="card mx-auto max-w-3xl p-6">
        @csrf

        <h1 class="mb-5 text-xl font-bold text-gray-900">Tambah Promo</h1>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="name" class="label">Nama Promo</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required class="input">
            </div>

            <div>
                <label for="code" class="label">Kode</label>
                <input id="code" type="text" name="code" value="{{ old('code') }}" class="input" placeholder="Opsional (untuk diskon kode)">
            </div>

            <div>
                <label for="type" class="label">Jenis</label>
                <select id="type" name="type" class="select" required>
                    <option value="percentage" {{ old('type') === 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                    <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>Nominal (Rp)</option>
                </select>
            </div>

            <div>
                <label for="value" class="label">Nilai</label>
                <input id="value" type="number" name="value" value="{{ old('value') }}" required min="0" step="0.01" class="input">
            </div>

            <div>
                <label for="min_amount" class="label">Min. Pembelian (Rp)</label>
                <input id="min_amount" type="number" name="min_amount" value="{{ old('min_amount') }}" min="0" step="0.01" class="input">
            </div>

            <div>
                <label for="max_amount" class="label">Maks. Potongan (Rp)</label>
                <input id="max_amount" type="number" name="max_amount" value="{{ old('max_amount') }}" min="0" step="0.01" class="input">
            </div>

            <div>
                <label for="starts_at" class="label">Mulai</label>
                <input id="starts_at" type="date" name="starts_at" value="{{ old('starts_at') }}" class="input">
            </div>

            <div>
                <label for="ends_at" class="label">Berakhir</label>
                <input id="ends_at" type="date" name="ends_at" value="{{ old('ends_at') }}" class="input">
            </div>
        </div>

        <div class="mt-5 rounded-lg border border-gray-200 p-4">
            <h2 class="mb-2 text-sm font-semibold text-gray-900">Sasaran Diskon</h2>
            <p class="mb-3 text-xs text-gray-400">Kosongkan untuk berlaku ke seluruh menu.</p>

            <div>
                <h3 class="mb-1 text-xs font-medium uppercase text-gray-500">Kategori</h3>
                <div class="flex flex-wrap gap-3">
                    @foreach ($categories as $category)
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="hidden" name="discount_items[c{{ $category->id }}][target_type]" value="category">
                            <input type="checkbox" name="discount_items[c{{ $category->id }}][target_id]" value="{{ $category->id }}" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            {{ $category->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="mt-4">
                <h3 class="mb-1 text-xs font-medium uppercase text-gray-500">Produk</h3>
                <div class="max-h-48 overflow-y-auto rounded-lg border border-gray-200 p-2">
                    <div class="flex flex-wrap gap-3">
                        @foreach ($products as $product)
                            <label class="flex min-w-44 items-center gap-2 text-sm text-gray-700">
                                <input type="hidden" name="discount_items[p{{ $product->id }}][target_type]" value="product">
                                <input type="checkbox" name="discount_items[p{{ $product->id }}][target_id]" value="{{ $product->id }}" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                {{ $product->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="is_automatic" value="1" @checked(old('is_automatic')) class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                Terapkan otomatis (tanpa kode)
            </label>

            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                Aktif
            </label>
        </div>

        <div class="mt-6">
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
@endsection