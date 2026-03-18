<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Website;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

class ResolveTenant
{public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost(); // Menangkap domain (misal: tokobudi.com atau budi.elecios.id)
        
        // Ambil domain utama aplikasi Anda dari file .env (misal: elecios.id)
        $appDomain = parse_url(config('app.url'), PHP_URL_HOST); 
        // ========================================================
        // 🚨 ALAT PELACAK (DEBUGGER) - Hapus nanti kalau sudah jalan
        // ========================================================
        // if ($host !== 'localhost' && $host !== '127.0.0.1') {
            
        //     // Coba cari secara manual untuk melihat hasilnya
        //     $cleanHost = str_replace('www.', '', $host);
        //     $subdomainTarget = str_replace('.' . $appDomain, '', $host);

        //     dd([
        //         '1_URL_Yang_Diketik' => $request->fullUrl(),
        //         '2_Host_Terdeteksi' => $host,
        //         '3_App_Domain_Utama' => $appDomain,
        //         '4_Parameter_Route' => $request->route('subdomain'),
        //         '5_Hasil_Cari_Custom_Domain' => \App\Models\Website::where('custom_domain', $cleanHost)->orWhere('custom_domain', $host)->first() ? 'KETEMU' : 'KOSONG',
        //         '6_Hasil_Cari_Subdomain' => \App\Models\Website::where('subdomain', $subdomainTarget)->first() ? 'KETEMU' : 'KOSONG',
        //     ]);
        // }
        // // ========================================================

        // 1. KEAMANAN: Abaikan tenant jika yang diakses adalah domain utama
        // Agar halaman Login, Admin, dan Dashboard klien tidak error
        if ($host === $appDomain || $host === 'www.' . $appDomain || $host === 'localhost' || $host === '127.0.0.1') {
            return $next($request);
        }

        // 2. CARI TOKO DI DATABASE
        if (str_ends_with($host, '.' . $appDomain)) {
            // Skenario A: Mengakses via Subdomain (misal: budi.elecios.id)
            $subdomain = str_replace('.' . $appDomain, '', $host);
            $website = Website::where('subdomain', $subdomain)->first();
        } else {
            // Skenario B: Mengakses via Custom Domain Luar (misal: www.tokobudi.com)
            // Hilangkan 'www.' jika ada agar pencarian lebih akurat
            $cleanHost = str_replace('www.', '', $host);
            $website = Website::where('custom_domain', $cleanHost)->orWhere('custom_domain', $host)->first();
        }

        if (!$website) {
            abort(404, 'Toko tidak ditemukan atau domain belum dihubungkan ke sistem kami.');
        }

       // ... (kode pengecekan database di atasnya biarkan sama) ...

        // 3. MAGIC: Set Default URL Parameter
        // 🚨 PASTIKAN MENGGUNAKAN 'subdomain'
        \Illuminate\Support\Facades\URL::defaults(['subdomain' => $host]);

        // 4. Inject Data ke Request & View
        $request->merge(['website' => $website]);
        \Illuminate\Support\Facades\View::share('website', $website);

        

        if (!$website) {
            // Beri pesan spesifik agar kita tahu 404-nya dari Middleware
            abort(404, 'Toko tidak ditemukan atau domain belum terhubung.');
        }

        // 1. URL GENERATOR DEFAULT
        \Illuminate\Support\Facades\URL::defaults(['subdomain' => $host]);

        // 🚨 2. THE JEDI MIND TRICK (Memperbaiki 404 Storefront)
        // Kita "bohongi" Controller lama Anda. Meskipun URL-nya joseelectronics.com,
        // kita paksa ganti parameternya menjadi 'elecjos' agar Controller tidak error!
        if ($request->route()) {
            $request->route()->setParameter('subdomain', $website->subdomain);
        }

        // 3. Inject Data
        $request->merge(['website' => $website]);
        \Illuminate\Support\Facades\View::share('website', $website);

        return $next($request);
    }
}