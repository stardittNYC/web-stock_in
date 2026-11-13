@extends('layouts.dashboard')

@section('title')
    {{ t('reports') }} - StockIn
@endsection

@php $currentPage = 'dashboard.reports'; @endphp

@section('content')
        <section style="padding:32px;">
            <!-- JUDUL DAN DESKRIPSI HALAMAN -->
            <div style="margin-bottom:25px;">
                <h1 style="margin:0; font-size:29px;">
                    {{ t('reports') }}
                </h1>

                <p style="color:#81776D; margin-top:10px; line-height:1.6;">
                    {{ t('reports_description') }}
                </p>
            </div>

            <!-- FORM FILTER DAN PENGURUTAN LAPORAN -->
            <div style="background:white;border:1px solid #E9E1D7; border-radius:12px; padding:22px; margin-bottom:25px;">
                <form method="GET">
                    <!-- Kirim ID bisnis aktif sebagai parameter tersembunyi -->
                    <input type="hidden" name="business_id" value="{{ (int) $business_id }}">
                    <div style="display:flex; gap:15px; align-items:end; flex-wrap:wrap;">

                        <!-- Filter tanggal awal periode -->
                        <div>
                            <label style="display:block; margin-bottom:7px; font-weight:600;">
                                {{ t('date_from') }}
                            </label>
                            <input type="date" name="date_from" value="{{ $dateFrom }}" style="padding:10px; border:1px solid #E9E1D7; border-radius:8px;">
                        </div>

                        <!-- Filter tanggal akhir periode -->
                        <div>
                            <label style="display:block; margin-bottom:7px; font-weight:600;">
                                {{ t('date_to') }}
                            </label>
                            <input type="date" name="date_to" value="{{ $dateTo }}" style="padding:10px; border:1px solid #E9E1D7; border-radius:8px;">
                        </div>

                        <!-- Dropdown pilihan kolom pengurutan data -->
                        <div>
                            <label style="display:block; margin-bottom:7px; font-weight:600;">
                                {{ t('sort_by') }}
                            </label>

                            <select name="sort"
                                style="padding:10px; border:1px solid #E9E1D7; border-radius:8px;">

                                <option value="profit" {{ $sort === 'profit' ? 'selected' : '' }}>
                                    {{ t('profit') }}
                                </option>

                                <option value="stock_in" {{ $sort === 'stock_in' ? 'selected' : '' }}>
                                    {{ t('stock_in') }}
                                </option>

                                <option value="stock_out" {{ $sort === 'stock_out' ? 'selected' : '' }}>
                                    {{ t('stock_out') }}
                                </option>

                                <option value="current_stock" {{ $sort === 'current_stock' ? 'selected' : '' }}>
                                    {{ t('current_stock') }}
                                </option>

                                <option value="product_name" {{ $sort === 'product_name' ? 'selected' : '' }}>
                                    {{ t('product_name') }}
                                </option>
                            </select>
                        </div>

                        <!-- Dropdown arah pengurutan (naik / turun) -->
                        <div>
                            <label style="display:block; margin-bottom:7px; font-weight:600;">
                                {{ t('order') }}
                            </label>

                            <select name="order" style="padding:10px; border:1px solid #E9E1D7; border-radius:8px;">
                                <option value="DESC" {{ $order === 'DESC' ? 'selected' : '' }}>
                                    {{ t('descending') }}
                                </option>

                                <option value="ASC" {{ $order === 'ASC' ? 'selected' : '' }}>
                                    {{ t('ascending') }}
                                </option>
                            </select>
                        </div>

                        <!-- Tombol Terapkan Filter -->
                        <button type="submit"
                            style="height:40px; padding:0 20px; border:0; border-radius:9px; background:#76553D; color:white; font-weight:600; cursor:pointer;">
                            {{ t('apply') }}
                        </button>

                    </div>
                </form>
            </div>

            <!-- GRID KARTU RINGKASAN STATISTIK -->
            <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:18px; margin-bottom:25px;">

                <!-- Total Produk -->
                <div style="background:white; border:1px solid #E9E1D7; border-radius:12px; padding:20px;">
                    <p style="margin:0; color:#81776D; font-size:13px;">
                        {{ t('total_products') }}
                    </p>

                    <h2 style="margin:10px 0 0;">
                        {{ number_format($totalProducts) }}
                    </h2>
                </div>

                <!-- Total Stok -->
                <div style="background:white; border:1px solid #E9E1D7; border-radius:12px; padding:20px;">
                    <p style="margin:0; color:#81776D; font-size:13px;">
                        {{ t('total_stock') }}
                    </p>

                    <h2 style="margin:10px 0 0;">
                        {{ number_format($totalStock) }}
                    </h2>
                </div>

                <!-- Total Stok Masuk -->
                <div style="background:white; border:1px solid #E9E1D7; border-radius:12px; padding:20px;">
                    <p style="margin:0; color:#81776D; font-size:13px;">
                        {{ t('stock_in') }}
                    </p>
                    <h2 style="margin:10px 0 0;">
                        {{ number_format($totalStockIn) }}
                    </h2>
                </div>

                <!-- Total Stok Keluar -->
                <div style="background:white; border:1px solid #E9E1D7; border-radius:12px; padding:20px;">
                    <p style="margin:0; color:#81776D; font-size:13px;">
                        {{ t('stock_out') }}
                    </p>
                    <h2 style="margin:10px 0 0;">
                        {{ number_format($totalStockOut) }}
                    </h2>
                </div>

                <!-- Total Modal -->
                <div style="background:white; border:1px solid #E9E1D7; border-radius:12px; padding:20px;">
                    <p style="margin:0; color:#81776D; font-size:13px;">
                        {{ t('total_capital') }}
                    </p>
                    <h2 style="margin:10px 0 0; color:#76553D;">
                        {{ format_money($totalModalValue, $business->currency) }}
                    </h2>
                </div>

                <!-- Total Profit (memanjang 2 kolom) -->
                <div style="background:white; border:1px solid #E9E1D7; border-radius:12px; padding:20px; grid-column:span 2;">
                    <p style="margin:0; color:#81776D; font-size:13px;">
                        {{ t('profit') }}
                    </p>
                    <h2 style="margin:10px 0 0;">
                        {{ format_money($totalProfit, $business->currency) }}
                    </h2>
                </div>
            </div>

            <!-- KARTU TABEL LAPORAN PRODUK -->
            <div style="background:white; border:1px solid #E9E1D7; border-radius:12px; overflow:hidden;">

                <!-- Header tabel: judul & tombol ekspor -->
                <div style="padding:18px 20px; border-bottom:1px solid #E9E1D7; font-weight:bold; display:flex; justify-content:space-between; align-items:center;">
                    <span>{{ t('product_report') }}</span>

                    <div style="display:flex; gap:10px; align-items:center;">
                        <!-- Tombol ekspor laporan ke CSV -->
                        <button type="button" onclick="exportTableToCSV('laporan_stok.csv')" style="padding:8px 16px; border:1px solid #E9E1D7; border-radius:8px; background:#76553D; color:white; font-weight:600; cursor:pointer; font-size:13px; display:inline-flex; align-items:center; gap:6px;">
                            <i data-lucide="download" style="width:16px; height:16px;"></i>
                            {{ t('export_csv') }}
                        </button>

                        <!-- Tombol ekspor laporan ke PDF -->
                        <button type="button" onclick="printPDF('{{ route('dashboard.reports.export-pdf', ['business_id' => $business_id, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}')" style="padding:8px 16px; border:0; border-radius:8px; background:#76553D; color:white; font-weight:600; cursor:pointer; font-size:13px; display:inline-flex; align-items:center; gap:6px;">
                            <i data-lucide="file-text" style="width:16px; height:16px;"></i>
                            {{ t('export_pdf') }}
                        </button>
                    </div>
                </div>

                <!-- Area tabel dengan scroll -->
                <div style="max-height: 500px; overflow-y: auto; overflow-x: auto; border-top: 1px solid #E9E1D7;">
                    <table id="reportTable" style="width:100%; border-collapse:collapse; text-align:left; white-space:nowrap;">
                        <thead style="background:#F5EFE8; position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <!-- Header kolom dibuat otomatis dari daftar kunci terjemahan -->
                                @foreach (['product_name', 'stock_in', 'stock_out', 'profit', 'current_stock'] as $key)
                                    <th style=" padding:14px 16px; font-size:13px; color:#76553D; border-bottom:1px solid #E9E1D7;">
                                        {{ t($key) }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody>
                        @if ($reports->isEmpty())
                            <!-- Tampilan baris khusus saat tidak ada data -->
                            <tr>
                                <td colspan="5" style="padding:35px; text-align:center; color:#81776D;">
                                    {{ t('no_data') }}
                                </td>
                            </tr>
                        @else
                            <!-- Loop untuk merender setiap baris laporan -->
                            @foreach ($reports as $row)
                                <tr>
                                    <!-- Nama produk -->
                                    <td style="padding:14px 16px; border-bottom:1px solid #F0EBE5; font-weight:600;">
                                        {{ $row->product_name }}
                                    </td>

                                    <!-- Jumlah stok masuk -->
                                    <td style="padding:14px 16px; border-bottom:1px solid #F0EBE5;">
                                        {{ number_format((int) $row->stock_in) }}
                                    </td>

                                    <!-- Jumlah stok keluar -->
                                    <td style="padding:14px 16px; border-bottom:1px solid #F0EBE5;">
                                        {{ number_format((int) $row->stock_out) }}
                                    </td>

                                    <!-- Profit produk -->
                                    <td style="padding:14px 16px; border-bottom:1px solid #F0EBE5;">
                                        {{ format_money($row->profit, $business->currency) }}
                                    </td>

                                    <!-- Stok saat ini -->
                                    <td style="padding:14px 16px; border-bottom:1px solid #F0EBE5;">
                                        {{ number_format((int) $row->current_stock) }}
                                    </td>
                                </tr>
                            @endforeach
                        @endif

                        </tbody>
                    </table>

                </div>

            </div>

@endsection

@push('scripts')
<!-- Iframe tersembunyi untuk memproses cetak PDF -->
<iframe id="pdfFrame" style="display:none;"></iframe>

<!-- Library Ikon & Asset JS -->
<script src="https://unpkg.com/lucide@latest"></script>
<script src="{{ asset('assets/js/csrf.js') }}"></script>
<script src="{{ asset('assets/js/toast.js') }}" defer></script>

<script>
// Inisialisasi ikon Lucide
lucide.createIcons();

// Penanda apakah ada data laporan yang bisa diekspor
const hasReportData = {{ $totalProducts > 0 ? 'true' : 'false' }};

// Menangani ekspor tabel laporan menjadi file CSV
async function exportTableToCSV(defaultFilename) {
    // Batalkan jika tidak ada data
    if (!hasReportData) {
        showToast({{ Illuminate\Support\Js::from(t('no_data_to_export')) }}, 'error');
        return;
    }

    const table = document.getElementById('reportTable');
    let csv = [];

    // Baca isi tabel baris per baris
    for (let row of table.rows) {
        let cols = Array.from(row.cells).map(cell => `"${cell.innerText.replace(/"/g, '""')}"`);
        csv.push(cols.join(','));
    }

    // Tambahkan BOM agar karakter UTF-8 terbaca benar di Excel
    const csvData = '\uFEFF' + csv.join('\n');

    // Gunakan dialog simpan bawaan browser jika didukung
    if ('showSaveFilePicker' in window) {
        try {
            const handle = await window.showSaveFilePicker({
                suggestedName: defaultFilename,
                types: [{
                    description: 'CSV File',
                    accept: { 'text/csv': ['.csv'] },
                }],
            });
            const writable = await handle.createWritable();
            await writable.write(csvData);
            await writable.close();
            showToast({{ Illuminate\Support\Js::from(t('csv_export_success')) }}, 'success');
        } catch (err) {
            // Pengguna membatalkan dialog penyimpanan
        }
    } else {
        // Fallback: unduh otomatis via link sementara
        try {
            const csvFile = new Blob([csvData], { type: 'text/csv;charset=utf-8;' });
            const downloadLink = document.createElement('a');
            downloadLink.download = defaultFilename;
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = 'none';
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
            showToast({{ Illuminate\Support\Js::from(t('csv_export_success')) }}, 'success');
        } catch (e) {
            showToast({{ Illuminate\Support\Js::from(t('csv_export_failed')) }}, 'error');
        }
    }
}

// Mencetak / menyimpan laporan sebagai PDF melalui iframe tersembunyi
function printPDF(url) {
    // Batalkan jika tidak ada data
    if (!hasReportData) {
        showToast({{ Illuminate\Support\Js::from(t('no_data_to_export')) }}, 'error');
        return;
    }

    const iframe = document.getElementById('pdfFrame');
    iframe.src = url;

    iframe.onload = function() {
        try {
            iframe.contentWindow.focus();

            // Tampilkan notifikasi setelah proses cetak selesai
            iframe.contentWindow.addEventListener('afterprint', function handleAfterPrint() {
                showToast({{ Illuminate\Support\Js::from(t('pdf_export_success') ?? 'Dokumen PDF berhasil diproses.') }}, 'success');
                iframe.contentWindow.removeEventListener('afterprint', handleAfterPrint);
            });

            iframe.contentWindow.print();
        } catch (e) {
            console.error(e);
            showToast({{ Illuminate\Support\Js::from(t('pdf_export_failed') ?? 'Gagal memuat dokumen PDF.') }}, 'error');
        }
    };
}

</script>
@endpush
