@extends('layouts.app')

@section('title', 'Gudang - ' . config('app.name'))
@section('header', 'Gudang')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Gudang</h1>
        <a href="{{ route('admin.warehouses.create') }}" class="btn btn-primary">Tambah Gudang</a>
    </div>

    <form method="GET" action="{{ route('admin.warehouses.index') }}" class="mb-5">
        <div class="flex items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari gudang..." class="input max-w-xs">
            <button type="submit" class="btn btn-secondary">Cari</button>
            @if (request('search'))
                <a href="{{ route('admin.warehouses.index') }}" class="btn btn-secondary">Reset</a>
            @endif
        </div>
    </form>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($warehouses as $warehouse)
            <div class="card p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-brand-500/15 text-brand-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <span class="badge {{ $warehouse->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ $warehouse->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>

                <h2 class="mt-3 text-lg font-bold text-gray-900">{{ $warehouse->name }}</h2>
                <p class="mt-0.5 text-xs text-gray-500">/{{ $warehouse->slug }}</p>

                @if ($warehouse->address)
                    <p class="mt-2 text-sm text-gray-600">{{ $warehouse->address }}</p>
                @endif

                <div class="mt-3 flex items-center gap-4 text-xs text-gray-500">
                    <span><strong class="text-gray-800">{{ number_format($warehouse->inventory_items_count) }}</strong> item</span>
                </div>

                <div class="mt-4 flex items-center gap-2 border-t border-night-700 pt-4">
                    <a href="{{ route('admin.warehouses.edit', $warehouse) }}" class="btn btn-secondary btn-sm">Edit</a>
                    <form method="POST" action="{{ route('admin.warehouses.destroy', $warehouse) }}" onsubmit="return confirm('Yakin ingin menghapus gudang ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="card col-span-full p-8 text-center text-sm text-gray-500">
                Tidak ada data gudang.
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $warehouses->links() }}
    </div>
@endsection