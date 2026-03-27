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

        $url = "https://account.accurate.id/oauth/authorize?client_id={$clientId}&response_type=code&redirect_uri={$redirectUri}&scope=item_view item_save sales_invoice_save item_adjustment_save customer_view sales_invoice_view sales_receipt_save item_adjustment_view customer_save&state={$state}";

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

    // 3. Menyimpan ID Database yang dipilih user
    public function saveDatabase(Request $request, Website $website)
    {
        $this->authorize('update', $website);
        
        $request->validate([
            'accurate_database_id' => 'required|string'
        ]);

        if ($website->accurateIntegration) {
            $website->accurateIntegration->update([
                'accurate_database_id' => $request->accurate_database_id
            ]);
        }

        return redirect()->back()->with('success', 'Database Accurate berhasil dihubungkan ke toko ini!');
    }
    // 4. Memutuskan Koneksi Accurate (Ganti Akun / Database)
    public function disconnect(Website $website)
    {
        $this->authorize('update', $website);

        // Hapus data integrasi dari database lokal
        if ($website->accurateIntegration) {
            $website->accurateIntegration->delete();
        }

        return redirect()->back()->with('success', 'Koneksi Accurate berhasil diputuskan. Silakan hubungkan kembali dengan akun atau database yang baru.');
    }
    // ... (Fungsi redirect, callback, saveDatabase, disconnect biarkan seperti semula) ...

    // 5. Mencari Produk yang Belum Memiliki Gambar (Langkah 1 dari Sistem Cicilan)
    // 🚨 UBAH PARAMETER MENJADI $websiteId
    public function getMissingImages(Request $request, $websiteId) 
    {
        // Sekarang $websiteId PASTI berisi angka 1 dari URL!
        
        $productIds = \Illuminate\Support\Facades\DB::table('products')
            ->where('website_id', $websiteId) // Gunakan $websiteId di sini
            ->where(function($query) {
                $query->whereNull('image')
                      ->orWhere('image', '');
            })
            ->whereNotNull('sku')
            ->where('sku', '!=', '')
            ->pluck('id');

        return response()->json([
            'status' => 'success',
            'data' => $productIds->toArray() 
        ]);
    }

    // 6. Mengunduh Gambar secara Batch / Cicilan (Langkah 2)
   public function syncImagesBatch(Request $request, $websiteId)
    {
        $website = \App\Models\Website::findOrFail($websiteId);
        $ids = $request->input('ids', []);
        
        // 🚨 WADAH LAPORAN CCTV
        $debugLog = []; 

        if (empty($ids)) {
            return response()->json(['status' => 'success', 'pesan' => 'ID kosong']);
        }

        $integration = $website->accurateIntegration;
        if (!$integration || !$integration->access_token) {
            return response()->json(['error' => 'Token Accurate tidak valid'], 400);
        }

        // Buka Sesi Accurate
        $sessionResponse = Http::withoutRedirecting()
            ->withToken($integration->access_token)
            ->get('https://account.accurate.id/api/open-db.do', [
                'id' => $integration->accurate_database_id
            ]);

        if ($sessionResponse->status() === 302 || $sessionResponse->status() === 401) {
            return response()->json(['error' => 'Token Expired/Unauthorized'], 401);
        }

        $sessionData = $sessionResponse->json();
        $host = $sessionData['host'] ?? null;
        $sessionToken = $sessionData['session'] ?? null;

        $products = \App\Models\Product::whereIn('id', $ids)->get();

        foreach ($products as $product) {
            $debugLog[$product->sku] = 'Memulai... ';

            $detailResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $integration->access_token,
                'X-Session-ID' => $sessionToken
            ])->get($host . '/api/item/detail.do', [
                'no' => $product->sku
            ]);

            if ($detailResponse->successful() && $detailResponse->json('d')) {
                $itemData = $detailResponse->json('d');
                $debugLog[$product->sku] .= 'API Detail OK. ';

                // Cek array imageList
                if (isset($itemData['imageList']) && count($itemData['imageList']) > 0) {
                    $imageUrl = $itemData['imageList'][0]['url'];
                    $debugLog[$product->sku] .= 'URL Ketemu: ' . $imageUrl . ' | ';

                    // 🚨 PERBAIKAN: Gunakan Header Auth untuk mengunduh gambar!
                    $imageResponse = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $integration->access_token,
                        'X-Session-ID' => $sessionToken
                    ])->get($imageUrl);

                    if ($imageResponse->successful()) {
                        $imageContent = $imageResponse->body();
                        
                        $originalName = basename(parse_url($imageUrl, PHP_URL_PATH));
                        $fileName = $originalName ?: \Illuminate\Support\Str::slug($product->name) . '.jpg';
                        $path = 'products/' . $website->id . '/' . time() . '-' . $fileName;

                        // Simpan file
                        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $imageContent);

                        // 🚨 PERBAIKAN: Gunakan DB Murni untuk menyimpan nama file agar tembus filter
                        \Illuminate\Support\Facades\DB::table('products')
                            ->where('id', $product->id)
                            ->update(['image' => $path]);

                        $debugLog[$product->sku] .= 'SUKSES DISIMPAN: ' . $path;
                    } else {
                        $debugLog[$product->sku] .= 'GAGAL UNDUH GAMBAR (Status ' . $imageResponse->status() . '). ';
                    }
                } else {
                    $debugLog[$product->sku] .= 'ARRAY imageList KOSONG/TIDAK ADA! Isi keys: ' . implode(',', array_keys($itemData));
                }
            } else {
                $debugLog[$product->sku] .= 'GAGAL API Detail (Status ' . $detailResponse->status() . ').';
            }
        }

        return response()->json([
            'status' => 'success',
            'debug_log' => $debugLog
        ]);
    }
}