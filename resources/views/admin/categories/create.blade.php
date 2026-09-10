@extends('layouts.app')

@section('title', __('admin.categories.create').' - '.config('app.name'))
@section('header', __('admin.categories.create'))

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink-900">{{ __('admin.categories.create') }}</h1>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">{{ __('admin.back') }}</a>
    </div>

    <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="card card-pad">
        @csrf

        <div class="grid gap-4 sm:grid-cols-2">
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

            <div class="sm:col-span-2">
                <label for="description" class="label">{{ __('admin.description') }}</label>
                <textarea name="description" id="description" rows="3" class="input @error('description') border-burgundy-500 @enderror">{{ old('description') }}</textarea>
                @error('description')
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
                <label for="sort_order" class="label">{{ __('admin.sort_order') }}</label>
                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" class="input @error('sort_order') border-burgundy-500 @enderror">
                @error('sort_order')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="checkbox">
                    <span class="text-sm text-ink-700">{{ __('admin.is_active') }}</span>
                </label>
            </div>
        </div>

        <div class="mt-6 flex items-center gap-2">
            <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">{{ __('admin.cancel') }}</a>
        </div>
    </form>
@endsection