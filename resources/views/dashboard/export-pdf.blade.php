<!-- HTML -->
<!DOCTYPE html>
<!-- Set bahasa -->
<html lang="{{ $lang }}">

<head>
    <meta charset="UTF-8">
    <title>Laporan_Stok_{{ $business->business_name }}</title>
    <!-- CSS khusus tampilan dokumen cetak laporan -->
    <style>
        body { font-family: Arial, sans-serif; color: #333; margin: 20px; font-size: 12px; }

        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #76553D; padding-bottom: 10px; }

        .header h2 { margin: 0; color: #76553D; }

        .info { margin-bottom: 15px; display: flex; justify-content: space-between; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }

        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }

        th { background: #F5EFE8; color: #76553D; }

        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>

<body>

    <!-- Tombol cetak / simpan PDF (tidak ikut tercetak) -->
    <div class="no-print" style="margin-bottom:15px; text-align:right;">
        <button onclick="window.print()" style="padding:8px 16px; background:#76553D; color:white; border:0; border-radius:6px; cursor:pointer;">
            {{ t('print_save_pdf') }}
        </button>
    </div>

    <!-- Header laporan: nama bisnis & judul -->
    <div class="header">
        <h2>{{ $business->business_name }}</h2>
        <p style="margin:5px 0 0;">{{ t('stock_report_header') }}</p>
    </div>

    <!-- Informasi periode laporan -->
    <div class="info">
        <span><strong>{{ t('period') }}:</strong> {{ $dateFrom }} {{ t('to') }} {{ $dateTo }}</span>
    </div>

    <!-- Tabel ringkasan statistik stok -->
    <table style="width:100%; border-collapse:collapse; margin-bottom:20px;">
        <tr>
            <td style="padding:10px; border:1px solid #ccc; background:#f9f9f9;">
                <small>{{ t('total_products') }}</small>
                <strong>{{ number_format($totalProducts) }}</strong>
            </td>

            <td style="padding:10px; border:1px solid #ccc; background:#f9f9f9;">
                <small>{{ t('total_stock') }}</small>
                <strong>{{ number_format($totalStock) }}</strong>
            </td>

            <td style="padding:10px; border:1px solid #ccc; background:#f9f9f9;">
                <small>{{ t('stock_in') }}</small>
                <strong>{{ number_format($totalStockIn) }}</strong>
            </td>

            <td style="padding:10px; border:1px solid #ccc; background:#f9f9f9;">
                <small>{{ t('stock_out') }}</small>
                <strong>{{ number_format($totalStockOut) }}</strong>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="padding:10px; border:1px solid #ccc; background:#f9f9f9;">
                <small>{{ t('total_capital') }}</small>
                <strong>{{ format_money($totalModalValue, $business->currency) }}</strong>
            </td>

            <td colspan="2" style="padding:10px; border:1px solid #ccc; background:#f9f9f9;">
                <small>{{ t('profit') }}</small>
                <strong>{{ format_money($totalProfit, $business->currency) }}</strong>
            </td>
        </tr>
    </table>

    <!-- Tabel rincian laporan per produk -->
    <table>
        <thead>
            <tr>
                <th>{{ t('product_name') }}</th>
                <th>{{ t('stock_in') }}</th>
                <th>{{ t('stock_out') }}</th>
                <th>{{ t('profit') }}</th>
                <th>{{ t('current_stock') }}</th>
            </tr>
        </thead>
        <tbody>
            @if ($reports->isEmpty())
                <!-- Tampilan baris khusus saat tidak ada data -->
                <tr>
                    <td colspan="5" style="text-align:center;">{{ t('no_data') }}</td>
                </tr>
            @else
                <!-- Loop untuk merender setiap baris laporan -->
                @foreach ($reports as $row)
                    <tr>
                        <td>{{ $row->product_name }}</td>
                        <td>{{ number_format((int) $row->stock_in) }}</td>
                        <td>{{ number_format((int) $row->stock_out) }}</td>
                        <td>{{ format_money($row->profit, $business->currency) }}</td>
                        <td>{{ number_format((int) $row->current_stock) }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

</body>
</html>
