<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk {{ $order->order_number }}</title>
    <script>
        window.addEventListener('DOMContentLoaded', () => setTimeout(() => window.print(), 250));
    </script>
    <style>
        @page { size: 80mm auto; margin: 6mm 4mm; }
        * { box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            color: #000;
            margin: 0;
        }
        .center { text-align: center; }
        .break { border-top: 1px dashed #000; margin: 6px 0; }
        .row { display: flex; justify-content: space-between; gap: 8px; }
        .row > *:first-child { white-space: pre-wrap; flex: 1; }
        .row > *:last-child { text-align: right; white-space: nowrap; }
        .line-item { display: flex; justify-content: space-between; gap: 8px; }
        .line-item .name { flex: 1; white-space: pre-wrap; }
        .line-item .amount { text-align: right; white-space: nowrap; }
        h1 { font-size: 15px; margin: 0 0 2px; }
        .note { font-size: 10px; }
        .sub { font-size: 10px; }
    </style>
</head>
<body>
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

    <div class="center">
        <h1>{{ $business['name'] }}</h1>
        @if ($business['address']) <p class="sub" style="margin:0">{{ $business['address'] }}</p> @endif
        @if ($business['phone']) <p class="sub" style="margin:0">Telp: {{ $business['phone'] }}</p> @endif
    </div>

    <p>{{ $order->order_number }}</p>
    <p>{{ $order->ordered_at?->format('d/m/Y H:i') }}</p>
    <p>{{ $typeLabels[$order->order_type] ?? $order->order_type }}@if ($order->area) · {{ $order->area->name }} · {{ $order->locationLabel() }}@endif</p>
    @if ($order->customer_name) <p>Atas nama: {{ $order->customer_name }}</p> @endif

    <div class="break"></div>

    @foreach ($order->items as $item)
        <div class="line-item">
            <span class="name">{{ $item->quantity }}x {{ $item->product_name }}</span>
            <span class="amount">{{ number_format($item->subtotal, 0, ',', '.') }}</span>
        </div>
        @if ($item->notes) <p class="note" style="margin:0 0 2px 8px">Catatan: {{ $item->notes }}</p> @endif
    @endforeach

    <div class="break"></div>

    <div class="row"><span>Subtotal</span><span>{{ number_format($order->subtotal, 0, ',', '.') }}</span></div>
    @if ($order->discount_amount > 0)
        <div class="row"><span>Diskon</span><span>-{{ number_format($order->discount_amount, 0, ',', '.') }}</span></div>
    @endif
    @if ($order->tax_amount > 0)
        <div class="row"><span>Pajak</span><span>{{ number_format($order->tax_amount, 0, ',', '.') }}</span></div>
    @endif
    @if ($order->service_charge_amount > 0)
        <div class="row"><span>SC</span><span>{{ number_format($order->service_charge_amount, 0, ',', '.') }}</span></div>
    @endif
    <div class="row" style="font-weight:bold"><span>TOTAL</span><span>{{ number_format($order->grand_total, 0, ',', '.') }}</span></div>
    <div class="row"><span>Dibayar</span><span>{{ number_format($totalPaid, 0, ',', '.') }}</span></div>
    @if ($change > 0)
        <div class="row"><span>Kembalian</span><span>{{ number_format($change, 0, ',', '.') }}</span></div>
    @endif
    <p>Status: {{ $order->payment_status === \App\Models\Order::PAYMENT_PAID ? 'LUNAS' : strtoupper($order->payment_status) }}</p>

    @foreach ($order->payments as $payment)
        <p class="sub" style="margin:2px 0">{{ $payment->paymentMethod?->name }} {{ $payment->reference ?? '' }} {{ $payment->paid_at?->format('d/m/Y H:i') }}</p>
    @endforeach
    @if ($order->creator) <p class="sub" style="margin:2px 0">Kasir: {{ $order->creator->name }}</p> @endif

    <div class="break"></div>
    <div class="center note">{{ $footer }}</div>
</body>
</html>