@extends('layouts.app')

@section('title', 'Struk '.$order->order_number.' - Kasir - '.config('app.name'))
@section('header', 'Struk '.$order->order_number)

@section('content')
    @php
        $settings = app(\App\Services\SettingsService::class);
        $business = [
            'name' => $settings->get('business.name', config('app.name')),
            'address' => $settings->get('business.address', ''),
            'phone' => $settings->get('business.phone', ''),
        ];
        $footer = $settings->get('receipt.footer', 'Terima kasih atas kunjungan Anda');
        $totalPaid = $order->payments()->where('status', \App\Models\Payment::STATUS_PAID)->sum('amount');
        $change = max(0, $totalPaid - $order->grand_total);
        $typeLabels = ['dine_in' => 'Dine In', 'take_away' => 'Take Away', 'room_service' => 'Room Service'];
    @endphp

    <div class="mx-auto max-w-md">
        <div class="mb-5 flex justify-between">
            <a href="{{ url()->previous() ?: route('cashier.dashboard') }}" class="btn btn-secondary">&larr; Kembali</a>
            <a href="{{ route('cashier.receipt.print', $order) }}" target="_blank" rel="noopener" class="btn btn-primary">Cetak Struk</a>
        </div>

        <div class="card overflow-hidden p-6">
            <div class="text-center">
                <h1 class="text-lg font-bold text-gray-900">{{ $business['name'] }}</h1>
                @if ($business['address']) <p class="text-xs text-gray-500">{{ $business['address'] }}</p> @endif
                @if ($business['phone']) <p class="text-xs text-gray-500">Telp: {{ $business['phone'] }}</p> @endif
                <p class="mt-2 text-lg font-semibold tracking-wide text-gray-900">{{ $order->order_number }}</p>
                <p class="text-xs text-gray-500">{{ $order->ordered_at?->format('d/m/Y H:i') }} · {{ $typeLabels[$order->order_type] ?? $order->order_type }}</p>
                @if ($order->area)
                    <p class="text-xs text-gray-500">{{ $order->area->name }} · {{ $order->locationLabel() }}</p>
                @endif
                @if ($order->customer_name)
                    <p class="mt-1 text-xs text-gray-600">Atas nama: {{ $order->customer_name }}</p>
                @endif
            </div>

            <div class="mt-5 border-t border-dashed border-gray-300 pt-4">
                @foreach ($order->items as $item)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-800">{{ $item->quantity }}× {{ $item->product_name }}</span>
                        <span class="font-medium text-gray-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @if ($item->notes)
                        <p class="pl-4 text-xs text-gray-400">Catatan: {{ $item->notes }}</p>
                    @endif
                @endforeach
            </div>

            <div class="mt-4 space-y-1 border-t border-dashed border-gray-300 pt-4 text-sm">
                <p class="flex justify-between text-gray-600"><span>Subtotal</span><span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span></p>
                @if ($order->discount_amount > 0)
                    <p class="flex justify-between text-gray-600"><span>Diskon</span><span class="text-emerald-600">−Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span></p>
                @endif
                @if ($order->tax_amount > 0)
                    <p class="flex justify-between text-gray-600"><span>Pajak</span><span>Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</span></p>
                @endif
                @if ($order->service_charge_amount > 0)
                    <p class="flex justify-between text-gray-600"><span>Service Charge</span><span>Rp {{ number_format($order->service_charge_amount, 0, ',', '.') }}</span></p>
                @endif
                <p class="flex justify-between border-t border-gray-300 pt-2 text-base font-bold text-gray-900"><span>TOTAL</span><span>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span></p>
                <p class="flex justify-between text-gray-600"><span>Dibayar</span><span>Rp {{ number_format($totalPaid, 0, ',', '.') }}</span></p>
                @if ($change > 0)
                    <p class="flex justify-between font-medium text-gray-900"><span>Kembalian</span><span>Rp {{ number_format($change, 0, ',', '.') }}</span></p>
                @endif
                <p class="flex justify-between text-gray-600"><span>Status</span><span>{{ $order->payment_status === \App\Models\Order::PAYMENT_PAID ? 'LUNAS' : strtoupper($order->payment_status) }}</span></p>
            </div>

            <div class="mt-5 border-t border-dashed border-gray-300 pt-3 text-center text-sm text-gray-600">
                @foreach ($order->payments as $payment)
                    <p class="text-xs">{{ $payment->paymentMethod?->name }} · {{ $payment->reference ?? '' }} · {{ $payment->paid_at?->format('d/m/Y H:i') }}</p>
                @endforeach
                @if ($order->creator)
                    <p class="mt-1 text-xs">Kasir: {{ $order->creator->name }}</p>
                @endif
                <p class="mt-2 text-xs text-gray-400">{{ $footer }}</p>
            </div>
        </div>
    </div>
@endsection