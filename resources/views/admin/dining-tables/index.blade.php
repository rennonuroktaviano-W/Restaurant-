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
            <select name="area" class="select max-w-xs">
                <option value="">Semua Area</option>
                @foreach ($areas as $area)
                    <option value="{{ $area->id }}" @selected(request('area') == $area->id)>{{ $area->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary">Filter</button>
            @if (request('area'))
                <a href="{{ route('admin.dining-tables.index') }}" class="btn btn-secondary">Reset</a>
            @endif
        </div>
    </form>

    <div class="card overflow-hidden">
        <table class="table-w">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Nomor</th>
                    <th class="px-4 py-3 font-medium">Nama</th>
                    <th class="px-4 py-3 font-medium">Area</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Aktif</th>
                    <th class="px-4 py-3 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($tables as $table)
                    <tr>
                        <td class="px-4 py-3">{{ $table->table_number }}</td>
                        <td class="px-4 py-3">{{ $table->name }}</td>
                        <td class="px-4 py-3">{{ $table->area->name }}</td>
                        <td class="px-4 py-3">
                            @if ($table->status === 'available')
                                <span class="badge bg-emerald-100 text-emerald-700">Tersedia</span>
                            @elseif ($table->status === 'occupied')
                                <span class="badge bg-amber-100 text-amber-700">Terisi</span>
                            @elseif ($table->status === 'reserved')
                                <span class="badge bg-blue-100 text-blue-700">Dipesan</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($table->is_active)
                                <span class="badge bg-emerald-100 text-emerald-700">Aktif</span>
                            @else
                                <span class="badge bg-gray-100 text-gray-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.tables.qr', $table) }}" class="btn btn-secondary !px-3 !py-1 text-xs">QR</a>
                                <a href="{{ route('admin.dining-tables.edit', $table) }}" class="btn btn-secondary !px-3 !py-1 text-xs">Edit</a>
                                <form method="POST" action="{{ route('admin.dining-tables.destroy', $table) }}" onsubmit="return confirm('Yakin ingin menghapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger !px-3 !py-1 text-xs">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">Tidak ada data meja.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $tables->links() }}
    </div>
@endsection
