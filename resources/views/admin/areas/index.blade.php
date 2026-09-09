@extends('layouts.app')

@section('title', 'Area - ' . config('app.name'))
@section('header', 'Area')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Area</h1>
        <a href="{{ route('admin.areas.create') }}" class="btn btn-primary">Tambah Area</a>
    </div>

    <div class="card overflow-hidden">
        <table class="table-w">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Nama</th>
                    <th class="px-4 py-3 font-medium">Slug</th>
                    <th class="px-4 py-3 font-medium">Tipe</th>
                    <th class="px-4 py-3 font-medium">Meja</th>
                    <th class="px-4 py-3 font-medium">Room</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($areas as $area)
                    <tr>
                        <td class="px-4 py-3">{{ $area->name }}</td>
                        <td class="px-4 py-3">{{ $area->slug }}</td>
                        <td class="px-4 py-3">{{ $area->type }}</td>
                        <td class="px-4 py-3">{{ $area->dining_tables_count }}</td>
                        <td class="px-4 py-3">{{ $area->rooms_count }}</td>
                        <td class="px-4 py-3">
                            @if ($area->is_active)
                                <span class="badge bg-emerald-100 text-emerald-700">Aktif</span>
                            @else
                                <span class="badge bg-gray-100 text-gray-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.areas.edit', $area) }}" class="btn btn-secondary !px-3 !py-1 text-xs">Edit</a>
                                <form method="POST" action="{{ route('admin.areas.destroy', $area) }}" onsubmit="return confirm('Yakin ingin menghapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger !px-3 !py-1 text-xs">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">Tidak ada data area.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $areas->links() }}
    </div>
@endsection
