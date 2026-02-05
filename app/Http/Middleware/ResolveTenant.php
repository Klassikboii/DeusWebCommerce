<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Website;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        // 1. Bypass untuk Admin Panel (127.0.0.1 atau localhost)
        if ($host === '127.0.0.1' || $host === 'localhost') {
            return $next($request);
        }

        $website = null;

        // 2. LOGIKA BARU: Cek Subdomain Localhost
        // Apakah host diakhiri dengan '.localhost'?
        if (str_ends_with($host, '.localhost')) {
            // Ambil nama depannya. Contoh: 'sepatubudi.localhost' -> 'sepatubudi'
            $subdomain = str_replace('.localhost', '', $host);
            $website = Website::where('subdomain', $subdomain)->first();
        }
        // 3. Cek Custom Domain (Logic lama)
        else {
            $website = Website::where('custom_domain', $host)->first();
        }

        if (!$website) {
            abort(404, 'Toko tidak ditemukan.');
        }

        $request->attributes->add(['website' => $website]);
        
        $response = $next($request);
        $response->headers->remove('X-Frame-Options');

        return $response;
    }
}