@extends('layouts.app')

@section('title', __('admin.areas.title').' - '.config('app.name'))
@section('header', __('admin.areas.title'))

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink-900">{{ __('admin.areas.title') }}</h1>
        <a href="{{ route('admin.areas.create') }}" class="btn btn-primary">{{ __('admin.add_new', ['item' => __('admin.areas.title')]) }}</a>
    </div>

    <div class="card overflow-hidden">
        <table class="table-admin">
            <thead>
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('admin.name') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.slug') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.type') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.tables') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.rooms') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.status') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-900/10">
                @forelse ($areas as $area)
                    <tr>
                        <td class="px-4 py-3">{{ $area->name }}</td>
                        <td class="px-4 py-3">{{ $area->slug }}</td>
                        <td class="px-4 py-3">{{ $area->type }}</td>
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