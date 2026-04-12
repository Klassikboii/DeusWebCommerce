<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPackageFeature
{
   public function handle(Request $request, Closure $next, $feature)
{
    // 1. Ambil parameter website dari route
    $website = $request->route('website');

    // 2. Jika website tidak ditemukan (null)
    if (!$website) {
        // Coba ambil ID dari request jika di rute tidak ada (fallback)
        // Jika masih tidak ada, lempar ke halaman 404 atau home
        return redirect()->route('home')->with('error', 'Konteks website tidak ditemukan.');
    }

    // 3. Jika $website ternyata hanya ID (string) dan bukan Objek Model
    // (Kadang terjadi jika Middleware berjalan sangat awal)
    if (!is_object($website)) {
        $website = \App\Models\Website::find($website);
    }

    // 4. Baru cek fiturnya
    if (!$website || !$website->hasFeature($feature)) {
        // Gunakan ID secara aman
        $websiteId = is_object($website) ? $website->id : $website;
        
        return redirect()->route('client.dashboard', ['website' => $websiteId])
            ->with('error', 'Fitur ini tidak tersedia di paket Anda saat ini.');
    }

    return $next($request);
}
}