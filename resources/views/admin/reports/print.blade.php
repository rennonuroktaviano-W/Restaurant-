<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        .meta { color: #6b7280; font-size: 11px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; font-size: 11px; }
        th { background: #f3f4f6; }
        .right { text-align: right; }
        .summary { display: flex; gap: 24px; margin: 12px 0; }
        .summary div { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 8px 12px; }
        .summary .label { color: #6b7280; font-size: 10px; }
        .summary .value { font-weight: bold; font-size: 14px; }
    </style>
</head>
<body>
    <h1>Laporan Penjualan</h1>
    <div class="meta">
        {{ $filters['date_from'] ?? 'Awal' }} s/d {{ $filters['date_to'] ?? 'Sekarang' }} · Dicetak {{ now()->format('d/m/Y H:i') }}
    </div>

    <div class="summary">
        <div><div class="label">Penjualan Kotor</div><div class="value">Rp {{ number_format($grossSales, 0, ',', '.') }}</div></div>
        <div><div class="label">Jumlah Order</div><div class="value">{{ $orders->count() }}</div></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Order</th>
                <th>Tanggal</th>
                <th>Tipe / Lokasi</th>
                <th>Status</th>
                <th>Pembayaran</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->ordered_at?->format('d/m/Y H:i') }}</td>
                    <td>{{ ['dine_in' => 'Dine In', 'take_away' => 'Take Away', 'room_service' => 'Room Service'][$order->order_type] }} · {{ $order->locationLabel() }}</td>
                    <td>{{ \App\Models\Order::$flowLabels[$order->order_status] ?? $order->order_status }}</td>
                    <td>{{ ['paid' => 'Lunas', 'pending' => 'Belum', 'failed' => 'Gagal', 'expired' => 'Kadaluarsa'][$order->payment_status] ?? $order->payment_status }}</td>
                    <td class="right">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="right" style="text-align:center">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>