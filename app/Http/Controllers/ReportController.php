<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controller untuk mengelola laporan inventaris, laba kotor, analitik grafik, dan ekspor cetak PDF.
 */
class ReportController extends Controller
{
    /**
     * Daftar kolom laporan yang diizinkan untuk pengurutan data.
     */
    private const SORTABLE = ['product_name', 'stock_in', 'stock_out', 'profit', 'current_stock'];

    /**
     * Menampilkan laporan rekapitulasi pergerakan barang, penjualan, dan keuntungan.
     */
    public function index(Request $request)
    {
        // Ambil objek bisnis aktif dari request
        $business = $request->attributes->get('business');
        // Validasi dan parsing rentang tanggal laporan
        [$dateFrom, $dateTo] = $this->resolveDates($request);

        // Ambil parameter opsi urutan dari URL, atur default ke 'profit' jika kosong
        $sort = (string) $request->input('sort', 'profit');
        if (! in_array($sort, self::SORTABLE, true)) {
            $sort = 'profit';
        }

        // Ambil arah pengurutan (ASC/DESC), default ke DESC
        $order = strtoupper((string) $request->input('order', 'DESC'));
        if (! in_array($order, ['ASC', 'DESC'], true)) {
            $order = 'DESC';
        }

        // Eksekusi query agregasi laporan inventaris
        $reports = $this->aggregateQuery($business->id, $dateFrom, $dateTo)
            ->orderBy($sort, $order)
            ->get();

        // Hitung total ringkasan untuk kartu metrik
        $totalProducts = $reports->count();
        $totalStock = $reports->sum(fn ($row) => (int) $row->current_stock);
        $totalStockIn = $reports->sum(fn ($row) => (int) $row->stock_in);
        $totalStockOut = $reports->sum(fn ($row) => (int) $row->stock_out);
        $totalProfit = $reports->sum(fn ($row) => (float) $row->profit);

        // Hitung total nilai modal (aset stok barang) saat ini
        $totalModalValue = (float) DB::table('products')
            ->where('business_id', $business->id)
            ->sum(DB::raw('current_stock * purchase_price'));

        // Render tampilan halaman laporan
        return view('dashboard.reports', compact(
            'reports',
            'totalProducts',
            'totalStock',
            'totalStockIn',
            'totalStockOut',
            'totalProfit',
            'totalModalValue',
            'dateFrom',
            'dateTo',
            'sort',
            'order'
        ));
    }

    /**
     * Menampilkan halaman analitik visual (grafik batang dan garis) untuk 10 produk dengan penjualan terbanyak.
     */
    public function analytics(Request $request)
    {
        // Ambil objek bisnis aktif
        $business = $request->attributes->get('business');
        [$dateFrom, $dateTo] = $this->resolveDates($request);

        // Ambil data 10 produk teratas berdasarkan jumlah barang keluar
        $rows = $this->aggregateQuery($business->id, $dateFrom, $dateTo)
            ->orderByDesc('stock_out')
            ->limit(10)
            ->get();

        // Ekstrak array data untuk dirender ke dalam Chart.js
        $productNames = $rows->pluck('product_name')->all();
        $stockIns = $rows->map(fn ($row) => (int) $row->stock_in)->all();
        $stockOuts = $rows->map(fn ($row) => (int) $row->stock_out)->all();
        $profits = $rows->map(fn ($row) => (float) $row->profit)->all();

        // Render tampilan grafik analitik
        return view('dashboard.reports-analytics', compact(
            'productNames',
            'stockIns',
            'stockOuts',
            'profits',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Menampilkan halaman cetak/ekspor PDF laporan inventaris yang siap cetak lewat browser (Ctrl+P).
     */
    public function exportPdf(Request $request)
    {
        // Ambil objek bisnis aktif
        $business = $request->attributes->get('business');
        [$dateFrom, $dateTo] = $this->resolveDates($request);

        // Ambil seluruh data laporan yang diurutkan menurut nama produk (A-Z)
        $reports = $this->aggregateQuery($business->id, $dateFrom, $dateTo)
            ->orderBy('product_name', 'asc')
            ->get();

        // Hitung total metrik ringkasan
        $totalProducts = $reports->count();
        $totalStock = $reports->sum(fn ($row) => (int) $row->current_stock);
        $totalStockIn = $reports->sum(fn ($row) => (int) $row->stock_in);
        $totalStockOut = $reports->sum(fn ($row) => (int) $row->stock_out);
        $totalProfit = $reports->sum(fn ($row) => (float) $row->profit);

        // Hitung total nilai modal barang
        $totalModalValue = (float) DB::table('products')
            ->where('business_id', $business->id)
            ->sum(DB::raw('current_stock * purchase_price'));

        // Render tampilan halaman siap cetak PDF
        return view('dashboard.export-pdf', compact(
            'reports',
            'totalProducts',
            'totalStock',
            'totalStockIn',
            'totalStockOut',
            'totalProfit',
            'totalModalValue',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Query pembantu untuk mengagregasi data transaksi inventaris per produk dalam rentang tanggal tertentu.
     */
    private function aggregateQuery(int $businessId, string $dateFrom, string $dateTo)
    {
        // Menggabungkan tabel produk dengan riwayat transaksi dalam rentang tanggal yang dipilih
        return Product::query()
            ->leftJoin('inventory_transactions as it', function ($join) use ($dateFrom, $dateTo) {
                $join->on('it.product_id', '=', 'products.id')
                    ->whereRaw('DATE(it.transaction_date) BETWEEN ? AND ?', [$dateFrom, $dateTo]);
            })
            ->where('products.business_id', $businessId)
            ->groupBy('products.id', 'products.product_name', 'products.current_stock')
            ->selectRaw('products.id, products.product_name, products.current_stock')
            ->selectRaw("COALESCE(SUM(CASE WHEN it.transaction_type = 'stock_in' THEN it.quantity ELSE 0 END), 0) AS stock_in")
            ->selectRaw("COALESCE(SUM(CASE WHEN it.transaction_type = 'stock_out' THEN it.quantity ELSE 0 END), 0) AS stock_out")
            ->selectRaw("COALESCE(SUM(CASE WHEN it.transaction_type = 'stock_out' THEN (it.selling_price - it.purchase_price) * it.quantity ELSE 0 END), 0) AS profit");
    }

    /**
     * Memvalidasi format tanggal awal dan akhir dari URL, atau memberikan nilai tanggal default (bulan berjalan).
     */
    private function resolveDates(Request $request): array
    {
        // Filter tanggal laporan: awal bulan berjalan sampai hari ini
        $dateFrom = (string) $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = (string) $request->input('date_to', now()->toDateString());

        // Validasi format tanggal awal standar YYYY-MM-DD
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
            $dateFrom = now()->startOfMonth()->toDateString();
        }

        // Validasi format tanggal akhir standar YYYY-MM-DD
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
            $dateTo = now()->toDateString();
        }

        return [$dateFrom, $dateTo];
    }
}
