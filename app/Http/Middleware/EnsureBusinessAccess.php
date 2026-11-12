<?php

namespace App\Http\Middleware;

use App\Models\Business;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class EnsureBusinessAccess
{
    /**
     * Resolve the active business from the request and make sure it belongs
     * to the authenticated user (mirrors includes/auth_business.php).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $businessId = $request->input('business_id');

        if ($businessId === null || $businessId === '') {
            $businessId = $request->route('business_id');
        }

        $business = null;

        if (is_numeric($businessId) && (int) $businessId > 0) {
            $business = Business::where('id', (int) $businessId)
                ->where('user_id', $request->user()->id)
                ->first();
        }

        if (! $business) {
            return redirect()->route('businesses.index');
        }

        $request->attributes->set('business', $business);
        View::share('business', $business);
        View::share('business_id', $business->id);

        return $next($request);
    }
}
