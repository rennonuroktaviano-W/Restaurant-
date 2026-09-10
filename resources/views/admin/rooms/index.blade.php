@extends('layouts.app')

@section('title', __('admin.rooms.title').' - '.config('app.name'))
@section('header', __('admin.rooms.title'))

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink-900">{{ __('admin.rooms.title') }}</h1>
        <a href="{{ route('admin.rooms.create') }}" class="btn btn-primary">{{ __('admin.add_new', ['item' => __('admin.rooms.title')]) }}</a>
    </div>

    <form method="GET" action="{{ route('admin.rooms.index') }}" class="mb-5">
        <div class="flex flex-wrap items-center gap-2">
            <select name="area" class="select max-w-xs">
                <option value="">{{ __('admin.all_areas') }}</option>
                @foreach ($areas as $area)
                    <option value="{{ $area->id }}" @selected(request('area') == $area->id)>{{ $area->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary">{{ __('admin.filter') }}</button>
            @if (request('area'))
                <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">{{ __('admin.reset') }}</a>
            @endif
        </div>
    </form>

    <div class="card overflow-hidden">
        <table class="table-admin">
            <thead>
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('admin.rooms.room_number') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.rooms.room_name') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.area') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.rooms.status') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.is_active') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-900/10">
                @forelse ($rooms as $room)
                    <tr>
                        <td class="px-4 py-3">{{ $room->room_number }}</td>
                        <td class="px-4 py-3">{{ $room->name }}</td>
                        <td class="px-4 py-3">{{ $room->area->name }}</td>
                        <td class="px-4 py-3">
                            @if ($room->status === 'available')
                                <span class="badge badge-forest">{{ __('admin.rooms.available') }}</span>
                            @elseif ($room->status === 'occupied')
                                <span class="badge badge-gold">{{ __('admin.rooms.occupied') }}</span>
                            @elseif ($room->status === 'reserved')
                                <span class="badge badge-ink">{{ __('admin.rooms.reserved') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($room->is_active)
                                <span class="badge badge-forest">{{ __('admin.active') }}</span>
                            @else
                                <span class="badge badge-ink">{{ __('admin.inactive') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.rooms.qr', $room) }}" class="btn btn-secondary btn-sm">{{ __('admin.rooms.qr_code') }}</a>
                                <a href="{{ route('admin.rooms.edit', $room) }}" class="btn btn-secondary btn-sm">{{ __('admin.edit') }}</a>
                                <form method="POST" action="{{ route('admin.rooms.destroy', $room) }}" onsubmit="return confirm('{{ __('admin.confirm_delete') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">{{ __('admin.delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-ink-500">{{ __('admin.no_data') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $rooms->links('partials.pagination') }}
    </div>
@endsection