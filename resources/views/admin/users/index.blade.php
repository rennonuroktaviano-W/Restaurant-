@extends('layouts.app')

@section('title', __('admin.users.title').' - '.config('app.name'))
@section('header', __('admin.users.title'))

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink-900">{{ __('admin.users.title') }}</h1>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">{{ __('admin.add_new', ['item' => __('admin.users.title')]) }}</a>
    </div>

    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-5">
        <div class="flex flex-wrap items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.search_placeholder') }}" class="input max-w-xs">
            <select name="role" class="select max-w-xs">
                <option value="">{{ __('admin.all_roles') }}</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}" @selected(request('role') === $role->name)>{{ $role->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary">{{ __('admin.search') }}</button>
            @if (request('search') || request('role'))
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">{{ __('admin.reset') }}</a>
            @endif
        </div>
    </form>

    <div class="card overflow-hidden">
        <table class="table-admin">
            <thead>
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('admin.name') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.email') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.phone') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.role') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.is_active') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-900/10">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-4 py-3">{{ $user->name }}</td>
                        <td class="px-4 py-3">{{ $user->email }}</td>
                        <td class="px-4 py-3">{{ $user->phone }}</td>
                        <td class="px-4 py-3">{{ $user->getRoleNames()->join(', ') }}</td>
                        <td class="px-4 py-3">
                            @if ($user->is_active)
                                <span class="badge badge-forest">{{ __('admin.active') }}</span>
                            @else
                                <span class="badge badge-ink">{{ __('admin.inactive') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-secondary btn-sm">{{ __('admin.edit') }}</a>
                                @if ($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('{{ __('admin.confirm_delete') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">{{ __('admin.deactivate') }}</button>
                                    </form>
                                @endif
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
        {{ $users->links('partials.pagination') }}
    </div>
@endsection