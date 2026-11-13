<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Controller untuk mengelola data bisnis pengguna (daftar bisnis, tambah bisnis baru, dan hapus bisnis).
 */
class BusinessController extends Controller
{
    /**
     * Menampilkan daftar bisnis milik pengguna yang sedang login.
     */
    public function index(Request $request)
    {
        // Ambil daftar bisnis pengguna dari database terurut dari yang terbaru
        $businesses = $request->user()
            ->businesses()
            ->orderByDesc('id')
            ->get();

        // Tampilkan view halaman daftar bisnis
        return view('businesses', compact('businesses'));
    }

    /**
     * Menampilkan formulir pendaftaran bisnis baru.
     */
    public function create()
    {
        return view('businesses.create');
    }

    /**
     * Menyimpan data bisnis baru ke database beserta foto/logo yang diunggah.
     */
    public function store(Request $request)
    {
        // Menangkap dan membersihkan input nama bisnis, mata uang, serta catatan
        $businessName = trim((string) $request->input('business_name', ''));
        $currency = trim((string) $request->input('currency', 'IDR'));
        $notes = trim((string) $request->input('notes', ''));

        // Memvalidasi agar nama bisnis tidak kosong
        if ($businessName === '') {
            return response()->json([
                'status' => 'error',
                'message' => t('business_name_user_required'),
            ], 400);
        }

        // Menangani validasi berkas foto profil / logo bisnis jika ada
        $file = $request->file('photo');
        $photoExtension = null;

        if ($file !== null) {
            // Memeriksa apakah berkas berhasil diunggah tanpa error
            if (! $file->isValid()) {
                $isSizeError = in_array($file->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true);

                return response()->json([
                    'status' => 'error',
                    'message' => $isSizeError ? t('photo_max_size') : t('photo_upload_failed'),
                ], 400);
            }

            // Memvalidasi ukuran berkas tidak melebihi batas 5MB
            if ($file->getSize() > 5 * 1024 * 1024) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('photo_max_size'),
                ], 400);
            }

            // Memverifikasi apakah berkas benar-benar gambar valid
            $imageInfo = @getimagesize($file->getRealPath());
            if ($imageInfo === false) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('invalid_image_file'),
                ], 400);
            }

            // Daftar format MIME tipe gambar yang diperbolehkan
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

            // Ambil ekstensi berkas yang valid
            $photoExtension = $mimeTypes[$mime];
        }

        // Menyimpan record bisnis baru ke database
        try {
            $business = $request->user()->businesses()->create([
                'business_name' => $businessName,
                'currency' => $currency !== '' ? $currency : 'IDR',
                'notes' => $notes,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => t('business_create_failed'),
            ], 500);
        }

        // Simpan file foto ke penyimpanan disk jika berkas diunggah
        if ($file !== null && $photoExtension !== null) {
            $photoName = time().'.'.$photoExtension;

            $stored = Storage::disk('uploads')->putFileAs(
                'businesses/'.$business->id,
                $file,
                $photoName
            );

            // Perbarui nama file foto pada data bisnis di database
            if ($stored) {
                $business->update(['photo' => $photoName]);
            }
        }

        // Mengembalikan respon JSON sukses pembuatan bisnis
        return response()->json([
            'status' => 'success',
            'message' => t('business_create_success'),
        ]);
    }

    /**
     * Menghapus bisnis beserta berkas foto yang terkait.
     */
    public function destroy(Request $request)
    {
        // Menangkap ID bisnis yang akan dihapus
        $businessId = (int) $request->input('business_id', 0);

        // Memvalidasi ID bisnis agar valid
        if ($businessId <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => t('invalid_business'),
            ]);
        }

        // Menghapus data bisnis milik pengguna aktif dari database
        try {
            $deleted = $request->user()
                ->businesses()
                ->where('id', $businessId)
                ->delete();
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => t('business_delete_failed'),
            ]);
        }

        // Hapus direktori penyimpanan berkas jika data bisnis berhasil dihapus
        if ($deleted > 0) {
            Storage::disk('uploads')->deleteDirectory('businesses/'.$businessId);

            return response()->json([
                'status' => 'success',
                'message' => t('business_delete_success'),
            ]);
        }

        // Tolak jika pengguna tidak memiliki wewenang atas bisnis ini
        return response()->json([
            'status' => 'error',
            'message' => t('business_unauthorized'),
        ]);
    }
}
