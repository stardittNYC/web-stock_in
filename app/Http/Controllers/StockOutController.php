<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Controller untuk mengelola transaksi barang keluar (Stock Out) dan pengurangan stok penjualan produk.
 */
class StockOutController extends Controller
{
    /**
     * Menampilkan halaman formulir pencatatan barang keluar (Stock Out).
     */
    public function index(Request $request)
    {
        // Ambil data bisnis aktif dari atribut request
        $business = $request->attributes->get('business');

        // Ambil daftar produk milik bisnis aktif yang memiliki harga jual
        $products = Product::where('business_id', $business->id)
            ->orderBy('product_name')
            ->get(['id', 'product_name', 'current_stock', 'selling_price', 'category_id']);

        // Ambil daftar kategori produk untuk opsi filter dropdown
        $categories = Category::where('business_id', $business->id)
            ->orderBy('category_name')
            ->get(['id', 'category_name']);

        // Render tampilan halaman stock-out
        return view('dashboard.stock-out', compact('products', 'categories'));
    }

    /**
     * Menyimpan transaksi barang keluar, mengurangi jumlah stok produk, dan mencatat riwayat transaksi penjualan.
     */
    public function store(Request $request)
    {
        // Ambil data bisnis aktif
        $business = $request->attributes->get('business');
        $businessId = $business->id;

        // Tangkap dan memvalidasi input parameter dari request
        $productId = (int) $request->input('product_id');
        $quantity = filter_var($request->input('quantity'), FILTER_VALIDATE_INT);
        $notes = trim((string) $request->input('notes', ''));

        // Memvalidasi input agar ID produk dan kuantitas barang keluar bernilai minimal 1
        if ($productId < 1 || $quantity === false || $quantity === null || $quantity < 1) {
            return response()->json([
                'success' => false,
                'message' => t('invalid_data'),
            ]);
        }

        try {
            // Memulai transaksi database agar pengurangan stok dan riwayat tersimpan atomik
            DB::beginTransaction();

            // Mengambil dan mengunci data produk (FOR UPDATE) untuk menghindari race condition
            $product = Product::where('id', $productId)
                ->where('business_id', $businessId)
                ->lockForUpdate()
                ->first();

            // Pastikan produk ditemukan pada bisnis terkait
            if (! $product) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => t('product_not_found'),
                ]);
            }

            // Memeriksa apakah jumlah stok yang tersedia mencukupi permintaan pengeluaran
            if ((int) $product->current_stock < $quantity) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => t('insufficient_stock'),
                ]);
            }

            // Hitung nilai stok sebelum dan sesudah transaksi, serta total nominal penjualan
            $stockBefore = (int) $product->current_stock;
            $stockAfter = $stockBefore - $quantity;
            $totalAmount = $quantity * (float) $product->selling_price;

            // Perbarui jumlah stok terkini pada tabel produk
            $product->update(['current_stock' => $stockAfter]);

            // Menyimpan bukti riwayat transaksi barang keluar ke database
            InventoryTransaction::create([
                'business_id' => $businessId,
                'product_id' => $productId,
                'product_name' => $product->product_name,
                'product_code' => $product->product_code,
                'transaction_type' => 'stock_out',
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'purchase_price' => (float) $product->purchase_price,
                'selling_price' => (float) $product->selling_price,
                'total_amount' => $totalAmount,
                'notes' => $notes,
                'transaction_date' => now(),
            ]);

            // Commit seluruh operasi transaksi ke database jika berhasil
            DB::commit();

            // Mengembalikan respon sukses transaksi barang keluar
            return response()->json([
                'success' => true,
                'message' => t('stock_out_save_success'),
            ]);
        } catch (Throwable $e) {
            // Batalkan transaksi database jika terjadi error
            DB::rollBack();
            report($e);

            return response()->json([
                'success' => false,
                'message' => t('history_save_failed'),
            ]);
        }
    }
}
