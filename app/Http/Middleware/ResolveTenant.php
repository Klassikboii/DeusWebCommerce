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

        // 1. BYPASS ADMIN & LOCALHOST (PENTING!)
        // Abaikan request jika datang dari IP lokal atau localhost
        if ($host === '127.0.0.1' || $host === 'localhost') {
            return $next($request);
        }

        $website = null;

        // 2. LOGIKA SUBDOMAIN LOCALHOST (Agar preview jalan)
        if (str_ends_with($host, '.localhost')) {
            $subdomain = str_replace('.localhost', '', $host);
            $website = Website::where('subdomain', $subdomain)->first();
        } 
        // 3. LOGIKA CUSTOM DOMAIN
        else {
            $website = Website::where('custom_domain', $host)->first();
        }

        // Jika website tidak ditemukan
        if (!$website) {
            abort(404, 'Toko tidak ditemukan.');
        }

        // Simpan data website ke request agar bisa dipakai di Controller
        $request->attributes->add(['website' => $website]);
        
        // Hapus X-Frame-Options agar Editor Website bisa jalan
        $response = $next($request);
        $response->headers->remove('X-Frame-Options');

        return $response;
    }
}