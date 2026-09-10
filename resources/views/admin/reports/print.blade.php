<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 18mm 12mm 16mm 12mm;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #111827;
            line-height: 1.35;
        }

        .brand-accent { color: #0f766e; }
        .muted { color: #6b7280; }

        .report-header { border-bottom: 3px solid #111827; margin-bottom: 14px; padding-bottom: 10px; }
        .report-header table { width: 100%; border-collapse: collapse; }
        .brand-name { font-size: 17px; font-weight: bold; letter-spacing: 0.5px; }
        .brand-meta { font-size: 9px; color: #6b7280; margin-top: 2px; }
        .report-title { font-size: 13px; font-weight: bold; text-transform: uppercase; text-align: right; }
        .report-sub { font-size: 9px; text-align: right; color: #6b7280; margin-top: 3px; }

        .kpi-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .kpi-table td {
            width: 20%;
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
            text-align: center;
        }
        .kpi-table .kpi-label { font-size: 8px; text-transform: uppercase; letter-spacing: 0.6px; color: #6b7280; }
        .kpi-table .kpi-value { font-size: 14px; font-weight: bold; margin-top: 3px; }
        .kpi-table .positive { color: #0f766e; }
        .kpi-table .negative { color: #dc2626; }

        .meta-line { font-size: 9px; color: #6b7280; margin-bottom: 10px; }

        .section-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #111827;
            border-bottom: 1.5px solid #111827;
            margin: 14px 0 6px;
            padding-bottom: 3px;
        }

        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th {
            background: #111827;
            color: #ffffff;
            padding: 6px 7px;
            font-size: 8.5px;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .data-table td {
            padding: 5px 7px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9px;
        }
        .data-table tbody tr:nth-child(even) td { background: #f9fafb; }
        .data-table .right { text-align: right; }
        .data-table .center { text-align: center; }
        .data-table tfoot td {
            background: #e7f5ef;
            font-weight: bold;
            border-top: 2px solid #111827;
        }

        .two-col { width: 100%; border-collapse: collapse; }
        .two-col td { vertical-align: top; padding-right: 14px; }

        .plain-list { width: 100%; border-collapse: collapse; }
        .plain-list td { padding: 3px 6px; border-bottom: 1px solid #f3f4f6; font-size: 9px; }
        .top-badge { font-size: 8px; font-weight: bold; color: #ffffff; background: #0f766e; border-radius: 3px; padding: 1px 4px; }

        .signature-area { margin-top: 26px; }
        .signature-area .sign { display: inline-block; width: 30%; text-align: center; }
        .signature-area .line { border-top: 1px solid #111827; margin-top: 52px; padding-top: 4px; font-size: 9px; }

        .page-number {
            position: fixed;
            bottom: 0;
            left: 12mm;
            font-size: 8px;
            color: #9ca3af;
        }
        .page-number::after { content: "Halaman " counter(page); }
        .page-info {
            position: fixed;
            bottom: 0;
            right: 12mm;
            font-size: 8px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    @php
        $siteName = \App\Models\Setting::where('key', 'business.name')->value('value') ?? config('app.name');
        $siteAddress = \App\Models\Setting::where('key', 'business.address')->value('value') ?? '';
        $sitePhone = \App\Models\Setting::where('key', 'business.phone')->value('value') ?? '';
    @endphp

    <div class="report-header">
        <table>
            <tr>
                <td style="width: 60%">
                    <div class="brand-name">{{ $siteName }}</div>
                    <div class="brand-meta">
                        {{ $siteAddress ?: 'Laporan Penjualan' }}@if ($siteAddress && $sitePhone) &nbsp;|&nbsp; {{ $sitePhone }} @endif
                    </div>
                </td>
                <td style="width: 40%">
                    <div class="report-title">Laporan Penjualan</div>
                    <div class="report-sub">
                        Periode {{ $periodLabel }}<br>
                        Dicetak {{ now()->format('d/m/Y H:i') }} oleh {{ auth()->user()?->name ?? '-' }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="kpi-table">
        <tr>
            <td>
                <div class="kpi-label">Penjualan Kotor</div>
                <div class="kpi-value">Rp {{ number_format($grossSales, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="kpi-label">Refund</div>
                <div class="kpi-value negative">- Rp {{ number_format($refundTotal, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="kpi-label">Penjualan Bersih</div>
                <div class="kpi-value positive">Rp {{ number_format($netSales, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="kpi-label">Order Selesai</div>
                <div class="kpi-value">{{ $orderCount }}</div>
            </td>
            <td>
                <div class="kpi-label">Dibatalkan</div>
                <div class="kpi-value">{{ $cancelledCount }}</div>
            </td>
        </tr>
    </table>

    <div class="meta-line">
        Rata-rata nilai order: <strong>Rp {{ number_format($avgOrderValue, 0, ',', '.') }}</strong>
        &nbsp;&bull;&nbsp; Metode pembayaran teratas: <strong>{{ $paymentMix->first()?->name ?? '-' }}</strong>
        @if ($appliedFilters !== [])
            &nbsp;&bull;&nbsp; Filter:
            @foreach ($appliedFilters as $label => $value)
                <strong>{{ $label }}:</strong> {{ $value }}@if (! $loop->last), @endif
            @endforeach
        @endif
    </div>

    <div class="section-title">Detail Order ({{ $orders->count() }} {{ Str::plural('Order', $orders->count()) }})</div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%">Order</th>
                <th style="width: 13%">Tanggal</th>
                <th style="width: 12%">Tipe</th>
                <th style="width: 14%">Lokasi</th>
                <th style="width: 10%">Status</th>
                <th style="width: 10%">Pembayaran</th>
                <th style="width: 7%" class="right">Items</th>
                <th style="width: 19%" class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->ordered_at?->format('d/m/Y H:i') }}</td>
                    <td>{{ ['dine_in' => 'Dine In', 'take_away' => 'Take Away', 'room_service' => 'Room Service'][$order->order_type] ?? $order->order_type }}</td>
                    <td>{{ $order->area?->name ? $order->area->name.' / ' : '' }}{{ $order->locationLabel() }}</td>
                    <td>{{ \App\Models\Order::$flowLabels[$order->order_status] ?? $order->order_status }}</td>
                    <td>{{ ['paid' => 'Lunas', 'pending' => 'Belum', 'failed' => 'Gagal', 'expired' => 'Kadaluarsa'][$order->payment_status] ?? $order->payment_status }}</td>
                    <td class="center">{{ $order->items->sum('quantity') }}</td>
                    <td class="right">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="center muted">Tidak ada data pada filter ini.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($orders->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="6">TOTAL ({{ $orders->count() }} {{ Str::plural('Order', $orders->count()) }})</td>
                    <td class="center">{{ $orders->sum(fn ($order) => $order->items->sum('quantity')) }}</td>
                    <td class="right">Rp {{ number_format($orders->sum('grand_total'), 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    @if ($orders->isNotEmpty())
        <table class="two-col">
            <tr>
                <td style="width: 50%">
                    <div class="section-title">Produk Teratas</div>
                    <table class="plain-list">
                        @foreach ($topProducts as $index => $product)
                            <tr>
                                <td style="width: 6%"><span class="top-badge">{{ $index + 1 }}</span></td>
                                <td>{{ $product->product_name }}</td>
                                <td class="center" style="width: 10%">{{ number_format($product->qty, 0, ',', '.') }}x</td>
                                <td class="right" style="width: 22%">Rp {{ number_format($product->revenue, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </table>
                </td>
                <td style="width: 50%">
                    <div class="section-title">Distribusi Status Order</div>
                    <table class="plain-list">
                        @foreach (\App\Models\Order::$flowLabels as $value => $label)
                            @if (isset($statusDistribution[$value]))
                                <tr>
                                    <td>{{ $label }}</td>
                                    <td class="right" style="width: 20%">{{ number_format($statusDistribution[$value], 0, ',', '.') }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </table>
                </td>
            </tr>
        </table>
    @endif

    <div class="signature-area">
        <span class="sign">Mengetahui,<br><br><br><br><div class="line">{{ $siteName }}</div></span>
        <span class="sign" style="margin-left: 5%">Dicetak oleh,<br><br><br><br><div class="line">{{ auth()->user()?->name ?? '-' }}</div></span>
    </div>

    <div class="page-number"></div>
    <div class="page-info">Dicetak dari {{ $siteName }} — {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>