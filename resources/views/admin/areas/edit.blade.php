@extends('layouts.app')

@section('title', 'Edit Area - ' . config('app.name'))
@section('header', 'Edit Area')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Edit Area</h1>
        <a href="{{ route('admin.areas.index') }}" class="btn btn-secondary">Kembali</a>
    </div>

    <form method="POST" action="{{ route('admin.areas.update', $area) }}" class="card p-6">
        @csrf
        @method('PUT')

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="name" class="label">Nama</label>
                <input type="text" name="name" id="name" value="{{ old('name', $area->name) }}" class="input @error('name') border-red-400 @enderror" required>
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="slug" class="label">Slug</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug', $area->slug) }}" class="input @error('slug') border-red-400 @enderror">
                @error('slug')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="type" class="label">Tipe</label>
                <select name="type" id="type" class="select @error('type') border-red-400 @enderror" required>
                    <option value="restaurant" @selected(old('type', $area->type) === 'restaurant')>Restaurant</option>
                    <option value="pool" @selected(old('type', $area->type) === 'pool')>Pool</option>
                    <option value="room" @selected(old('type', $area->type) === 'room')>Room</option>
                    <option value="villa" @selected(old('type', $area->type) === 'villa')>Villa</option>
                    <option value="other" @selected(old('type', $area->type) === 'other')>Lainnya</option>
                </select>
                @error('type')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="sort_order" class="label">Urutan</label>
                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $area->sort_order) }}" class="input @error('sort_order') border-red-400 @enderror">
                @error('sort_order')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="description" class="label">Deskripsi</label>
                <textarea name="description" id="description" rows="3" class="input @error('description') border-red-400 @enderror">{{ old('description', $area->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="address" class="label">Alamat Lokasi</label>
                <textarea name="address" id="address" rows="2" class="input @error('address') border-red-400 @enderror" placeholder="contoh: Jl. Raya Senggigi, Lombok Utara">{{ old('address', $area->address) }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Alamat ini digunakan untuk menautkan lokasi ke Google Maps.</p>
                @error('address')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $area->is_active)) class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    <span class="text-sm text-gray-700">Aktif</span>
                </label>
            </div>
        </div>

        <div class="mt-6 flex items-center gap-2">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.areas.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
@endsection
