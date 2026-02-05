<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class CheckSubscription
{
   public function handle(Request $request, Closure $next)
    {
        // 1. Ambil Parameter Website dari URL
        // Ini bisa berupa Object (jika binding jalan) atau String ID (jika belum)
        $websiteParam = $request->route('website');

        // 2. Logika Penyelamat: Pastikan kita punya OBJECT Website
        if ($websiteParam instanceof \App\Models\Website) {
            // Jika sudah berupa object, pakai langsung
            $website = $websiteParam;
        } else {
            // Jika masih berupa String ID (angka), cari di database
            $website = \App\Models\Website::find($websiteParam);
        }

        // Jika website tidak ditemukan (ID ngawur), return 404
        if (!$website) {
            abort(404);
        }

        // 3. PENTING: Jangan blokir halaman Billing! (Nanti infinite loop/gak bisa bayar)
        if ($request->routeIs('client.billing.*')) {
            return $next($request);
        }

        // 4. Cek Subscription
        // Sekarang aman karena $website sudah pasti Object
        $subscription = $website->activeSubscription;

        // Jika tidak ada subscription atau sudah expired
        if (!$subscription || ($subscription->ends_at && $subscription->ends_at->isPast())) {
            
            // Redirect ke halaman billing dengan pesan error
            return redirect()->route('client.billing.index', $website->id)
                ->with('error', 'Masa aktif paket Anda telah habis. Silakan perpanjang untuk melanjutkan.');
        }

        return $next($request);
    }
}