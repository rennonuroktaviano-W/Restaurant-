@extends('layouts.app')

@section('title', 'Meja Makan - ' . config('app.name'))
@section('header', 'Meja Makan')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Meja Makan</h1>
        <a href="{{ route('admin.dining-tables.create') }}" class="btn btn-primary">Tambah Meja</a>
    </div>

    <form method="GET" action="{{ route('admin.dining-tables.index') }}" class="mb-5">
        <div class="flex flex-wrap items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor meja..." class="input max-w-xs">
            <select name="area" class="select max-w-xs">
                <option value="">Semua Area</option>
                @foreach ($areas as $area)
                    <option value="{{ $area->id }}" @selected(request('area') == $area->id)>{{ $area->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary">Filter</button>
            @if (request('area') || request('search'))
                <a href="{{ route('admin.dining-tables.index') }}" class="btn btn-secondary">Reset</a>
            @endif
        </div>
    </form>

    @php
        $statusMeta = [
            'available' => ['Tersedia', 'bg-emerald-100 text-emerald-700', 'border-emerald-500/60'],
            'occupied' => ['Terisi', 'bg-amber-100 text-amber-700', 'border-amber-500/60'],
            'reserved' => ['Dipesan', 'bg-blue-100 text-blue-700', 'border-blue-500/60'],
        ];
    @endphp

    @forelse ($tables->groupBy(fn ($t) => $t->area?->name ?? 'Tanpa area') as $areaName => $areaTables)
        <div class="mb-8">
            <h2 class="mb-3 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                {{ $areaName }}
            </h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($areaTables as $table)
                    @php
                        [$statusLabel, $statusClass, $borderClass] = $statusMeta[$table->status] ?? [$table->status, 'bg-gray-100 text-gray-600', 'border-gray-500/60'];
                    @endphp
                    <div class="card border-l-4 {{ $borderClass }} p-4">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xl font-bold text-gray-900">{{ $table->table_number }}</p>
                                @if ($table->name)
                                    <p class="text-sm text-gray-500">{{ $table->name }}</p>
                                @endif
                            </div>
                            <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                        </div>

                        <div class="mt-3 flex items-center gap-3 text-xs text-gray-500">
                            <span class="inline-flex items-center gap-1">
                                @if ($table->is_active)
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Aktif
                                @else
                                    <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span> Nonaktif
                                @endif
                            </span>
                        </div>

                        <div class="mt-4 flex items-center gap-2 border-t border-night-700 pt-3">
                            <a href="{{ route('admin.tables.qr', $table) }}" class="btn btn-secondary !px-3 !py-1 text-xs">QR</a>
                            <a href="{{ route('admin.dining-tables.edit', $table) }}" class="btn btn-secondary !px-3 !py-1 text-xs">Edit</a>
                            <form method="POST" action="{{ route('admin.dining-tables.destroy', $table) }}" onsubmit="return confirm('Yakin ingin menghapus?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger !px-3 !py-1 text-xs">Hapus</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="card p-8 text-center text-sm text-gray-500">Tidak ada data meja.</div>
    @endforelse

    <div class="mt-4">
        {{ $tables->links() }}
    </div>
@endsection