@extends('layouts.app')

@section('title', 'Room - ' . config('app.name'))
@section('header', 'Room')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Room</h1>
        <a href="{{ route('admin.rooms.create') }}" class="btn btn-primary">Tambah Room</a>
    </div>

    <form method="GET" action="{{ route('admin.rooms.index') }}" class="mb-5">
        <div class="flex flex-wrap items-center gap-2">
            <select name="area" class="select max-w-xs">
                <option value="">Semua Area</option>
                @foreach ($areas as $area)
                    <option value="{{ $area->id }}" @selected(request('area') == $area->id)>{{ $area->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary">Filter</button>
            @if (request('area'))
                <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">Reset</a>
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
                @forelse ($rooms as $room)
                    <tr>
                        <td class="px-4 py-3">{{ $room->room_number }}</td>
                        <td class="px-4 py-3">{{ $room->name }}</td>
                        <td class="px-4 py-3">{{ $room->area->name }}</td>
                        <td class="px-4 py-3">
                            @if ($room->status === 'available')
                                <span class="badge bg-emerald-100 text-emerald-700">Tersedia</span>
                            @elseif ($room->status === 'occupied')
                                <span class="badge bg-amber-100 text-amber-700">Terisi</span>
                            @elseif ($room->status === 'reserved')
                                <span class="badge bg-blue-100 text-blue-700">Dipesan</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($room->is_active)
                                <span class="badge bg-emerald-100 text-emerald-700">Aktif</span>
                            @else
                                <span class="badge bg-gray-100 text-gray-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.rooms.edit', $room) }}" class="btn btn-secondary !px-3 !py-1 text-xs">Edit</a>
                                <form method="POST" action="{{ route('admin.rooms.destroy', $room) }}" onsubmit="return confirm('Yakin ingin menghapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger !px-3 !py-1 text-xs">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">Tidak ada data room.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $rooms->links() }}
    </div>
@endsection
