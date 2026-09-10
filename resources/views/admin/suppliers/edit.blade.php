@extends('layouts.app')

@section('title', 'Edit Pemasok - ' . config('app.name'))
@section('header', 'Edit Pemasok')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Edit Pemasok</h1>
        <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary">Kembali</a>
    </div>

    <form method="POST" action="{{ route('admin.suppliers.update', $supplier) }}" class="card p-6">
        @csrf
        @method('PUT')

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="name" class="label">Nama</label>
                <input type="text" name="name" id="name" value="{{ old('name', $supplier->name) }}" class="input @error('name') border-red-400 @enderror" required>
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="contact_person" class="label">Nama Kontak</label>
                <input type="text" name="contact_person" id="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}" class="input @error('contact_person') border-red-400 @enderror">
                @error('contact_person')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone" class="label">Telepon</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $supplier->phone) }}" class="input @error('phone') border-red-400 @enderror">
                @error('phone')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="label">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $supplier->email) }}" class="input @error('email') border-red-400 @enderror">
                @error('email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="address" class="label">Alamat</label>
                <textarea name="address" id="address" rows="2" class="input @error('address') border-red-400 @enderror">{{ old('address', $supplier->address) }}</textarea>
                @error('address')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="notes" class="label">Catatan</label>
                <textarea name="notes" id="notes" rows="3" class="input @error('notes') border-red-400 @enderror">{{ old('notes', $supplier->notes) }}</textarea>
                @error('notes')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $supplier->is_active)) class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    <span class="text-sm text-gray-700">Aktif</span>
                </label>
            </div>
        </div>

        <div class="mt-6 flex items-center gap-2">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
@endsection