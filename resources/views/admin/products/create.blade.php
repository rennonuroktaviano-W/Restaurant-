@extends('layouts.app')

@section('title', 'Tambah Produk - ' . config('app.name'))
@section('header', 'Tambah Produk')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Tambah Produk</h1>
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Kembali</a>
    </div>

    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="card p-6">
        @csrf

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="category_id" class="label">Kategori</label>
                <select name="category_id" id="category_id" class="select @error('category_id') border-red-400 @enderror" required>
                    <option value="">Pilih Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="sku" class="label">SKU</label>
                <input type="text" name="sku" id="sku" value="{{ old('sku') }}" class="input @error('sku') border-red-400 @enderror" required>
                @error('sku')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="name" class="label">Nama</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" class="input @error('name') border-red-400 @enderror" required>
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="slug" class="label">Slug</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug') }}" class="input @error('slug') border-red-400 @enderror">
                @error('slug')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="image" class="label">Gambar</label>
                <input type="file" name="image" id="image" class="input @error('image') border-red-400 @enderror">
                @error('image')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="cost_price" class="label">Harga Pokok</label>
                <input type="number" name="cost_price" id="cost_price" value="{{ old('cost_price') }}" class="input @error('cost_price') border-red-400 @enderror" min="0">
                @error('cost_price')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="sale_price" class="label">Harga Jual</label>
                <input type="number" name="sale_price" id="sale_price" value="{{ old('sale_price') }}" class="input @error('sale_price') border-red-400 @enderror" min="0" required>
                @error('sale_price')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="stock_type" class="label">Tipe Stok</label>
                <select name="stock_type" id="stock_type" class="select @error('stock_type') border-red-400 @enderror" required>
                    <option value="limited" @selected(old('stock_type') === 'limited')>Terbatas</option>
                    <option value="unlimited" @selected(old('stock_type') === 'unlimited')>Tidak Terbatas</option>
                </select>
                @error('stock_type')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="stock" class="label">Stok</label>
                <input type="number" name="stock" id="stock" value="{{ old('stock', 0) }}" class="input @error('stock') border-red-400 @enderror" min="0">
                @error('stock')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="sort_order" class="label">Urutan</label>
                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" class="input @error('sort_order') border-red-400 @enderror">
                @error('sort_order')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="description" class="label">Deskripsi</label>
                <textarea name="description" id="description" rows="3" class="input @error('description') border-red-400 @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <div class="flex flex-wrap gap-4">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        <span class="text-sm text-gray-700">Aktif</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_available" value="1" @checked(old('is_available', true)) class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        <span class="text-sm text-gray-700">Tersedia</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured')) class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        <span class="text-sm text-gray-700">Unggulan</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_kitchen" value="1" @checked(old('is_kitchen')) class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        <span class="text-sm text-gray-700">Disiapkan dapur (KDS)</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="mt-6 flex items-center gap-2">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
@endsection
