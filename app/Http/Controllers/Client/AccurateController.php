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
public function syncImagesBatch(Request $request, $websiteId)
    {
        try {
            $website = \App\Models\Website::findOrFail($websiteId);
            $ids = $request->input('ids', []);
            $debugLog = [];

            if (empty($ids)) {
                return response()->json(['status' => 'success']);
            }

            $integration = $website->accurateIntegration;
            if (!$integration || !$integration->access_token) {
                return response()->json(['error' => 'Token Accurate tidak valid'], 400);
            }

            // 1. Buka Pintu Accurate (Minta Session ID)
            $sessionResponse = \Illuminate\Support\Facades\Http::withoutRedirecting()
                ->withToken($integration->access_token)
                ->get('https://account.accurate.id/api/open-db.do', [
                    'id' => $integration->accurate_database_id
                ]);

            if ($sessionResponse->status() === 302 || $sessionResponse->status() === 401) {
                throw new \Exception("Sesi ditolak. Silakan Putuskan Koneksi dan Hubungkan Ulang Accurate.");
            }

            $sessionData = $sessionResponse->json();
            $host = $sessionData['host'] ?? null;
            $sessionToken = $sessionData['session'] ?? null;

            if (!$host || !$sessionToken) {
                // Bongkar pesan asli jika Accurate menolak (misal: Maintenance)
                throw new \Exception("Akses Database Ditolak: " . $sessionResponse->body());
            }

            // 2. Ambil produk dari database kita
            $products = \Illuminate\Support\Facades\DB::table('products')
                ->where('website_id', $websiteId)
                ->whereIn('id', $ids)
                ->get();

            // 3. Mulai Perburuan Gambar
           // 3. Mulai Perburuan Gambar
            foreach ($products as $product) {
                
                // 3. Mulai Perburuan Gambar (Di dalam foreach)
                $cleanSku = trim(str_replace(["\r", "\n", "\t"], '', $product->sku));
                $apiUrl = rtrim($host, '/') . '/accurate/api/item/detail.do';

                $detailResponse = \Illuminate\Support\Facades\Http::withoutRedirecting()
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $integration->access_token,
                        'X-Session-ID' => $sessionToken
                    ])->get($apiUrl, [
                        'no' => $cleanSku 
                    ]);

                if ($detailResponse->successful() && $detailResponse->json('d')) {
                    $itemData = $detailResponse->json('d');
                    
                    // 🚨 PERUBAHAN BESAR: Cari array 'detailItemImage'
                    if (isset($itemData['detailItemImage']) && count($itemData['detailItemImage']) > 0) {
                        
                        // Ambil path file-nya
                        $imagePath = $itemData['detailItemImage'][0]['fileName'];
                        
                        // 🚨 PERBAIKAN: Ambil domain utamanya saja (https://odin.accurate.id)
                        $parsedHost = parse_url($host);
                        $domainOnly = $parsedHost['scheme'] . '://' . $parsedHost['host'];
                        
                        // Rangkai menjadi Full URL yang benar!
                        $fullImageUrl = $domainOnly . $imagePath;
                        
                        // UNDUH FISIK GAMBARNYA
                        // 🚨 KUNCI MASTER: Kirim otorisasi lewat Header, Cookie, DAN Query URL sekaligus!
                        $imageResponse = \Illuminate\Support\Facades\Http::withHeaders([
                            'Authorization' => 'Bearer ' . $integration->access_token,
                            'X-Session-ID' => $sessionToken,
                            // Paksa masukkan sebagai Cookie juga
                            'Cookie' => 'X-Session-ID=' . $sessionToken . '; Authorization=Bearer ' . $integration->access_token
                        ])->get($fullImageUrl, [
                            // Paksa masukkan ke ujung URL (?access_token=...&session=...)
                            'access_token' => $integration->access_token,
                            'session' => $sessionToken,
                            'Authorization' => 'Bearer ' . $integration->access_token
                        ]);

                        if ($imageResponse->successful()) {
                            // ... (Biarkan kode sukses sama seperti sebelumnya) ...
                            $imageContent = $imageResponse->body();
                            $originalName = $itemData['detailItemImage'][0]['originalName'] ?? '';
                            $slugName = \Illuminate\Support\Str::slug($product->name);
                            $fileName = $originalName ?: $slugName . '.png'; 
                            
                            $path = 'products/' . $websiteId . '/' . time() . '-' . $fileName;
                            \Illuminate\Support\Facades\Storage::disk('public')->put($path, $imageContent);
                            \Illuminate\Support\Facades\DB::table('products')->where('id', $product->id)->update(['image' => $path]);

                            $debugLog[$product->sku] = 'Sukses ditarik dan disimpan!';
                        } else {
                            // 🚨 CCTV TAMBAHAN: Tampilkan URL-nya jika masih gagal
                            $debugLog[$product->sku] = 'GAGAL UNDUH FISIK. Status: ' . $imageResponse->status() . ' | URL: ' . $fullImageUrl;
                        }
                    } else {
                        $debugLog[$product->sku] = 'TIDAK ADA GAMBAR DI ACCURATE (Array detailItemImage kosong).';
                    }
                } else {
                    $debugLog[$product->sku] = "GAGAL API DETAIL. Status: " . $detailResponse->status();
                }
            } // (Akhir dari foreach)
            return response()->json([
                'status' => 'success',
                'debug_log' => $debugLog
            ]);

        } catch (\Throwable $e) {
            // 🚨 AIRBAG: Tangkap error agar Javascript tidak meledak (Error 500 HTML)
            return response()->json([
                'status' => 'fatal_error',
                'pesan_error' => $e->getMessage()
            ]);
        }
    }
}