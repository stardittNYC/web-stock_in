<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use Illuminate\Http\Request;

/**
 * Controller untuk menampilkan riwayat seluruh transaksi inventaris (Stok Masuk, Stok Keluar, dan Audit Stok).
 */
class HistoryController extends Controller
{
    /**
     * Menampilkan daftar transaksi inventaris berdasarkan filter tipe, rentang tanggal, dan pencarian.
     */
    public function index(Request $request)
    {
        // Ambil objek bisnis yang sedang aktif dari atribut request (middleware)
        $business = $request->attributes->get('business');

        // Daftar tipe transaksi yang diizinkan oleh sistem
        $allowedTypes = ['stock_in', 'stock_out', 'stock_audit'];

        // Ambil input parameter filter riwayat transaksi dari URL (Query String)
        $type = (string) $request->input('type', '');
        $dateFrom = (string) $request->input('date_from', '');
        $dateTo = (string) $request->input('date_to', '');
        $search = trim((string) $request->input('search', ''));

        // Validasi tipe transaksi agar hanya memproses jenis yang terdaftar
        if (! in_array($type, $allowedTypes, true)) {
            $type = '';
        }

        // Validasi format tanggal awal agar sesuai dengan format standar YYYY-MM-DD
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
            $dateFrom = '';
        }

        // Validasi format tanggal akhir agar sesuai dengan format standar YYYY-MM-DD
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
            $dateTo = '';
        }

        // Siapkan query untuk mengambil riwayat transaksi sesuai bisnis aktif
        $query = InventoryTransaction::where('business_id', $business->id)
            ->select([
                'id',
                'product_name',
                'product_code',
                'transaction_type',
                'quantity',
                'stock_before',
                'stock_after',
                'notes',
                'transaction_date',
                'selling_price',
                'total_amount',
            ]);

        // Filter berdasarkan jenis transaksi jika dipilih
        if ($type !== '') {
            $query->where('transaction_type', $type);
        }

        // Filter berdasarkan batas awal tanggal transaksi
        if ($dateFrom !== '') {
            $query->whereRaw('DATE(transaction_date) >= ?', [$dateFrom]);
        }

        // Filter berdasarkan batas akhir tanggal transaksi
        if ($dateTo !== '') {
            $query->whereRaw('DATE(transaction_date) <= ?', [$dateTo]);
        }

        // Filter pencarian berdasarkan nama produk, kode produk, atau catatan transaksi
        if ($search !== '') {
            $query->where(function ($w) use ($search) {
                $w->where('product_name', 'like', '%'.$search.'%')
                    ->orWhere('product_code', 'like', '%'.$search.'%')
                    ->orWhere('notes', 'like', '%'.$search.'%');
            });
        }

        // Eksekusi query dengan pengurutan dari transaksi yang paling baru
        $transactions = $query
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        // Hitung total data transaksi yang ditemukan
        $total = $transactions->count();

        // Tampilkan view riwayat transaksi beserta data filter
        return view('dashboard.history', compact(
            'transactions',
            'total',
            'type',
            'dateFrom',
            'dateTo',
            'search',
            'allowedTypes'
        ));
    }
}
