@extends('layouts.app')

@section('title', __('admin.payment_methods.title').' - '.config('app.name'))
@section('header', __('admin.payment_methods.title'))

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink-900">{{ __('admin.payment_methods.title') }}</h1>
        <a href="{{ route('admin.payment-methods.create') }}" class="btn btn-primary">{{ __('admin.add_new', ['item' => __('admin.payment_methods.title')]) }}</a>
    </div>

    <div class="card overflow-hidden">
        <table class="table-admin">
            <thead>
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('admin.name') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.code') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.type') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.sort_order') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.status') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-900/10">
                @forelse ($methods as $method)
                    <tr>
                        <td class="px-4 py-3 font-medium text-ink-900">{{ $method->name }}</td>
                        <td class="px-4 py-3 text-ink-600">{{ $method->code }}</td>
                        <td class="px-4 py-3">
                            <span class="badge {{ $method->type === 'online' ? 'badge-gold' : 'badge-forest' }}">
                                {{ $method->type === 'online' ? __('admin.payment_methods.online') : __('admin.payment_methods.cash') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-ink-600">{{ $method->sort_order }}</td>
                        <td class="px-4 py-3">
                            <span class="badge {{ $method->is_active ? 'badge-forest' : 'badge-ink' }}">
                                {{ $method->is_active ? __('admin.active') : __('admin.inactive') }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.payment-methods.edit', $method) }}" class="btn btn-secondary btn-sm">{{ __('admin.edit') }}</a>
                                <form method="POST" action="{{ route('admin.payment-methods.destroy', $method) }}" onsubmit="return confirm('{{ __('admin.confirm_delete') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">{{ __('admin.delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-sm text-ink-500">{{ __('admin.no_data') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection