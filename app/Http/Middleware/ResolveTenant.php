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
    
    // --- 1. BYPASS DOMAIN UTAMA & IP LOKAL ---
    // Daftar host yang TIDAK BOLEH dianggap sebagai toko
    $ignoredHosts = [
        'localhost',
        '127.0.0.1',
        parse_url(env('APP_URL'), PHP_URL_HOST), // Ambil domain dari .env
    ];

    // Jika yang diakses adalah admin/localhost, langsung lewatkan!
    if (in_array($host, $ignoredHosts)) {
        return $next($request);
    }

    // ... (Lanjut ke logika pencarian website di bawah ini) ...
    
    $appUrl = env('APP_URL');
    // Bersihkan port
    $host = preg_replace('/:\d+$/', '', $host);
    $appUrl = preg_replace('/:\d+$/', '', $appUrl);
    
    $website = null;

    // KASUS 1: Subdomain
    if (str_ends_with($host, $appUrl)) {
        $subdomain = str_replace('.' . $appUrl, '', $host);
        if ($subdomain !== $host && $subdomain !== 'www') {
            $website = Website::where('subdomain', $subdomain)->first();
        }
    } 
    // KASUS 2: Custom Domain
    else {
        $website = Website::where('custom_domain', $host)->first();
    }

    // Jika Website tidak ditemukan, tampilkan 404
    if (!$website) {
        abort(404, 'Toko tidak ditemukan.');
    }

    $request->attributes->add(['website' => $website]);
    
    // Hapus header X-Frame-Options agar editor jalan
    $response = $next($request);
    $response->headers->remove('X-Frame-Options');

    return $response;
}
}