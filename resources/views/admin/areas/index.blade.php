@extends('layouts.app')

@section('title', __('admin.areas.title').' - '.config('app.name'))
@section('header', __('admin.areas.title'))

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink-900">{{ __('admin.areas.title') }}</h1>
        <a href="{{ route('admin.areas.create') }}" class="btn btn-primary">{{ __('admin.add_new', ['item' => __('admin.areas.title')]) }}</a>
    </div>

    <form method="GET" action="{{ route('admin.areas.index') }}" class="mb-5">
        <div class="flex items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari area..." class="input max-w-xs">
            <button type="submit" class="btn btn-secondary">Cari</button>
            @if (request('search'))
                <a href="{{ route('admin.areas.index') }}" class="btn btn-secondary">Reset</a>
            @endif
        </div>
    </form>

    <div class="card overflow-hidden">
        <table class="table-admin">
            <thead>
                <tr>
                    <th class="px-4 py-3 font-medium">Nama</th>
                    <th class="px-4 py-3 font-medium">Lokasi</th>
                    <th class="px-4 py-3 font-medium">Jam Buka</th>
                    <th class="px-4 py-3 font-medium">Meja</th>
                    <th class="px-4 py-3 font-medium">Room</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-900/10">
                @forelse ($areas as $area)
                    <tr>
                        <td class="px-4 py-3">
                            <span class="font-medium text-gray-900">{{ $area->name }}</span>
                            <span class="ml-1.5 badge bg-brand-100 text-brand-700">{{ $area->type }}</span>
                            <span class="block text-xs text-gray-500">/{{ $area->slug }}</span>
                            @if ($area->description)
                                <span class="mt-1 block max-w-[240px] truncate text-xs text-gray-500" title="{{ $area->description }}">{{ $area->description }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($area->address)
                                @if (filter_var($area->address, FILTER_VALIDATE_URL))
                                    <span class="block max-w-[260px] truncate text-sm text-gray-600" title="{{ $area->address }}">Link Google Maps</span>
                                @else
                                    <span class="block max-w-[260px] text-sm text-gray-600" title="{{ $area->address }}">{{ $area->address }}</span>
                                @endif
                                <a href="{{ $area->maps_url }}" target="_blank" rel="noopener" class="mt-1 inline-flex items-center gap-1 text-xs font-medium text-brand-600 hover:underline">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    Buka di Maps
                                </a>
                            @else
                                <span class="text-sm text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                            @if ($area->hours_label)
                                {{ $area->hours_label }}
                            @else
                                <span class="text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $area->dining_tables_count }}</td>
                        <td class="px-4 py-3">{{ $area->rooms_count }}</td>
                        <td class="px-4 py-3">
                            @if ($area->is_active)
                                <span class="badge badge-forest">{{ __('admin.active') }}</span>
                            @else
                                <span class="badge badge-ink">{{ __('admin.inactive') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.areas.edit', $area) }}" class="btn btn-secondary btn-sm">{{ __('admin.edit') }}</a>
                                <form method="POST" action="{{ route('admin.areas.destroy', $area) }}" onsubmit="return confirm('{{ __('admin.confirm_delete') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">{{ __('admin.delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-ink-500">{{ __('admin.no_data') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $areas->links('partials.pagination') }}
    </div>
@endsection