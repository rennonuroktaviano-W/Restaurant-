@extends('layouts.app')

@section('title', __('admin.refunds.title').' - '.config('app.name'))
@section('header', __('admin.refunds.title'))

@section('content')
    <h1 class="mb-5 text-2xl font-bold text-ink-900">{{ __('admin.refunds.title') }}</h1>

    <div class="card mb-6 overflow-hidden">
        <form method="GET" action="{{ route('admin.refunds.index') }}" class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-5">
            <div class="lg:col-span-3">
                <label class="label">{{ __('admin.refunds.search') }}</label>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('admin.refunds.search_placeholder') }}" class="input">
            </div>
            <div>
                <label class="label">{{ __('admin.reports.date_from') }}</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="input">
            </div>
            <div>
                <label class="label">{{ __('admin.reports.date_to') }}</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="input">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn btn-primary">{{ __('admin.filter') }}</button>
                <a href="{{ route('admin.refunds.index') }}" class="btn btn-secondary">{{ __('admin.reset') }}</a>
            </div>
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-admin">
                <thead>
                    <tr>
                        <th class="px-4 py-3 font-medium">{{ __('admin.refunds.time') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('admin.refunds.order') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('admin.refunds.payment_method') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('admin.refunds.amount') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('admin.refunds.reason') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('admin.refunds.by') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('admin.status') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-900/10">
                    @forelse ($refunds as $refund)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-ink-600">{{ $refund->created_at?->format('d M Y H:i:s') }}</td>
                            <td class="px-4 py-3 text-sm text-forest-600">
                                <a href="{{ route('cashier.orders.show', $refund->order) }}">{{ $refund->order?->order_number }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm text-ink-600">{{ $refund->payment?->paymentMethod?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-ink-900">Rp {{ number_format($refund->amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-sm text-ink-700">
                                @if ($refund->reason_code)
                                    <span class="badge badge-ink mr-1">{{ $refund->reason_code }}</span>
                                @endif
                                {{ $refund->reason }}
                            </td>
                            <td class="px-4 py-3 text-sm text-ink-600">{{ $refund->creator?->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="badge {{ $refund->status === 'succeeded' ? 'badge-forest' : 'badge-burgundy' }}">
                                    {{ $refund->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-ink-500">{{ __('admin.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4">
            {{ $refunds->links('partials.pagination') }}
        </div>
    </div>
@endsection