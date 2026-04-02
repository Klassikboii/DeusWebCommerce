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
    // 1. Ambil URL/Host yang sedang diketik pengunjung
    $host = $request->getHost();
    
    // 2. Ambil domain utama aplikasi kita dari .env
    $mainDomain = parse_url(config('app.url'), PHP_URL_HOST);

    // (Keamanan Tambahan) Jika pengunjung entah bagaimana mengakses domain utama tapi nyasar ke sini
    if ($host === $mainDomain || $host === 'localhost' || $host === '127.0.0.1') {
        return $next($request);
    }

    // 3. Logika Detektif: Subdomain atau Custom Domain?
    if (str_ends_with($host, '.' . $mainDomain)) {
        // Skenario A: Jika berakhiran domain utama -> Ini SUBDOMAIN (misal: elecjos.deusserver.test)
        // Kita potong nama domain utamanya, sisakan 'elecjos' saja
        $subdomainOnly = str_replace('.' . $mainDomain, '', $host);
        $website = Website::where('subdomain', $subdomainOnly)->first();
    } else {
        // Skenario B: Jika tidak ada hubungannya dengan domain utama -> Ini CUSTOM DOMAIN (misal: joseelectronics.com)
        $website = Website::where('custom_domain', $host)->first();
    }

    // 4. Lemparkan 404 jika toko tidak ada atau domain salah ketik
    if (!$website) {
        abort(404, 'Toko tidak ditemukan atau domain belum terdaftar di sistem kami.');
    }

    // 🚨 PERHATIKAN: Kita MENGHAPUS URL::defaults(['any_domain' => ...])
    // karena kita sudah tidak butuh parameter URL lagi!

    // 5. Suntikkan Data Website ke Request dan View
    $request->merge(['website' => $website]);
    View::share('website', $website);

    return $next($request);
}
}