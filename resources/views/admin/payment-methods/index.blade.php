@extends('layouts.app')

@section('title', 'Metode Pembayaran - '.config('app.name'))
@section('header', 'Metode Pembayaran')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Metode Pembayaran</h1>
        <a href="{{ route('admin.payment-methods.create') }}" class="btn btn-primary">Tambah Metode</a>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
        <table class="table-w">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Nama</th>
                    <th class="px-4 py-3 font-medium">Kode</th>
                    <th class="px-4 py-3 font-medium">Tipe</th>
                    <th class="px-4 py-3 font-medium">Urutan</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($methods as $method)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $method->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $method->code }}</td>
                        <td class="px-4 py-3">
                            <span class="badge {{ $method->type === 'online' ? 'bg-brand-100 text-brand-700' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ $method->type === 'online' ? 'Online' : 'Tunai' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $method->sort_order }}</td>
                        <td class="px-4 py-3">
                            <span class="badge {{ $method->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $method->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.payment-methods.edit', $method) }}" class="btn btn-secondary !px-3 !py-1 text-xs">Edit</a>
                                <form method="POST" action="{{ route('admin.payment-methods.destroy', $method) }}" onsubmit="return confirm('Yakin ingin menghapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger !px-3 !py-1 text-xs">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">Belum ada metode pembayaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
@endsection