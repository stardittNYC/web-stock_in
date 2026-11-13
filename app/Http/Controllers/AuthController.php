<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

/**
 * Controller untuk mengelola autentikasi pengguna (Login, Register, Logout, dan Hapus Akun).
 */
class AuthController extends Controller
{
    /**
     * Menampilkan halaman formulir login.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Menampilkan halaman formulir registrasi akun baru.
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Memproses autentikasi dan login pengguna.
     */
    public function login(Request $request)
    {
        // Menangkap dan membersihkan input email serta password dari form
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');

        // Memvalidasi agar input email dan password tidak kosong
        if ($email === '' || $password === '') {
            return response()->json([
                'status' => 'error',
                'message' => t('email_password_required'),
            ], 400);
        }

        // Memverifikasi kredensial pengguna menggunakan mekanisme autentikasi Laravel
        if (! Auth::attempt(['email' => $email, 'password' => $password])) {
            return response()->json([
                'status' => 'error',
                'message' => t('invalid_credentials'),
            ], 401);
        }

        // Regenerasi ID sesi demi keamanan dari serangan session fixation
        $request->session()->regenerate();

        // Mengembalikan respon JSON sukses login
        return response()->json([
            'status' => 'success',
            'message' => t('login_success'),
        ]);
    }

    /**
     * Memproses pendaftaran (registrasi) akun pengguna baru.
     */
    public function register(Request $request)
    {
        // Menangkap dan membersihkan input nama, email, serta password
        $name = trim((string) $request->input('name', ''));
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');

        // Memvalidasi agar seluruh kolom formulir terisi
        if ($name === '' || $email === '' || $password === '') {
            return response()->json([
                'status' => 'error',
                'message' => t('all_fields_required'),
            ], 400);
        }

        // Memvalidasi kesesuaian format alamat email
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'status' => 'error',
                'message' => t('invalid_email_format'),
            ], 400);
        }

        // Validasi panjang password harus tepat 6 karakter
        if (mb_strlen($password) !== 6) {
            return response()->json([
                'status' => 'error',
                'message' => t('password_exact_6'),
            ], 400);
        }

        // Memeriksa ketersediaan email agar tidak terjadi duplikasi akun
        if (User::where('email', $email)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => t('email_already_used'),
            ], 400);
        }

        // Menyimpan data akun pengguna baru ke database
        try {
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ]);
        } catch (Throwable $e) {
            report($e);

            // Menangani kegagalan saat proses penyimpanan akun
            return response()->json([
                'status' => 'error',
                'message' => t('account_create_failed'),
            ], 500);
        }

        // Mengembalikan respon JSON sukses pendaftaran akun
        return response()->json([
            'status' => 'success',
            'message' => t('account_created'),
        ]);
    }

    /**
     * Mengakhiri sesi pengguna (logout) dan mengarahkan kembali ke halaman login.
     */
    public function logout(Request $request)
    {
        // Keluarkan pengguna dari sistem autentikasi
        Auth::logout();

        // Bersihkan dan batalkan seluruh data sesi aktif
        $request->session()->invalidate();
        // Buat ulang token CSRF baru untuk keamanan
        $request->session()->regenerateToken();

        // Alihkan pengguna ke halaman login
        return redirect()->route('login');
    }

    /**
     * Menghapus akun pengguna yang sedang login beserta seluruh data terkait.
     */
    public function deleteAccount(Request $request)
    {
        // Menghapus data akun pengguna dari database
        try {
            $request->user()->delete();
        } catch (Throwable $e) {
            report($e);

            // Mengembalikan respon gagal jika terjadi kendala saat penghapusan
            return response()->json([
                'status' => 'error',
                'message' => t('account_delete_failed'),
            ]);
        }

        // Hapus sesi pengguna setelah akun berhasil dihapus
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Mengembalikan respon sukses penghapusan akun
        return response()->json([
            'status' => 'success',
            'message' => t('account_delete_success'),
        ]);
    }
}
