<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Controller untuk mengelola halaman utama Dashboard bisnis pengguna.
 */
class DashboardController extends Controller
{
    /**
     * Menampilkan metrik statistik ringkasan dan daftar 5 produk terlaris hari ini.
     */
    public function index(Request $request)
    {
        // Ambil objek bisnis yang sedang aktif dari atribut request (middleware)
        $business = $request->attributes->get('business');

        // Set batas waktu awal hari ini (00:00:00) dan awal hari esok
        $todayStart = now()->startOfDay();
        $tomorrowStart = now()->startOfDay()->addDay();

        // Statistik transaksi hari ini: total stok masuk, stok keluar, pendapatan, dan laba
        $dailyStats = InventoryTransaction::where('business_id', $business->id)
            ->where('transaction_date', '>=', $todayStart)
            ->where('transaction_date', '<', $tomorrowStart)
            ->selectRaw("COALESCE(SUM(CASE WHEN transaction_type = 'stock_in' THEN quantity ELSE 0 END), 0) AS total_stock_in")
            ->selectRaw("COALESCE(SUM(CASE WHEN transaction_type = 'stock_out' THEN quantity ELSE 0 END), 0) AS total_stock_out")
            ->selectRaw("COALESCE(SUM(CASE WHEN transaction_type = 'stock_out' THEN total_amount ELSE 0 END), 0) AS total_revenue")
            ->selectRaw("COALESCE(SUM(CASE WHEN transaction_type = 'stock_out' THEN (selling_price - purchase_price) * quantity ELSE 0 END), 0) AS total_profit")
            ->first();

        // Jumlah produk dan total stok tersedia saat ini pada bisnis aktif
        $stockStats = Product::where('business_id', $business->id)
            ->selectRaw('COUNT(*) AS total_products, COALESCE(SUM(current_stock), 0) AS total_stock')
            ->first();

        // Query untuk mengambil 5 produk teratas berdasarkan unit yang paling banyak terjual hari ini
        $topProducts = Product::query()
            ->join('inventory_transactions as t', function ($join) use ($todayStart, $tomorrowStart) {
                $join->on('t.product_id', '=', 'products.id')
                    ->where('t.transaction_type', '=', 'stock_out')
                    ->where('t.transaction_date', '>=', $todayStart)
                    ->where('t.transaction_date', '<', $tomorrowStart);
            })
            ->where('products.business_id', $business->id)
            ->groupBy('products.id', 'products.product_name', 'products.photo', 'products.current_stock')
            ->selectRaw('products.id AS product_id, products.product_name, products.photo, products.current_stock, COALESCE(SUM(t.quantity), 0) AS total_sold')
            ->orderByRaw('total_sold DESC, products.current_stock DESC')
            ->limit(5)
            ->get();

        // Render tampilan halaman dashboard beserta data statistik
        return view('dashboard.index', compact('dailyStats', 'stockStats', 'topProducts'));
    }
}
