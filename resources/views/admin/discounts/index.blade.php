@extends('layouts.app')

@section('title', 'Promo & Diskon - '.config('app.name'))
@section('header', 'Promo & Diskon')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Promo & Diskon</h1>
        <a href="{{ route('admin.discounts.create') }}" class="btn btn-primary">Tambah Promo</a>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
        <table class="table-w">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Nama</th>
                    <th class="px-4 py-3 font-medium">Kode</th>
                    <th class="px-4 py-3 font-medium">Jenis</th>
                    <th class="px-4 py-3 font-medium">Nilai</th>
                    <th class="px-4 py-3 font-medium">Sasaran</th>
                    <th class="px-4 py-3 font-medium">Periode</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($discounts as $discount)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if ($discount->image)
                                    <img src="{{ asset('storage/'.$discount->image) }}" alt="{{ $discount->name }}" class="h-10 w-16 shrink-0 rounded-md border border-gray-200 object-cover">
                                @endif
                                <div>
                                    <p class="font-medium text-gray-900">{{ $discount->name }}</p>
                                    @if ($discount->description)
                                        <p class="max-w-56 truncate text-xs text-gray-500">{{ $discount->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @if ($discount->code)
                                <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-700">{{ $discount->code }}</code>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $discount->type === 'percentage' ? 'Persentase' : 'Nominal' }}</td>
                        <td class="px-4 py-3 text-gray-900">
                            {{ $discount->type === 'percentage' ? $discount->value.'%' : 'Rp '.number_format($discount->value, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $discount->is_automatic ? 'Otomatis' : ($discount->code ? 'Kode' : '—') }}
                            ({{ $discount->items_count }} target)
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600">
                            @if ($discount->starts_at || $discount->ends_at)
                                {{ $discount->starts_at?->format('d/m/Y') ?? '…' }} — {{ $discount->ends_at?->format('d/m/Y') ?? '∞' }}
                            @else
                                <span class="text-gray-400">Selalu</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="badge {{ $discount->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $discount->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.discounts.edit', $discount) }}" class="btn btn-secondary !px-3 !py-1 text-xs">Edit</a>
                                <form method="POST" action="{{ route('admin.discounts.destroy', $discount) }}" onsubmit="return confirm('Yakin ingin menghapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger !px-3 !py-1 text-xs">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-sm text-gray-500">Belum ada promo.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="mt-4">{{ $discounts->links() }}</div>
@endsection