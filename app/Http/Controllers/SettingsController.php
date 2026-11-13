<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Controller untuk mengelola pengaturan profil pengguna, preferensi bisnis, dan pembaruan kata sandi.
 */
class SettingsController extends Controller
{
    /**
     * Menampilkan halaman formulir pengaturan akun dan informasi bisnis.
     */
    public function edit(Request $request)
    {
        // Ambil data profil pengguna yang sedang login
        $userProfile = [
            'name' => $request->user()->name,
            'email' => $request->user()->email,
        ];

        // Tampilkan view pengaturan
        return view('dashboard.settings', compact('userProfile'));
    }

    /**
     * Memproses penyimpanan pembaruan profil pengguna, informasi bisnis, logo, dan kata sandi.
     */
    public function update(Request $request)
    {
        // Ambil objek bisnis aktif dan objek pengguna saat ini
        $business = $request->attributes->get('business');
        $user = $request->user();

        // Tangkap input nama bisnis, nama pengguna, dan catatan
        $businessName = trim((string) $request->input('business_name', ''));
        $name = trim((string) $request->input('name', ''));
        $notes = trim((string) $request->input('notes', ''));

        // Tangkap input kolom kata sandi
        $currentPassword = (string) $request->input('current_password', '');
        $newPassword = (string) $request->input('new_password', '');
        $confirmPassword = (string) $request->input('confirm_password', '');

        // Validasi agar nama bisnis dan nama pengguna tidak kosong
        if ($businessName === '' || $name === '') {
            return response()->json([
                'status' => 'error',
                'message' => t('business_name_user_required'),
            ]);
        }

        $updatePassword = false;

        // Validasi pembaruan kata sandi jika ada salah satu kolom password yang diisi
        if ($currentPassword !== '' || $newPassword !== '' || $confirmPassword !== '') {
            if ($currentPassword === '') {
                return response()->json(['status' => 'error', 'message' => t('current_password_req')]);
            }

            if ($newPassword === '' || $confirmPassword === '') {
                return response()->json(['status' => 'error', 'message' => t('new_password_req')]);
            }

            // Validasi panjang kata sandi baru harus tepat 6 karakter
            if (mb_strlen($newPassword) !== 6) {
                return response()->json(['status' => 'error', 'message' => t('password_exact_6')]);
            }

            // Memastikan kecocokan password baru dengan konfirmasi password
            if ($newPassword !== $confirmPassword) {
                return response()->json(['status' => 'error', 'message' => t('password_mismatch')]);
            }

            // Memverifikasi apakah password lama yang dimasukkan sesuai
            if (! Hash::check($currentPassword, $user->password)) {
                return response()->json(['status' => 'error', 'message' => t('wrong_current_password')]);
            }

            $updatePassword = true;
        }

        // Menangani validasi berkas unggahan logo/foto bisnis jika ada
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

            // Batas maksimal ukuran foto logo 2MB
            if ($file->getSize() > 2 * 1024 * 1024) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('photo_max_size'),
                ], 400);
            }

            // Memverifikasi apakah berkas adalah gambar valid
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

        // Eksekusi pembaruan data pengguna dan bisnis ke database
        try {
            $user->update(['name' => $name]);

            $business->update([
                'business_name' => $businessName,
                'notes' => $notes,
            ]);

            // Perbarui password jika diminta
            if ($updatePassword) {
                $user->update(['password' => $newPassword]);
            }
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => t('settings_update_failed'),
            ]);
        }

        // Simpan foto logo baru dan bersihkan logo lama jika ada unggahan
        if ($file !== null && $photoExtension !== null) {
            $oldPhoto = $business->photo;
            $newFileName = 'logo_'.time().'.'.$photoExtension;

            $stored = Storage::disk('uploads')->putFileAs('businesses/'.$business->id, $file, $newFileName);
            if (! $stored) {
                return response()->json([
                    'status' => 'error',
                    'message' => t('photo_save_failed'),
                ], 500);
            }

            $business->update(['photo' => $newFileName]);

            // Hapus berkas foto logo lama dari direktori penyimpanan
            if (! empty($oldPhoto) && $oldPhoto !== $newFileName) {
                Storage::disk('uploads')->delete('businesses/'.$business->id.'/'.basename($oldPhoto));
            }
        }

        // Mengembalikan respon JSON sukses pembaruan pengaturan
        return response()->json([
            'status' => 'success',
            'message' => t('settings_update_success'),
        ]);
    }
}
