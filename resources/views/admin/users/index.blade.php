@extends('layouts.app')

@section('title', 'Pengguna - ' . config('app.name'))
@section('header', 'Pengguna')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Pengguna</h1>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Tambah Pengguna</a>
    </div>

    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-5">
        <div class="flex flex-wrap items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari pengguna..." class="input max-w-xs">
            <select name="role" class="select max-w-xs">
                <option value="">Semua Role</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}" @selected(request('role') === $role->name)>{{ $role->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary">Cari</button>
            @if (request('search') || request('role'))
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Reset</a>
            @endif
        </div>
    </form>

    <div class="card overflow-hidden">
        <table class="table-w">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Nama</th>
                    <th class="px-4 py-3 font-medium">Email</th>
                    <th class="px-4 py-3 font-medium">No. HP</th>
                    <th class="px-4 py-3 font-medium">Role</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Aktif</th>
                    <th class="px-4 py-3 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-4 py-3">{{ $user->name }}</td>
                        <td class="px-4 py-3">{{ $user->email }}</td>
                        <td class="px-4 py-3">{{ $user->phone }}</td>
                        <td class="px-4 py-3">{{ $user->getRoleNames()->join(', ') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                @if ($user->online)
                                    <span class="bg-emerald-100 p-1 rounded-full">
                                        <span class="block h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    </span>
                                    <span class="badge bg-emerald-100 text-emerald-700">Online</span>
                                @else
                                    <span class="bg-gray-200 p-1 rounded-full">
                                        <span class="block h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                                    </span>
                                    <span class="badge bg-gray-100 text-gray-600">
                                        {{ $user->last_seen_at ? 'Offline' : 'Belum pernah' }}
                                    </span>
                                @endif
                            </div>
                            @if ($user->last_seen_at)
                                <p class="mt-1 text-xs text-gray-400">{{ $user->last_seen_at->format('d M Y H:i') }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($user->is_active)
                                <span class="badge bg-emerald-100 text-emerald-700">Aktif</span>
                            @else
                                <span class="badge bg-gray-100 text-gray-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-secondary !px-3 !py-1 text-xs">Edit</a>
                                @if ($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Yakin ingin menghapus?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger !px-3 !py-1 text-xs">Nonaktifkan</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">Tidak ada data pengguna.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
@endsection
