<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPackageFeature
{
    public function handle(Request $request, Closure $next, $featureColumn)
    {
        // Tangkap website yang sedang dikelola (Asumsi menggunakan request attribute dari ResolveTenant)
        $website = $request->get('website'); 
        
        // Jika website tidak punya fitur ini
        if (!$website || !$website->hasFeature($featureColumn)) {
            
            // 1. Jika requestnya dari AJAX/API (seperti Webhook/Vue)
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Upgrade ke Paket Pro untuk fitur ini.'], 403);
            }

            // 2. Jika dari Browser biasa
            return redirect()->route('client.dashboard', $website)
                ->with('error', '🔒 Akses Ditolak: Fitur ini eksklusif untuk Paket yang lebih tinggi. Silakan upgrade berlangganan Anda.');
        }

        return $next($request);
    }
}