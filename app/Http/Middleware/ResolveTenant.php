<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Website;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

class ResolveTenant
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Ambil parameter 'subdomain' dari URL (prefix: s/{subdomain})
        $subdomain = $request->route('subdomain');

        if (!$subdomain) {
            abort(404, 'Toko tidak ditemukan (Parameter URL hilang).');
        }

        // 2. Cari Website di Database
        $website = Website::where('subdomain', $subdomain)->first();

        if (!$website) {
            abort(404, 'Toko tidak ditemukan di database.');
        }

        // 3. MAGIC: Set Default URL Parameter
        // Ini membuat route('store.cart') otomatis menjadi /s/elecjos/cart
        // tanpa perlu kita pass parameter manual di view.
        URL::defaults(['subdomain' => $subdomain]);

        // 4. Inject Data ke Request & View
        $request->merge(['website' => $website]);
        View::share('website', $website);

        return $next($request);
    }
}