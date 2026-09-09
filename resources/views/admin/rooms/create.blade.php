@extends('layouts.app')

@section('title', 'Tambah Room - ' . config('app.name'))
@section('header', 'Tambah Room')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Tambah Room</h1>
        <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">Kembali</a>
    </div>

    <form method="POST" action="{{ route('admin.rooms.store') }}" class="card p-6">
        @csrf

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="area_id" class="label">Area</label>
                <select name="area_id" id="area_id" class="select @error('area_id') border-red-400 @enderror" required>
                    <option value="">Pilih Area</option>
                    @foreach ($areas as $area)
                        <option value="{{ $area->id }}" @selected(old('area_id') == $area->id)>{{ $area->name }}</option>
                    @endforeach
                </select>
                @error('area_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="room_number" class="label">Nomor Room</label>
                <input type="text" name="room_number" id="room_number" value="{{ old('room_number') }}" class="input @error('room_number') border-red-400 @enderror" required>
                @error('room_number')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="name" class="label">Nama</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" class="input @error('name') border-red-400 @enderror">
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="status" class="label">Status</label>
                <select name="status" id="status" class="select @error('status') border-red-400 @enderror" required>
                    <option value="available" @selected(old('status') === 'available')>Tersedia</option>
                    <option value="occupied" @selected(old('status') === 'occupied')>Terisi</option>
                    <option value="reserved" @selected(old('status') === 'reserved')>Dipesan</option>
                </select>
                @error('status')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    <span class="text-sm text-gray-700">Aktif</span>
                </label>
            </div>
        </div>

        <div class="mt-6 flex items-center gap-2">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
@endsection
