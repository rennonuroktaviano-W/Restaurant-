@extends('layouts.app')

@section('title', __('admin.areas.edit').' - '.config('app.name'))
@section('header', __('admin.areas.edit'))

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink-900">{{ __('admin.areas.edit') }}</h1>
        <a href="{{ route('admin.areas.index') }}" class="btn btn-secondary">{{ __('admin.back') }}</a>
    </div>

    <form method="POST" action="{{ route('admin.areas.update', $area) }}" class="card card-pad">
        @csrf
        @method('PUT')

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="name" class="label">{{ __('admin.areas.name') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name', $area->name) }}" class="input @error('name') border-burgundy-500 @enderror" required>
                @error('name')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="slug" class="label">{{ __('admin.slug') }}</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug', $area->slug) }}" class="input @error('slug') border-burgundy-500 @enderror">
                @error('slug')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="type" class="label">{{ __('admin.areas.type') }}</label>
                <select name="type" id="type" class="select @error('type') border-burgundy-500 @enderror" required>
                    <option value="restaurant" @selected(old('type', $area->type) === 'restaurant')>{{ __('admin.areas.restaurant') }}</option>
                    <option value="pool" @selected(old('type', $area->type) === 'pool')>{{ __('admin.areas.pool') }}</option>
                    <option value="room" @selected(old('type', $area->type) === 'room')>{{ __('admin.areas.room') }}</option>
                    <option value="villa" @selected(old('type', $area->type) === 'villa')>{{ __('admin.areas.villa') }}</option>
                    <option value="other" @selected(old('type', $area->type) === 'other')>{{ __('admin.areas.other') }}</option>
                </select>
                @error('type')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="sort_order" class="label">{{ __('admin.sort_order') }}</label>
                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $area->sort_order) }}" class="input @error('sort_order') border-burgundy-500 @enderror">
                @error('sort_order')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="description" class="label">{{ __('admin.description') }}</label>
                <textarea name="description" id="description" rows="3" class="input @error('description') border-burgundy-500 @enderror">{{ old('description', $area->description) }}</textarea>
                @error('description')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="address" class="label">Link Google Maps</label>
                <input type="url" name="address" id="address" value="{{ old('address', $area->address) }}" class="input @error('address') border-red-400 @enderror" placeholder="https://www.google.com/maps/search/?api=1&query=-6.9,107.6">
                <p class="mt-1 text-xs text-gray-500">Tempel link berbagi dari Google Maps untuk lokasi area ini — dipakai tombol "Menuju Restaurant" dan peta di halaman landing.</p>
                @error('address')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="latitude" class="label">Latitude</label>
                <input type="number" name="latitude" id="latitude" step="any" value="{{ old('latitude', $area->latitude) }}" class="input @error('latitude') border-red-400 @enderror" placeholder="contoh: -6.9043">
                <p class="mt-1 text-xs text-gray-500">Jika diisi, peta memakai koordinat ini (presisi lebih akurat).</p>
                @error('latitude')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="longitude" class="label">Longitude</label>
                <input type="number" name="longitude" id="longitude" step="any" value="{{ old('longitude', $area->longitude) }}" class="input @error('longitude') border-red-400 @enderror" placeholder="contoh: 107.6181">
                @error('longitude')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="open_time" class="label">Jam Buka</label>
                <input type="time" name="open_time" id="open_time" value="{{ old('open_time', $area->open_time) }}" class="input @error('open_time') border-red-400 @enderror">
                @error('open_time')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="close_time" class="label">Jam Tutup</label>
                <input type="time" name="close_time" id="close_time" value="{{ old('close_time', $area->close_time) }}" class="input @error('close_time') border-red-400 @enderror">
                @error('close_time')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $area->is_active)) class="checkbox">
                    <span class="text-sm text-ink-700">{{ __('admin.is_active') }}</span>
                </label>
            </div>
        </div>

        <div class="mt-6 flex items-center gap-2">
            <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
            <a href="{{ route('admin.areas.index') }}" class="btn btn-secondary">{{ __('admin.cancel') }}</a>
        </div>
    </form>
@endsection