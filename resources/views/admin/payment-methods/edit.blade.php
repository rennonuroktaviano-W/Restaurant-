@extends('layouts.app')

@section('title', 'Edit Metode Pembayaran - '.config('app.name'))
@section('header', 'Metode Pembayaran')

@section('content')
    <div class="mb-5">
        <a href="{{ route('admin.payment-methods.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">&larr; Kembali</a>
    </div>

    <form method="POST" action="{{ route('admin.payment-methods.update', $method) }}" class="card mx-auto max-w-2xl p-6">
        @csrf
        @method('PUT')

        <h1 class="mb-5 text-xl font-bold text-gray-900">Edit Metode Pembayaran</h1>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="name" class="label">Nama</label>
                <input id="name" type="text" name="name" value="{{ old('name', $method->name) }}" required class="input">
            </div>

            <div>
                <label for="code" class="label">Kode</label>
                <input id="code" type="text" name="code" value="{{ old('code', $method->code) }}" required class="input">
            </div>

            <div>
                <label for="type" class="label">Tipe</label>
                <select id="type" name="type" class="select" required>
                    <option value="cash" {{ old('type', $method->type) === 'cash' ? 'selected' : '' }}>Tunai</option>
                    <option value="online" {{ old('type', $method->type) === 'online' ? 'selected' : '' }}>Online</option>
                </select>
            </div>

            <div>
                <label for="sort_order" class="label">Urutan</label>
                <input id="sort_order" type="number" name="sort_order" value="{{ old('sort_order', $method->sort_order) }}" min="0" max="9999" class="input">
            </div>
        </div>

        <div class="mt-4">
            <label for="config" class="label">Konfigurasi (JSON)</label>
            <textarea id="config" name="config" rows="4" class="input font-mono">{{ old('config', json_encode($method->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) }}</textarea>
            <p class="mt-1 text-xs text-gray-400">Opsional. Untuk pembayaran online digunakan provider gateway (mis. <code>mock</code>).</p>
        </div>

        <div class="mt-4">
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $method->is_active)) class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                Aktif
            </label>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
@endsection