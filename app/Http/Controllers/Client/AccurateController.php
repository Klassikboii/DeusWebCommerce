<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Models\AccurateIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AccurateController extends Controller
{
    // 1. Mengarahkan User ke Halaman Login Accurate
    public function redirect(Website $website)
    {
        // Pastikan user berhak mengakses website ini
        $this->authorize('update', $website);

        $clientId = config('services.accurate.client_id');
        $redirectUri = config('services.accurate.redirect_uri');
        
        // Kita simpan ID website di parameter 'state' agar saat callback kita tahu ini milik website mana
        $state = $website->id;

        $url = "https://account.accurate.id/oauth/authorize?client_id={$clientId}&response_type=code&redirect_uri={$redirectUri}&scope=item_view item_save sales_invoice_save customer_save&state={$state}";

        return redirect()->away($url);
    }

    // 2. Menangkap Respon dari Accurate setelah User Login
    public function callback(Request $request)
    {
        // 'state' berisi ID Website yang kita kirim di fungsi redirect
        $websiteId = $request->state;
        $website = Website::findOrFail($websiteId);

        // Jika user membatalkan atau terjadi error
        if ($request->has('error')) {
            return redirect()->route('client.settings.index', $website)->with('error', 'Gagal menghubungkan ke Accurate: ' . $request->error_description);
        }

        // Tukar Authorization Code dengan Access Token
        $response = Http::asForm()->withBasicAuth(
            config('services.accurate.client_id'), 
            config('services.accurate.client_secret')
        )->post('https://account.accurate.id/oauth/token', [
            'grant_type' => 'authorization_code',
            'code' => $request->code,
            'redirect_uri' => config('services.accurate.redirect_uri'),
        ]);

        if ($response->successful()) {
            $data = $response->json();

            // Simpan atau update token di database
            AccurateIntegration::updateOrCreate(
                ['website_id' => $website->id],
                [
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'],
                    // Token accurate biasanya valid untuk waktu tertentu (misal 30 hari)
                    'token_expires_at' => now()->addSeconds($data['expires_in']),
                ]
            );

            return redirect()->route('client.settings.index', $website)->with('success', 'Berhasil terhubung dengan Accurate Online!');
        }

        Log::error('Accurate Token Error: ', $response->json());
        return redirect()->route('client.settings.index', $website)->with('error', 'Gagal menukar token dengan Accurate.');
    }
}