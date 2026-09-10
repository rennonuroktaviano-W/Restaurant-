@extends('layouts.app')

@section('title', __('admin.discounts.title').' - '.config('app.name'))
@section('header', __('admin.discounts.title'))

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink-900">{{ __('admin.discounts.title') }}</h1>
        <a href="{{ route('admin.discounts.create') }}" class="btn btn-primary">{{ __('admin.add_new', ['item' => __('admin.discounts.title')]) }}</a>
    </div>

    <div class="card overflow-hidden">
        <table class="table-admin">
            <thead>
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('admin.name') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.discounts.code') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.discounts.discount_type') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.discounts.value') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.discounts.target') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.discounts.period') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.status') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-900/10">
                @forelse ($discounts as $discount)
                    <tr>
                        <td class="px-4 py-3 font-medium text-ink-900">{{ $discount->name }}</td>
                        <td class="px-4 py-3">
                            @if ($discount->code)
                                <code class="rounded bg-ink-100 px-1.5 py-0.5 text-xs text-ink-700">{{ $discount->code }}</code>
                            @else
                                <span class="text-xs text-ink-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-ink-600">{{ $discount->type === 'percentage' ? __('admin.discounts.percentage') : __('admin.discounts.nominal') }}</td>
                        <td class="px-4 py-3 text-ink-900">
                            {{ $discount->type === 'percentage' ? $discount->value.'%' : 'Rp '.number_format($discount->value, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-ink-600">
                            {{ $discount->is_automatic ? __('admin.discounts.auto') : ($discount->code ? __('admin.discounts.code') : '—') }}
                            ({{ $discount->items_count }} {{ __('admin.targets') }})
                        </td>
                        <td class="px-4 py-3 text-xs text-ink-600">
                            @if ($discount->starts_at || $discount->ends_at)
                                {{ $discount->starts_at?->format('d/m/Y') ?? '…' }} — {{ $discount->ends_at?->format('d/m/Y') ?? '∞' }}
                            @else
                                <span class="text-ink-400">{{ __('admin.discounts.always') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="badge {{ $discount->is_active ? 'badge-forest' : 'badge-ink' }}">
                                {{ $discount->is_active ? __('admin.active') : __('admin.inactive') }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.discounts.edit', $discount) }}" class="btn btn-secondary btn-sm">{{ __('admin.edit') }}</a>
                                <form method="POST" action="{{ route('admin.discounts.destroy', $discount) }}" onsubmit="return confirm('{{ __('admin.confirm_delete') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">{{ __('admin.delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-sm text-ink-500">{{ __('admin.no_data') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $discounts->links('partials.pagination') }}
    </div>
@endsection