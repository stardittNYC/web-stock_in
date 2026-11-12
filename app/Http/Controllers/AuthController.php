<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');

        if ($email === '' || $password === '') {
            return response()->json([
                'status' => 'error',
                'message' => t('email_password_required'),
            ], 400);
        }

        if (! Auth::attempt(['email' => $email, 'password' => $password])) {
            return response()->json([
                'status' => 'error',
                'message' => t('invalid_credentials'),
            ], 401);
        }

        $request->session()->regenerate();

        return response()->json([
            'status' => 'success',
            'message' => t('login_success'),
        ]);
    }

    public function register(Request $request)
    {
        $name = trim((string) $request->input('name', ''));
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');

        if ($name === '' || $email === '' || $password === '') {
            return response()->json([
                'status' => 'error',
                'message' => t('all_fields_required'),
            ], 400);
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'status' => 'error',
                'message' => t('invalid_email_format'),
            ], 400);
        }

        if (mb_strlen($password) !== 6) {
            return response()->json([
                'status' => 'error',
                'message' => t('password_exact_6'),
            ], 400);
        }

        if (User::where('email', $email)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => t('email_already_used'),
            ], 400);
        }

        try {
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => t('account_create_failed'),
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => t('account_created'),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function deleteAccount(Request $request)
    {
        try {
            $request->user()->delete();
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => t('account_delete_failed'),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'status' => 'success',
            'message' => t('account_delete_success'),
        ]);
    }
}
