<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Controller untuk mengelola seluruh siklus data produk (Katalog, Tambah, Detail, Edit, dan Hapus).
 */
class ProductController extends Controller
{
    /**
     * Menampilkan halaman daftar produk dengan fitur pencarian, filter kategori, dan pengurutan kode produk.
     */
    public function index(Request $request)
    {
        // Ambil data bisnis aktif dari atribut request (middleware)
        $business = $request->attributes->get('business');

        // Parameter pencarian dan pengurutan
        $search = trim((string) $request->input('search', ''));
        $sortCode = (string) $request->input('sort_code', '');

        // Parameter filter kategori
        $categoryId = $request->input('category_id');
        $categoryId = ($categoryId === null || $categoryId === '') ? null : (int) $categoryId;

        // Ambil daftar kategori milik bisnis aktif untuk dropdown filter
        $categories = Category::where('business_id', $business->id)
            ->orderBy('category_name')
            ->get();

        // Query produk beserta nama kategorinya
        $query = Product::query()
            ->leftJoin('categories as c', function ($join) {
                $join->on('c.id', '=', 'products.category_id')
                    ->on('c.business_id', '=', 'products.business_id');
            })
            ->where('products.business_id', $business->id)
            ->select('products.*', 'c.category_name');

        // Filter pencarian berdasarkan nama produk atau kode produk
        if ($search !== '') {
            $query->where(function ($w) use ($search) {
                $w->where('products.product_name', 'like', '%'.$search.'%')
                    ->orWhere('products.product_code', 'like', '%'.$search.'%');
            });
        }

        // Filter berdasarkan kategori produk jika kategori dipilih
        if ($categoryId !== null) {
            $query->where('products.category_id', $categoryId);
        }

        // Tentukan aturan pengurutan SQL berdasarkan pilihan pengguna
        if ($sortCode === 'ASC') {
            $query->orderBy('products.product_code', 'asc');
        } elseif ($sortCode === 'DESC') {
            $query->orderBy('products.product_code', 'desc');
        } else {
            // Default pengurutan alfabetis berdasarkan nama produk (A-Z)
            $query->orderBy('products.product_name', 'asc');
        }

        // Eksekusi query produk
        $products = $query->get();

        // Render tampilan halaman produk
        return view('dashboard.products', compact('products', 'categories', 'search', 'sortCode', 'categoryId'));
    }

    /**
     * Menyimpan data produk baru ke dalam database beserta berkas fotonya dan mencatat stok awal.
     */
    public function store(Request $request)
    {
        // Ambil bisnis aktif
        $business = $request->attributes->get('business');
        $businessId = $business->id;

        // Tangkap input nama dan kode produk
        $productName = trim((string) $request->input('product_name', ''));
        $productCode = trim((string) $request->input('product_code', ''));

        // Validasi agar nama produk dan kode produk tidak kosong
        if ($productName === '' || $productCode === '') {
            return response()->json([
                'status' => 'error',
                'message' => t('product_name_code_req'),
            ], 400);
        }

        // Menolak jika kode produk sudah digunakan pada bisnis yang sama
        if (Product::where('business_id', $businessId)->where('product_code', $productCode)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => t('duplicate_product_code'),
            ], 400);
        }

        // Tangkap ID kategori produk jika dipilih
        $categoryId = $request->filled('category_id') ? (int) $request->input('category_id') : null;

        // Normalisasi format desimal untuk harga beli dan harga jual
        $purchasePrice = str_replace(',', '.', trim((string) $request->input('purchase_price', '0')));
        $sellingPrice = str_replace(',', '.', trim((string) $request->input('selling_price', '0')));

        // Validasi keabsahan nilai nominal harga beli dan jual
        if (
            ! is_numeric($purchasePrice) || ! is_numeric($sellingPrice)
            || (float) $purchasePrice < 0 || (float) $sellingPrice < 0
        ) {
            return response()->json([
                'status' => 'error',
                'message' => t('invalid_price'),
            ], 400);
        }

        // Tangkap stok awal, tanggal kedaluwarsa, dan catatan
        $initialStock = (int) $request->input('initial_stock', 0);
        $expirationDate = $request->filled('expiration_date') ? $request->input('expiration_date') : null;
        $notes = $request->input('notes');

        // Menangani validasi berkas foto produk jika diunggah
        $file = $request->file('photo');
        $photoExtension = null;

        if ($file !== null) {
            if (! $file->isValid()) {
                $isSizeError = in_array($file->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true);

                return response()->json([
                    'status' => 'error',
                    'message' => $isSizeError ? t('photo_max_size') : t('photo_upload_failed'),
                ], 400);
            }

            // Batas maksimal ukuran foto 5MB
            if ($file->getSize() > 5 * 1024 * 1024) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('photo_max_size'),
                ], 400);
            }

            // Memvalidasi format gambar asli
            $imageInfo = @getimagesize($file->getRealPath());
            if ($imageInfo === false) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('invalid_image_file'),
                ], 400);
            }

            $mimeTypes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
            ];

            $mime = $imageInfo['mime'] ?? '';
            if (! isset($mimeTypes[$mime])) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('invalid_image_format'),
                ], 400);
            }

            $photoExtension = $mimeTypes[$mime];
        }

        // Memulai transaksi database agar penambahan produk dan transaksi stok awal tersimpan atomik
        try {
            DB::beginTransaction();

            $product = Product::create([
                'business_id' => $businessId,
                'category_id' => $categoryId,
                'product_name' => $productName,
                'product_code' => $productCode,
                'purchase_price' => (float) $purchasePrice,
                'selling_price' => (float) $sellingPrice,
                'initial_stock' => $initialStock,
                'current_stock' => $initialStock,
                'expiration_date' => $expirationDate,
                'notes' => $notes,
            ]);

            // Jika ada stok awal, catat otomatis ke riwayat transaksi inventaris sebagai stock_in
            if ($initialStock > 0) {
                InventoryTransaction::create([
                    'business_id' => $businessId,
                    'product_id' => $product->id,
                    'product_name' => $product->product_name,
                    'product_code' => $product->product_code,
                    'transaction_type' => 'stock_in',
                    'quantity' => $initialStock,
                    'stock_before' => 0,
                    'stock_after' => $initialStock,
                    'purchase_price' => (float) $purchasePrice,
                    'selling_price' => (float) $sellingPrice,
                    'total_amount' => 0,
                    'notes' => ! empty(trim((string) $notes)) ? trim((string) $notes) : t('initial_stock'),
                    'transaction_date' => now(),
                ]);
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => t('product_create_failed'),
            ], 500);
        }

        // Simpan file foto produk jika berhasil diunggah
        if ($file !== null && $photoExtension !== null) {
            $photoName = time().'.'.$photoExtension;

            $stored = Storage::disk('uploads')->putFileAs('products/'.$product->id, $file, $photoName);
            if ($stored) {
                $product->update(['photo' => $photoName]);
            }
        }

        // Mengembalikan respon JSON sukses pembuatan produk
        return response()->json([
            'status' => 'success',
            'message' => t('product_create_success'),
            'product_id' => $product->id,
        ]);
    }

    /**
     * Mengambil data detail suatu produk dalam format JSON untuk pengisian form edit modal.
     */
    public function show(Request $request, Product $product)
    {
        // Ambil objek bisnis aktif
        $business = $request->attributes->get('business');

        // Pastikan produk yang diminta merupakan milik bisnis aktif
        if ((int) $product->business_id !== (int) $business->id) {
            return response()->json([
                'status' => 'error',
                'message' => t('product_not_found'),
            ]);
        }

        // Konversi objek produk ke array dan sertakan URL lengkap gambar produk jika ada
        $data = $product->toArray();
        $data['photo_url'] = (! empty($product->photo) && file_exists(public_path('uploads/products/'.$product->id.'/'.$product->photo)))
            ? asset('uploads/products/'.$product->id.'/'.$product->photo)
            : null;

        // Mengembalikan respon JSON detail produk
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    /**
     * Memperbarui informasi data produk (termasuk foto) pada database.
     */
    public function update(Request $request, Product $product)
    {
        // Ambil objek bisnis aktif
        $business = $request->attributes->get('business');
        $businessId = $business->id;

        // Pastikan produk yang akan diperbarui milik bisnis aktif
        if ((int) $product->business_id !== (int) $businessId) {
            return response()->json([
                'status' => 'error',
                'message' => t('product_not_found'),
            ], 404);
        }

        // Tangkap input nama dan kode produk
        $productName = trim((string) $request->input('product_name', ''));
        $productCode = trim((string) $request->input('product_code', ''));
        $initialStock = filter_var($request->input('initial_stock'), FILTER_VALIDATE_INT);

        // Validasi input data produk
        if ($productName === '' || $initialStock === false || $initialStock === null || $initialStock < 0) {
            return response()->json([
                'status' => 'error',
                'message' => t('product_name_code_req'),
            ], 400);
        }

        // Memeriksa keunikan kode produk agar tidak bertabrakan dengan produk lain
        if ($productCode !== '') {
            $duplicate = Product::where('business_id', $businessId)
                ->where('product_code', $productCode)
                ->where('id', '!=', $product->id)
                ->exists();

            if ($duplicate) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('duplicate_product_code'),
                ], 400);
            }
        }

        // Normalisasi format desimal harga beli dan harga jual
        $purchasePrice = str_replace(',', '.', trim((string) $request->input('purchase_price', '0')));
        $sellingPrice = str_replace(',', '.', trim((string) $request->input('selling_price', '0')));

        // Validasi nominal harga
        if (
            ! is_numeric($purchasePrice) || ! is_numeric($sellingPrice)
            || (float) $purchasePrice < 0 || (float) $sellingPrice < 0
        ) {
            return response()->json([
                'status' => 'error',
                'message' => t('invalid_price'),
            ], 400);
        }

        // Validasi kategori jika dipilih
        $categoryId = $request->filled('category_id') ? (int) $request->input('category_id') : null;
        if ($categoryId !== null) {
            if (! Category::where('id', $categoryId)->where('business_id', $businessId)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('category_not_found'),
                ], 400);
            }
        }

        // Validasi format tanggal kedaluwarsa jika disertakan
        $expirationDate = $request->filled('expiration_date') ? $request->input('expiration_date') : null;
        if ($expirationDate !== null) {
            $date = \DateTime::createFromFormat('!Y-m-d', $expirationDate);
            if (! $date || $date->format('Y-m-d') !== $expirationDate) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('invalid_expiry_date'),
                ], 400);
            }
        }

        // Menangani unggahan foto baru jika ada
        $file = $request->file('photo');
        $photoExtension = null;

        if ($file !== null) {
            if (! $file->isValid()) {
                $isSizeError = in_array($file->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true);

                return response()->json([
                    'status' => 'error',
                    'message' => $isSizeError ? t('photo_max_size') : t('photo_upload_failed'),
                ], 400);
            }

            if ($file->getSize() > 5 * 1024 * 1024) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('photo_max_size'),
                ], 400);
            }

            $imageInfo = @getimagesize($file->getRealPath());

            if ($imageInfo === false) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('invalid_image_file'),
                ], 400);
            }

            $mimeTypes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
            ];

            $mime = $imageInfo['mime'] ?? '';

            if (! isset($mimeTypes[$mime])) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('invalid_image_format'),
                ], 400);
            }

            $photoExtension = $mimeTypes[$mime];
        }

        // Eksekusi pembaruan data produk pada database
        try {
            $product->update([
                'category_id' => $categoryId,
                'product_name' => $productName,
                'product_code' => $productCode,
                'purchase_price' => (float) $purchasePrice,
                'selling_price' => (float) $sellingPrice,
                'initial_stock' => (int) $initialStock,
                'expiration_date' => $expirationDate,
                'notes' => $request->input('notes'),
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => t('product_update_failed'),
            ], 500);
        }

        // Simpan foto baru dan hapus foto lama jika ada perubahan berkas
        if ($file !== null && $photoExtension !== null) {
            $oldPhoto = $product->photo;
            $filename = bin2hex(random_bytes(16)).'.'.$photoExtension;

            $stored = Storage::disk('uploads')->putFileAs('products/'.$product->id, $file, $filename);

            if (! $stored) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('photo_save_failed'),
                ], 500);
            }

            $product->update(['photo' => $filename]);

            // Hapus berkas foto lama jika ada
            if (! empty($oldPhoto) && $oldPhoto !== $filename) {
                Storage::disk('uploads')->delete('products/'.$product->id.'/'.basename($oldPhoto));
            }
        }

        // Mengembalikan respon JSON sukses pembaruan produk
        return response()->json([
            'status' => 'success',
            'message' => t('product_update_success'),
            'product_id' => $product->id,
        ]);
    }

    /**
     * Menghapus produk beserta direktori foto dan riwayat yang terkait.
     */
    public function destroy(Request $request, Product $product)
    {
        // Ambil objek bisnis aktif
        $business = $request->attributes->get('business');

        // Pastikan produk yang akan dihapus milik bisnis aktif
        if ((int) $product->business_id !== (int) $business->id) {
            return response()->json([
                'status' => 'error',
                'message' => t('product_access_denied'),
            ], 404);
        }

        $productId = $product->id;

        // Eksekusi penghapusan produk dari database
        try {
            $product->delete();
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => t('product_delete_failed'),
            ], 500);
        }

        // Hapus direktori penyimpanan foto produk
        Storage::disk('uploads')->deleteDirectory('products/'.$productId);

        // Mengembalikan respon JSON sukses penghapusan produk
        return response()->json([
            'status' => 'success',
            'message' => t('product_delete_success'),
        ]);
    }
}
