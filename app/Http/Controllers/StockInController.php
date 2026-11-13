<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Controller untuk mengelola transaksi barang masuk (Stock In) dan penambahan stok produk.
 */
class StockInController extends Controller
{
    /**
     * Menampilkan halaman formulir pencatatan barang masuk (Stock In).
     */
    public function index(Request $request)
    {
        // Ambil data bisnis aktif dari atribut request
        $business = $request->attributes->get('business');

        // Ambil daftar produk milik bisnis aktif yang diurutkan sesuai nama produk (A-Z)
        $products = Product::where('business_id', $business->id)
            ->orderBy('product_name')
            ->get(['id', 'product_name', 'product_code', 'current_stock', 'category_id']);

        // Hitung total jumlah produk yang tersedia
        $productCount = $products->count();

        // Ambil daftar kategori produk untuk filter dropdown
        $categories = Category::where('business_id', $business->id)
            ->orderBy('category_name')
            ->get(['id', 'category_name']);

        // Render tampilan halaman stock-in
        return view('dashboard.stock-in', compact('products', 'productCount', 'categories'));
    }

    /**
     * Menyimpan transaksi barang masuk, menambahkan jumlah stok produk, dan mencatat riwayat transaksi.
     */
    public function store(Request $request)
    {
        // Ambil data bisnis aktif
        $business = $request->attributes->get('business');
        $businessId = $business->id;

        // Tangkap dan amankan parameter input
        $productId = (int) $request->input('product_id');
        $quantity = filter_var($request->input('quantity'), FILTER_VALIDATE_INT);

        $notesInput = $request->input('notes', '');
        $notes = is_string($notesInput) ? trim($notesInput) : '';
        $notes = $notes === '' ? null : $notes;

        // Validasi keberadaan ID produk
        if ($productId < 1) {
            return response()->json([
                'status' => 'error',
                'message' => t('invalid_business_product'),
            ], 400);
        }

        // Validasi kuantitas barang masuk agar bernilai minimal 1
        if ($quantity === false || $quantity === null || $quantity < 1) {
            return response()->json([
                'status' => 'error',
                'message' => t('invalid_quantity'),
            ], 400);
        }

        try {
            // Memulai transaksi database agar update stok dan pencatatan riwayat bersifat atomik
            DB::beginTransaction();

            // Mengambil dan mengunci baris produk (FOR UPDATE) guna mencegah race condition
            $product = Product::where('id', $productId)
                ->where('business_id', $businessId)
                ->lockForUpdate()
                ->first();

            // Tolak jika produk tidak ditemukan atau bukan milik bisnis aktif
            if (! $product) {
                DB::rollBack();

                return response()->json([
                    'status' => 'error',
                    'message' => t('product_access_denied'),
                ], 404);
            }

            // Menghitung jumlah stok awal dan batas maksimum kapasitas integer
            $stockBefore = (int) $product->current_stock;
            $maxStock = 2147483647;

            // Memvalidasi agar penambahan stok tidak melebihi batas kapasitas maksimal
            if ($quantity > $maxStock - $stockBefore) {
                DB::rollBack();

                return response()->json([
                    'status' => 'error',
                    'message' => t('stock_limit_exceeded'),
                ], 400);
            }

            // Hitung nilai stok akhir setelah penambahan barang masuk
            $stockAfter = $stockBefore + $quantity;

            // Perbarui jumlah stok terkini pada tabel produk
            $product->update(['current_stock' => $stockAfter]);

            // Menyimpan catatan bukti riwayat transaksi ke database
            $transaction = InventoryTransaction::create([
                'business_id' => $businessId,
                'product_id' => $productId,
                'product_name' => $product->product_name,
                'product_code' => $product->product_code,
                'transaction_type' => 'stock_in',
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'purchase_price' => (float) $product->purchase_price,
                'selling_price' => (float) $product->selling_price,
                'total_amount' => 0,
                'notes' => $notes,
                'transaction_date' => now(),
            ]);

            // Commit seluruh operasi transaksi ke database jika berhasil
            DB::commit();

            // Mengembalikan respon JSON sukses penambahan stok masuk
            return response()->json([
                'status' => 'success',
                'message' => t('stock_in_save_success'),
                'transaction_id' => $transaction->id,
                'product_id' => $productId,
                'quantity_added' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
            ]);
        } catch (Throwable $e) {
            // Batalkan transaksi jika terjadi kesalahan
            DB::rollBack();
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => t('stock_in_save_failed'),
            ], 500);
        }
    }
}
