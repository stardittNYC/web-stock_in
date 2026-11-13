<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Throwable;

/**
 * Controller untuk mengelola kategori produk bisnis.
 */
class CategoryController extends Controller
{
    /**
     * Menyimpan kategori produk baru ke dalam database.
     */
    public function store(Request $request)
    {
        // Ambil objek bisnis yang sedang aktif dari atribut request (middleware)
        $business = $request->attributes->get('business');
        // Tangkap dan bersihkan input nama kategori
        $categoryName = trim((string) $request->input('category_name', ''));

        // Menolak permintaan jika nama kategori kosong
        if ($categoryName === '') {
            return response()->json([
                'status' => 'error',
                'message' => t('category_name_required'),
            ]);
        }

        // Memeriksa duplikasi nama kategori dalam bisnis yang sama (case-insensitive)
        $exists = Category::where('business_id', $business->id)
            ->whereRaw('LOWER(category_name) = LOWER(?)', [$categoryName])
            ->exists();

        // Mengembalikan pesan error jika kategori sudah terdaftar sebelumnya
        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => t('category_exists'),
            ]);
        }

        // Menyimpan data kategori baru ke database
        try {
            $category = Category::create([
                'business_id' => $business->id,
                'category_name' => $categoryName,
            ]);
        } catch (Throwable $e) {
            report($e);

            // Menangani kegagalan saat proses penyimpanan ke database
            return response()->json([
                'status' => 'error',
                'message' => t('category_save_failed'),
            ], 500);
        }

        // Mengembalikan respon sukses beserta data kategori yang baru ditambahkan
        return response()->json([
            'status' => 'success',
            'message' => t('category_save_success'),
            'category' => [
                'id' => $category->id,
                'category_name' => $category->category_name,
            ],
        ]);
    }
}
