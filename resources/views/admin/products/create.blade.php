@extends('layouts.app')

@section('title', __('admin.products.create').' - '.config('app.name'))
@section('header', __('admin.products.create'))

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink-900">{{ __('admin.products.create') }}</h1>
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">{{ __('admin.back') }}</a>
    </div>

    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="card card-pad">
        @csrf

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="category_id" class="label">{{ __('admin.category') }}</label>
                <select name="category_id" id="category_id" class="select @error('category_id') border-burgundy-500 @enderror" required>
                    <option value="">{{ __('admin.select_category') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) old('category_id', $selectedCategory ?? '') === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="sku" class="label">{{ __('admin.sku') }}</label>
                <input type="text" name="sku" id="sku" value="{{ old('sku') }}" class="input @error('sku') border-burgundy-500 @enderror" required>
                @error('sku')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="name" class="label">{{ __('admin.name') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" class="input @error('name') border-burgundy-500 @enderror" required>
                @error('name')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="slug" class="label">{{ __('admin.slug') }}</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug') }}" class="input @error('slug') border-burgundy-500 @enderror">
                @error('slug')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="image" class="label">{{ __('admin.image') }}</label>
                <input type="file" name="image" id="image" class="input @error('image') border-burgundy-500 @enderror">
                @error('image')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="cost_price" class="label">{{ __('admin.cost_price') }}</label>
                <input type="number" name="cost_price" id="cost_price" value="{{ old('cost_price') }}" class="input @error('cost_price') border-burgundy-500 @enderror" min="0">
                @error('cost_price')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="sale_price" class="label">{{ __('admin.sale_price') }}</label>
                <input type="number" name="sale_price" id="sale_price" value="{{ old('sale_price') }}" class="input @error('sale_price') border-burgundy-500 @enderror" min="0" required>
                @error('sale_price')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="stock_type" class="label">{{ __('admin.stock_type') }}</label>
                <select name="stock_type" id="stock_type" class="select @error('stock_type') border-burgundy-500 @enderror" required>
                    <option value="limited" @selected(old('stock_type') === 'limited')>{{ __('admin.limited') }}</option>
                    <option value="unlimited" @selected(old('stock_type') === 'unlimited')>{{ __('admin.unlimited') }}</option>
                </select>
                @error('stock_type')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="stock" class="label">{{ __('admin.stock') }}</label>
                <input type="number" name="stock" id="stock" value="{{ old('stock', 0) }}" class="input @error('stock') border-burgundy-500 @enderror" min="0">
                @error('stock')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="sort_order" class="label">{{ __('admin.sort_order') }}</label>
                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" class="input @error('sort_order') border-burgundy-500 @enderror">
                @error('sort_order')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="description" class="label">{{ __('admin.description') }}</label>
                <textarea name="description" id="description" rows="3" class="input @error('description') border-burgundy-500 @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <div class="flex flex-wrap gap-4">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="checkbox">
                        <span class="text-sm text-ink-700">{{ __('admin.is_active') }}</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_available" value="1" @checked(old('is_available', true)) class="checkbox">
                        <span class="text-sm text-ink-700">{{ __('admin.is_available') }}</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured')) class="checkbox">
                        <span class="text-sm text-ink-700">{{ __('admin.is_featured') }}</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_kitchen" value="1" @checked(old('is_kitchen')) class="checkbox">
                        <span class="text-sm text-ink-700">{{ __('admin.is_kitchen') }}</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="mt-6 flex items-center gap-2">
            <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">{{ __('admin.cancel') }}</a>
        </div>
    </form>
@endsection