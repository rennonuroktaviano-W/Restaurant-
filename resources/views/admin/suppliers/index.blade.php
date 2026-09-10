@extends('layouts.app')

@section('title', 'Pemasok - ' . config('app.name'))
@section('header', 'Pemasok')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Pemasok</h1>
        <a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary">Tambah Pemasok</a>
    </div>

    <form method="GET" action="{{ route('admin.suppliers.index') }}" class="mb-5">
        <div class="flex items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, atau telepon..." class="input max-w-xs">
            <button type="submit" class="btn btn-secondary">Cari</button>
            @if (request('search'))
                <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary">Reset</a>
            @endif
        </div>
    </form>

    <div class="card overflow-hidden">
        <table class="table-w">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Nama</th>
                    <th class="px-4 py-3 font-medium">Kontak</th>
                    <th class="px-4 py-3 font-medium">Telepon</th>
                    <th class="px-4 py-3 font-medium">Email</th>
                    <th class="px-4 py-3 font-medium">Transaksi</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($suppliers as $supplier)
                    <tr>
                        <td class="px-4 py-3">
                            <span class="font-medium text-gray-900">{{ $supplier->name }}</span>
                            @if ($supplier->contact_person)
                                <span class="block text-xs text-gray-500">{{ $supplier->contact_person }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $supplier->address ?: '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $supplier->phone ?: '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $supplier->email ?: '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ number_format($supplier->movements_count) }}</td>
                        <td class="px-4 py-3">
                            @if ($supplier->is_active)
                                <span class="badge bg-emerald-100 text-emerald-700">Aktif</span>
                            @else
                                <span class="badge bg-gray-100 text-gray-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-secondary !px-3 !py-1 text-xs">Edit</a>
                                <form method="POST" action="{{ route('admin.suppliers.destroy', $supplier) }}" onsubmit="return confirm('Yakin ingin menghapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger !px-3 !py-1 text-xs">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">Tidak ada data pemasok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $suppliers->links() }}
    </div>
@endsection