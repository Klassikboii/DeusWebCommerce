<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Ambil Parameter Website dari URL (karena rutenya /manage/{website}/...)
        $website = $request->route('website');

        // Pastikan kita sedang di dalam route yang punya parameter website
        if (!$website) {
            return $next($request);
        }

        // 2. PENTING: Jangan blokir halaman Billing! (Nanti infinite loop)
        // Kita izinkan user akses route yang namanya mengandung 'billing'
        if ($request->routeIs('client.billing.*')) {
            return $next($request);
        }

        // 3. Cek Langganan Aktif
        $subscription = $website->activeSubscription;

        // KONDISI TERKUNCI:
        // A. Tidak punya subscription sama sekali
        // B. Punya subscription tapi statusnya 'expired'
        // C. Punya subscription active, TAPI tanggalnya sudah lewat (belum dirun scheduler)
        
        $isLocked = false;

        if (!$subscription) {
            $isLocked = true;
        } elseif ($subscription->status !== 'active') {
            $isLocked = true;
        } elseif ($subscription->ends_at && $subscription->ends_at->isPast()) {
            $isLocked = true;
        }

        // 4. JIKA TERKUNCI -> LEMPAR KE BILLING
        if ($isLocked) {
            return redirect()->route('client.billing.index', $website->id)
                             ->with('error', 'Masa Trial/Langganan Anda Habis! Silakan bayar untuk melanjutkan akses.');
        }

        return $next($request);
    }
}