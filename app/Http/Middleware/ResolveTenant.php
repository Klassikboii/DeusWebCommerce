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
    // 1. Ambil Host/Domain yang sedang diakses (misal: joseelectronics.com atau elecjos.deusserver.ashop.asia)
    $host = $request->getHost();
    $mainDomain = parse_url(config('app.url'), PHP_URL_HOST);

    // 2. Logika pencarian Website
    if (str_ends_with($host, $mainDomain)) {
        // Jika host berakhiran domain utama (ini adalah SUBDOMAIN)
        // Ambil bagian depannya saja (misal: 'elecjos' dari 'elecjos.deusserver.ashop.asia')
        $subdomainOnly = str_replace('.' . $mainDomain, '', $host);
        $website = Website::where('subdomain', $subdomainOnly)->first();
    } else {
        // Jika host TIDAK berakhiran domain utama (ini adalah CUSTOM DOMAIN)
        $website = Website::where('custom_domain', $host)->first();
    }

    if (!$website) {
        abort(404, 'Toko tidak ditemukan.');
    }

    // 3. Set Default URL Parameter agar helper route() tetap bekerja
    URL::defaults(['any_domain' => $host]);

    // 4. Inject Data
    $request->merge(['website' => $website]);
    View::share('website', $website);

    return $next($request);
}
}