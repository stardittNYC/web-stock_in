<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ t('analytics_chart') }} - StockIn</title>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        @media print {
            body * {
                visibility: hidden !important;
            }

            #reportContent, #reportContent * {
                visibility: visible !important;
            }

            #reportContent {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            @page {
                size: A4 landscape;
                margin: 12mm;
            }

            .print-header {
                display: block !important;
            }

            .chart-container-print {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                border: 1px solid #E9E1D7 !important;
                box-shadow: none !important;
                margin-bottom: 0 !important;
            }

            .print-page-break {
                page-break-before: always !important;
                break-before: page !important;
            }

            canvas {
                max-width: 100% !important;
                max-height: 320px !important;
            }
        }
    </style>

</head>

<body style="margin:0; min-height:100vh; background:#FAF7F2; font-family:Arial,sans-serif; color:#49382D;">

@php $currentPage = 'dashboard.reports.analytics'; @endphp

<div style="display:flex; min-height:100vh;">

    @include('dashboard.partials.sidebar')

    <main style="margin-left:230px; flex:1; min-width:0;">

        @include('dashboard.partials.header')

        <section style="padding:32px;">
            <div style="margin-bottom:25px;">
                <h1 style="margin:0; font-size:29px;">{{ t('analytics_title') }}</h1>
                <p style="color:#81776D; margin-top:10px; line-height:1.6;">{{ t('analytics_desc') }}</p>
            </div>

            <div style="background:white; border:1px solid #E9E1D7; border-radius:12px; padding:22px; margin-bottom:25px;">
                <form method="GET" style="display:flex; gap:15px; align-items:end; flex-wrap:wrap;">
                    <input type="hidden" name="business_id" value="{{ (int) $business_id }}">

                    <div>
                        <label style="display:block; margin-bottom:7px; font-weight:600;">{{ t('date_from') }}</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" style="padding:10px; border:1px solid #E9E1D7; border-radius:8px;">
                    </div>

                    <div>
                        <label style="display:block; margin-bottom:7px; font-weight:600;">{{ t('date_to') }}</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}" style="padding:10px; border:1px solid #E9E1D7; border-radius:8px;">
                    </div>

                    <button type="submit" style="height:40px; padding:0 20px; border:0; border-radius:9px; background:#76553D; color:white; font-weight:600; cursor:pointer;">
                        {{ t('apply') }}
                    </button>

                    <button type="button" onclick="exportReportToPDF()" style="display:flex; align-items:center; gap:8px; height:40px; padding:0 16px; background:#76553D; color:white; border:none; border-radius:9px; cursor:pointer; font-weight:600;">
                        <i data-lucide="file-down" style="width:18px; height:18px;"></i>
                        {{ t('export_pdf') }}
                    </button>
                </form>
            </div>

            <div id="reportContent">

                <div class="print-header" style="text-align:center; margin-bottom:15px; display:none;">
                    <h2 style="margin:0; font-size:22px; color:#302921; text-transform:uppercase;">
                        {{ $business->business_name ?? 'NAMA TOKO' }}
                    </h2>
                    <p style="margin:4px 0 0; font-size:13px; color:#81776D; font-weight:600;">
                        {{ t('analytics_report_title') }}
                    </p>
                    <p style="margin:4px 0 0; font-size:12px; color:#81776D;">
                        {{ t('period') }}: {{ $dateFrom }} {{ t('to') }} {{ $dateTo }}
                    </p>
                    <hr style="border:0; border-top:2px solid #76553D; margin:12px 0 18px;">
                </div>

                <div class="chart-container-print" style="background: white; border: 1.5px solid #E9E1D7; border-radius: 12px; padding: 20px;">
                    <h3 style="font-size: 18px; color: #76553D; margin-top: 0; margin-bottom: 15px;">
                        {{ t('stock_movement_chart') }}
                    </h3>
                    <div style="position: relative; width: 100%; height: 350px;">
                        <canvas id="stockMovementChart"></canvas>
                    </div>
                </div>

                <div class="chart-container-print print-page-break" style="background: white; border: 1.5px solid #E9E1D7; border-radius: 12px; padding: 20px; margin-top: 20px;">
                    <div class="print-header" style="text-align:center; margin-bottom:15px; display:none;">
                        <h2 style="margin:0; font-size:22px; color:#302921; text-transform:uppercase;">
                            {{ $business->business_name ?? 'NAMA TOKO' }}
                        </h2>
                        <p style="margin:4px 0 0; font-size:13px; color:#81776D; font-weight:600;">
                            {{ t('analytics_report_title') }}
                        </p>
                        <p style="margin:4px 0 0; font-size:12px; color:#81776D;">
                            {{ t('period') }}: {{ $dateFrom }} {{ t('to') }} {{ $dateTo }}
                        </p>
                        <hr style="border:0; border-top:2px solid #76553D; margin:12px 0 18px;">
                    </div>

                    <h3 style="font-size: 18px; color: #2E5A27; margin-top: 0; margin-bottom: 15px;">
                        {{ t('profit_trend_chart') }}
                    </h3>
                    <div style="position: relative; width: 100%; height: 350px;">
                        <canvas id="profitTrendChart"></canvas>
                    </div>
                </div>

            </div>

        </section>
    </main>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script src="{{ asset('assets/js/csrf.js') }}"></script>
<script src="{{ asset('assets/js/toast.js') }}" defer></script>

<script>
lucide.createIcons();

const productLabels = {{ Illuminate\Support\Js::from($productNames) }};
const stockInValues  = {{ Illuminate\Support\Js::from($stockIns) }};
const stockOutValues = {{ Illuminate\Support\Js::from($stockOuts) }};
const profitValues   = {{ Illuminate\Support\Js::from($profits) }};

new Chart(document.getElementById('stockMovementChart'), {
    type: 'bar',
    data: {
        labels: productLabels,
        datasets: [
            { label: {{ Illuminate\Support\Js::from(t('stock_in')) }}, data: stockInValues, backgroundColor: '#76553D' },
            { label: {{ Illuminate\Support\Js::from(t('stock_out')) }}, data: stockOutValues, backgroundColor: '#B99A7D' }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true } }
    }
});

new Chart(document.getElementById('profitTrendChart'), {
    type: 'bar',
    data: {
        labels: productLabels,
        datasets: [
            { label: {{ Illuminate\Support\Js::from(t('profit')) }}, data: profitValues, backgroundColor: '#2E7D32' }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true } }
    }
});

const hasChartData = {{ count($productNames) > 0 ? 'true' : 'false' }};

function exportReportToPDF() {
    if (!hasChartData) {
        showToast({{ Illuminate\Support\Js::from(t('no_data_to_export')) }}, 'error');
        return;
    }

    window.addEventListener('afterprint', function handleAfterPrint() {
        showToast({{ Illuminate\Support\Js::from(t('pdf_export_success') ?? 'Dokumen PDF berhasil diproses.') }}, 'success');
        window.removeEventListener('afterprint', handleAfterPrint);
    });

    window.print();
}

</script>

</body>
</html>
