<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function set(Request $request)
    {
        $lang = $request->input('language', 'id');

        if (! in_array($lang, ['id', 'en'], true)) {
            $lang = 'id';
        }

        $request->session()->put('language', $lang);

        $redirect = (string) $request->input('redirect', '/');

        if (
            $redirect === ''
            || str_starts_with($redirect, '//')
            || str_contains($redirect, '://')
            || str_contains($redirect, "\n")
            || str_contains($redirect, "\r")
        ) {
            $redirect = '/';
        }

        return redirect()->to($redirect);
    }
}
