<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Controller untuk mengelola proses audit (stock opname) fisik persediaan barang.
 */
class StockAuditController extends Controller
{
    /**
     * Menampilkan halaman formulir audit stok fisik produk.
     */
    public function index(Request $request)
    {
        // Ambil data bisnis aktif dari atribut request
        $business = $request->attributes->get('business');

        // Ambil seluruh produk milik bisnis aktif
        $products = Product::where('business_id', $business->id)
            ->orderBy('product_name')
            ->get(['id', 'product_name', 'current_stock', 'category_id']);

        // Ambil daftar kategori produk untuk opsi filter dropdown
        $categories = Category::where('business_id', $business->id)
            ->orderBy('category_name')
            ->get(['id', 'category_name']);

        // Render tampilan halaman audit stok
        return view('dashboard.stock-audit', compact('products', 'categories'));
    }

    /**
     * Menyimpan hasil penyesuaian audit stok fisik, memperbarui stok produk, dan mencatat riwayat selisih audit.
     */
    public function store(Request $request)
    {
        // Ambil data bisnis aktif
        $business = $request->attributes->get('business');

        // Tangkap dan validasi parameter input dari form audit
        $productId = filter_var($request->input('product_id'), FILTER_VALIDATE_INT);
        $physicalStock = filter_var($request->input('physical_stock'), FILTER_VALIDATE_INT);
        $notes = trim((string) $request->input('notes', ''));

        // Memvalidasi agar nilai ID produk dan kuantitas stok fisik valid (tidak bernilai negatif)
        if (
            $productId === false || $productId === null
            || $physicalStock === false || $physicalStock === null || $physicalStock < 0
        ) {
            return response()->json([
                'success' => false,
                'message' => t('invalid_data'),
            ]);
        }

        try {
            // Memulai transaksi database agar penyesuaian stok dan riwayat tersimpan sinkron
            DB::beginTransaction();

            // Mengambil dan mengunci data produk (FOR UPDATE)
            $product = Product::where('id', $productId)
                ->where('business_id', $business->id)
                ->lockForUpdate()
                ->first();

            // Pastikan produk ditemukan pada bisnis terkait
            if (! $product) {
                throw new \Exception(t('product_not_found'));
            }

            // Hitung nilai stok sistem sebelumnya dan selisih dengan stok fisik riil
            $stockBefore = (int) $product->current_stock;
            $difference = $physicalStock - $stockBefore;

            // Jika tidak ada perubahan antara stok fisik dan stok sistem, akhiri tanpa perubahan
            if ($difference === 0) {
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => t('no_stock_change'),
                ]);
            }

            // Perbarui jumlah stok sistem pada tabel produk sesuai jumlah stok fisik riil
            $product->update(['current_stock' => $physicalStock]);

            // Menyimpan catatan riwayat penyesuaian audit stok ke database
            InventoryTransaction::create([
                'business_id' => $business->id,
                'product_id' => $productId,
                'product_name' => $product->product_name,
                'product_code' => $product->product_code,
                'transaction_type' => 'stock_audit',
                'quantity' => $difference,
                'stock_before' => $stockBefore,
                'stock_after' => $physicalStock,
                'purchase_price' => (float) $product->purchase_price,
                'selling_price' => (float) $product->selling_price,
                'notes' => $notes,
                'transaction_date' => now(),
            ]);

            // Commit seluruh operasi transaksi ke database jika berhasil
            DB::commit();

            // Mengembalikan respon sukses penyimpanan audit stok
            return response()->json([
                'success' => true,
                'message' => t('audit_save_success'),
            ]);
        } catch (Throwable $e) {
            // Batalkan transaksi database jika terjadi kendala
            DB::rollBack();
            report($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
