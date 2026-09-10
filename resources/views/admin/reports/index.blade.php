@extends('layouts.app')

@section('title', __('admin.reports.title').' - '.config('app.name'))
@section('header', __('admin.reports.title'))

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-ink-900">{{ __('admin.reports.title') }}</h1>
            <p class="mt-1 text-sm text-ink-500">
                {{ __('admin.reports.date_from') }} {{ ($filters['date_from'] ?? __('admin.na')) }}
                &ndash; {{ ($filters['date_to'] ?? __('admin.na')) }}
                &middot; {{ $orderCount }} {{ __('admin.completed_orders') }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.reports.excel', request()->query()) }}" class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-1.5 h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M5 2a2 2 0 00-2 2v16a2 2 0 002 2h14a2 2 0 002-2V4a2 2 0 00-2-2H5zm1.2 5l2.3 3.4L6.2 13.7h1.9l1.2-1.9 1.2 1.9h2L10 10.4 12.4 7h-1.9L9.3 8.9 8.1 7H6.2zm7 6.7V12h2.4v1.7H15.4v-1.2h-1.3l.8 1.2h1.5l-1.9 2.2h1.9v1.3h-2.6v-1.2h-.8v1.2h-1.6v1.4H11v-9.4h1.6v1.5h.6V11h1.8v1.9z"/></svg>
                {{ __('admin.reports.excel') }}
            </a>
            <a href="{{ route('admin.reports.pdf', request()->query()) }}" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 17l-2 4h18l2-4m-13-6h4m-4 3h6M6 7V3h12v4M6 7h2m8 0h2"/></svg>
                {{ __('admin.reports.print_pdf') }}
            </a>
        </div>
    </div>

    <div class="card mb-6 card-pad">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="label">{{ __('admin.search') }}</label>
                <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="{{ __('admin.search_placeholder') }}" class="input">
            </div>
            <div>
                <label class="label">{{ __('admin.reports.date_from') }}</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="input">
            </div>
            <div>
                <label class="label">{{ __('admin.reports.date_to') }}</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="input">
            </div>
            <div>
                <label class="label">{{ __('admin.areas.title') }}</label>
                <select name="area_id" class="select">
                    <option value="">{{ __('admin.all_areas') }}</option>
                    @foreach ($areas as $area)
                        <option value="{{ $area->id }}" {{ $filters['area_id'] == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">{{ __('admin.reports.order_type') }}</label>
                <select name="order_type" class="select">
                    <option value="">{{ __('admin.all') }}</option>
                    <option value="dine_in" {{ $filters['order_type'] === 'dine_in' ? 'selected' : '' }}>{{ __('cart.dine_in') }}</option>
                    <option value="take_away" {{ $filters['order_type'] === 'take_away' ? 'selected' : '' }}>{{ __('cart.take_away') }}</option>
                    <option value="room_service" {{ $filters['order_type'] === 'room_service' ? 'selected' : '' }}>{{ __('cart.room_service') }}</option>
                </select>
            </div>
            <div>
                <label class="label">{{ __('admin.reports.status') }}</label>
                <select name="status" class="select">
                    <option value="">{{ __('admin.all') }}</option>
                    @foreach (\App\Models\Order::$flowLabels as $value => $label)
                        <option value="{{ $value }}" {{ $filters['status'] === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">{{ __('admin.reports.cashier') }}</label>
                <select name="cashier_id" class="select">
                    <option value="">{{ __('admin.all') }}</option>
                    @foreach ($cashiers as $cashier)
                        <option value="{{ $cashier->id }}" {{ $filters['cashier_id'] == $cashier->id ? 'selected' : '' }}>{{ $cashier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">{{ __('admin.reports.payment_method') }}</label>
                <select name="payment_method_id" class="select">
                    <option value="">{{ __('admin.all') }}</option>
                    @foreach ($paymentMethods as $method)
                        <option value="{{ $method->id }}" {{ $filters['payment_method_id'] == $method->id ? 'selected' : '' }}>{{ $method->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn btn-primary">{{ __('admin.filter') }}</button>
                <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">{{ __('admin.reset') }}</a>
            </div>
        </form>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
        <div class="card card-pad">
            <p class="text-xs text-ink-500">{{ __('admin.monthly_sales') }}</p>
            <p class="mt-1 text-lg font-bold text-ink-900">Rp {{ number_format($grossSales, 0, ',', '.') }}</p>
        </div>
        <div class="card card-pad">
            <p class="text-xs text-ink-500">{{ __('admin.refund') }}</p>
            <p class="mt-1 text-lg font-bold text-burgundy-700">- Rp {{ number_format($refundTotal, 0, ',', '.') }}</p>
        </div>
        <div class="card card-pad">
            <p class="text-xs text-ink-500">{{ __('admin.net_sales') }}</p>
            <p class="mt-1 text-lg font-bold text-forest-700">Rp {{ number_format($netSales, 0, ',', '.') }}</p>
        </div>
        <div class="card card-pad">
            <p class="text-xs text-ink-500">{{ __('admin.completed_orders') }}</p>
            <p class="mt-1 text-lg font-bold text-ink-900">{{ $orderCount }}</p>
        </div>
        <div class="card card-pad">
            <p class="text-xs text-ink-500">{{ __('admin.cancelled_orders') }}</p>
            <p class="mt-1 text-lg font-bold text-ink-900">{{ $cancelledCount }}</p>
        </div>
        <div class="card card-pad">
            <p class="text-xs text-ink-500">{{ __('admin.avg_order_value') }}</p>
            <p class="mt-1 text-lg font-bold text-ink-900">Rp {{ number_format($avgOrderValue, 0, ',', '.') }}</p>
        </div>
        <div class="card card-pad">
            <p class="text-xs text-ink-500">{{ __('admin.top_payment_method') }}</p>
            <p class="mt-1 truncate text-lg font-bold text-ink-900">{{ $paymentMix->first()?->name ?? '-' }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="card overflow-hidden">
            <div class="card-header">
                <h2 class="font-medium text-ink-900">{{ __('admin.status_distribution') }}</h2>
            </div>
            <div class="card-body">
                <ul class="space-y-2">
                    @foreach (\App\Models\Order::$flowLabels as $value => $label)
                        @if (isset($statusDistribution[$value]))
                            <li class="flex justify-between rounded-lg bg-cream-100/50 px-3 py-2 text-sm">
                                <span class="text-ink-700">{{ $label }}</span>
                                <span class="font-semibold text-ink-900">{{ $statusDistribution[$value] }}</span>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="card overflow-hidden">
            <div class="card-header">
                <h2 class="font-medium text-ink-900">{{ __('admin.top_products') }}</h2>
            </div>
            <div class="card-body">
                <table class="table-admin">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 font-medium">{{ __('admin.product') }}</th>
                            <th class="px-4 py-3 text-right font-medium">{{ __('admin.qty') }}</th>
                            <th class="px-4 py-3 text-right font-medium">{{ __('admin.revenue') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-900/10">
                        @foreach ($topProducts as $row)
                            <tr>
                                <td class="px-4 py-3 text-sm text-ink-700">{{ $row->product_name }}</td>
                                <td class="px-4 py-3 text-right text-sm text-ink-700">{{ $row->qty }}</td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-ink-900">Rp {{ number_format($row->revenue, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="card overflow-hidden">
            <div class="card-header">
                <h2 class="font-medium text-ink-900">{{ __('admin.reports.payment_mix') }}</h2>
            </div>
            <div class="card-body">
                @if ($paymentMix->isEmpty())
                    <p class="text-sm text-ink-500">{{ __('admin.no_transactions') }}</p>
                @else
                    <ul class="space-y-2">
                        @foreach ($paymentMix as $row)
                            <li class="flex items-center justify-between rounded-lg bg-cream-100/50 px-3 py-2 text-sm">
                                <span class="text-ink-700">{{ $row->name }}</span>
                                <span class="font-semibold text-ink-900">Rp {{ number_format($row->total, 0, ',', '.') }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-6 card overflow-hidden">
        <div class="card-header">
            <h2 class="font-medium text-ink-900">{{ __('admin.reports.orders_list') }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="table-admin">
                <thead>
                    <tr>
                        <th class="px-4 py-3 font-medium">{{ __('admin.reports.order') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('admin.reports.date') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('admin.reports.type_location') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('admin.status') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('admin.reports.payment') }}</th>
                        <th class="px-4 py-3 text-right font-medium">{{ __('admin.total') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-900/10">
                    @forelse ($orders as $order)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-ink-900">{{ $order->order_number }}</td>
                            <td class="px-4 py-3 text-sm text-ink-600">{{ $order->ordered_at?->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 text-sm text-ink-600">
                                {{ ['dine_in' => __('cart.dine_in'), 'take_away' => __('cart.take_away'), 'room_service' => __('cart.room_service')][$order->order_type] }}
                                @if ($order->area)
                                    &middot; {{ $order->area->name }} / {{ $order->locationLabel() }}
                                @endif
                            </td>
                            <td class="px-4 py-3"><span class="badge badge-forest">{{ \App\Models\Order::$flowLabels[$order->order_status] ?? $order->order_status }}</span></td>
                            <td class="px-4 py-3 text-sm text-ink-600">
                                <span class="badge {{ $order->payment_status === 'paid' ? 'badge-forest' : 'badge-gold' }}">{{ ['paid' => __('admin.payment_paid'), 'pending' => __('admin.payment_pending'), 'failed' => __('admin.payment_failed'), 'expired' => __('admin.payment_expired')][$order->payment_status] ?? $order->payment_status }}</span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-ink-900">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-ink-500">{{ __('admin.no_orders') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-4">{{ $orders->appends(request()->query())->links('partials.pagination') }}</div>
    </div>
@endsection