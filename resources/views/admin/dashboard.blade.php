@extends('layouts.app')

@section('title', __('nav.dashboard').' - '.config('app.name'))
@section('header', __('nav.dashboard'))

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card card-pad">
            <p class="text-sm text-ink-500">{{ __('admin.monthly_sales') }}</p>
            <p class="mt-2 text-2xl font-bold text-ink-900">Rp {{ number_format($grossSales, 0, ',', '.') }}</p>
        </div>
        <div class="card card-pad">
            <p class="text-sm text-ink-500">{{ __('admin.completed_orders') }}</p>
            <p class="mt-2 text-2xl font-bold text-ink-900">{{ $orderCount }}</p>
        </div>
        <div class="card card-pad">
            <p class="text-sm text-ink-500">{{ __('admin.avg_order_value') }}</p>
            <p class="mt-2 text-2xl font-bold text-ink-900">Rp {{ number_format($avgOrderValue, 0, ',', '.') }}</p>
        </div>
        <div class="card card-pad">
            <p class="text-sm text-ink-500">{{ __('admin.low_stock_products') }}</p>
            <p class="mt-2 text-2xl font-bold text-ink-900">{{ $lowStock->count() }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="card overflow-hidden">
            <div class="card-header">
                <h2 class="font-medium text-ink-900">{{ __('admin.status_distribution') }}</h2>
            </div>
            <div class="card-body">
                @if ($statusDistribution->isEmpty())
                    <p class="text-sm text-ink-500">{{ __('admin.no_data_month') }}</p>
                @else
                    <ul class="space-y-2">
                        @foreach (['new' => __('admin.status_new'), 'accepted' => __('admin.status_accepted'), 'cooking' => __('admin.status_cooking'), 'ready' => __('admin.status_ready'), 'completed' => __('admin.status_completed'), 'cancelled' => __('admin.status_cancelled')] as $status => $label)
                            @if (isset($statusDistribution[$status]))
                                <li class="flex items-center justify-between rounded-lg bg-cream-100/50 px-3 py-2 text-sm">
                                    <span class="text-ink-700">{{ $label }}</span>
                                    <span class="font-semibold text-ink-900">{{ $statusDistribution[$status] }}</span>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="card-header">
                <h2 class="font-medium text-ink-900">{{ __('admin.top_products') }}</h2>
            </div>
            <div class="card-body">
                @if ($topProducts->isEmpty())
                    <p class="text-sm text-ink-500">{{ __('admin.no_data') }}</p>
                @else
                    <table class="table-admin">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 font-medium">{{ __('admin.product') }}</th>
                                <th class="px-4 py-3 text-right font-medium">{{ __('admin.qty') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-900/10">
                            @foreach ($topProducts as $row)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-ink-700">{{ $row->product_name }}</td>
                                    <td class="px-4 py-3 text-right text-sm font-medium text-ink-900">{{ $row->qty }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="card overflow-hidden">
            <div class="card-header">
                <h2 class="font-medium text-ink-900">{{ __('admin.payment_mix') }}</h2>
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

        <div class="card overflow-hidden">
            <div class="card-header">
                <h2 class="font-medium text-ink-900">{{ __('admin.recent_orders') }}</h2>
            </div>
            <div class="card-body">
                @if ($recentOrders->isEmpty())
                    <p class="text-sm text-ink-500">{{ __('admin.no_orders') }}</p>
                @else
                    <ul class="divide-y divide-ink-900/10">
                        @foreach ($recentOrders as $order)
                            <li class="flex items-center justify-between gap-3 py-2">
                                <div>
                                    <p class="text-sm font-medium text-ink-900">{{ $order->order_number }}</p>
                                    <p class="text-xs text-ink-500">{{ $order->ordered_at?->format('d M H:i') }} · {{ $order->locationLabel() }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-ink-900">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</p>
                                    <span class="badge badge-forest">{{ \App\Models\Order::$flowLabels[$order->order_status] ?? $order->order_status }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-6 card overflow-hidden">
        <div class="card-header">
            <h2 class="font-medium text-ink-900">{{ __('admin.low_stock') }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="table-admin">
                <thead>
                    <tr>
                        <th class="px-5 py-3 font-medium">{{ __('admin.product') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('admin.sku') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('admin.stock') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-900/10">
                    @forelse ($lowStock as $product)
                        <tr>
                            <td class="px-5 py-3 text-sm font-medium text-ink-900">{{ $product->name }}</td>
                            <td class="px-5 py-3 text-sm text-ink-600">{{ $product->sku }}</td>
                            <td class="px-5 py-3">
                                <span class="badge {{ $product->stock > 0 ? 'badge-gold' : 'badge-burgundy' }}">
                                    {{ $product->stock }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-10 text-center text-sm text-ink-500">{{ __('admin.all_stock_ok') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection