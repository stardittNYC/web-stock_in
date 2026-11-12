<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Apply the language stored in the session (limited to id/en).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $lang = $request->session()->get('language', 'id');

        if (! in_array($lang, ['id', 'en'], true)) {
            $lang = 'id';
        }

        App::setLocale($lang);
        View::share('lang', $lang);

        return $next($request);
    }
}
