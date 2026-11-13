<?php

namespace App\Http\Middleware;

use App\Models\Business;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk memastikan pengguna memiliki hak akses sah ke bisnis aktif.
 * Mirip dengan implementasi includes/auth_business.php pada versi prosedural.
 */
class EnsureBusinessAccess
{
    /**
     * Menangani permintaan masuk, memvalidasi kepemilikan bisnis, dan membagikan data bisnis ke view.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ambil ID bisnis dari parameter input query atau route parameter
        $businessId = $request->input('business_id');

        if ($businessId === null || $businessId === '') {
            $businessId = $request->route('business_id');
        }

        $business = null;

        // Validasi dan cari bisnis berdasarkan ID dan ID pengguna yang sedang login
        if (is_numeric($businessId) && (int) $businessId > 0) {
            $business = Business::where('id', (int) $businessId)
                ->where('user_id', $request->user()->id)
                ->first();
        }

        // Alihkan kembali ke daftar bisnis jika bisnis tidak ditemukan atau pengguna tidak memiliki akses
        if (! $business) {
            return redirect()->route('businesses.index');
        }

        // Simpan objek bisnis aktif ke atribut request agar dapat diakses oleh Controller
        $request->attributes->set('business', $business);

        // Bagikan variabel objek bisnis dan ID bisnis secara global ke seluruh template Blade
        View::share('business', $business);
        View::share('business_id', $business->id);

        // Lanjutkan request ke tahapan berikutnya
        return $next($request);
    }
}
