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
        // Cari ID produk yang: Induknya kosong ATAU punya varian yang kosong
        $productIds = \Illuminate\Support\Facades\DB::table('products')
            ->leftJoin('product_variants', 'products.id', '=', 'product_variants.product_id')
            ->where('products.website_id', $websiteId)
            ->where(function($query) {
                // Syarat 1: Induknya Kosong
                $query->whereNull('products.image')
                      ->orWhere('products.image', '')
                // Syarat 2: ATAU Variannya Kosong
                      ->orWhereNull('product_variants.image')
                      ->orWhere('product_variants.image', '');
            })
            // Gunakan distinct agar ID produk tidak dobel jika ada banyak varian yang kosong
            ->distinct()
            ->pluck('products.id');

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

          // --- FUNGSI BANTUAN UNDUH GAMBAR (Agar kode tidak panjang & berulang) ---
            $downloadImage = function($imagePath, $originalName, $productName) use ($host, $integration, $sessionToken, $websiteId) {
                $parsedHost = parse_url($host);
                $domainOnly = $parsedHost['scheme'] . '://' . $parsedHost['host'];
                $fullImageUrl = $domainOnly . $imagePath;

                $imageResponse = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => 'Bearer ' . $integration->access_token,
                    'X-Session-ID' => $sessionToken,
                    'Cookie' => 'X-Session-ID=' . $sessionToken . '; Authorization=Bearer ' . $integration->access_token
                ])->get($fullImageUrl, [
                    'access_token' => $integration->access_token,
                    'session' => $sessionToken,
                    'Authorization' => 'Bearer ' . $integration->access_token
                ]);

                if ($imageResponse->successful()) {
                    $imageContent = $imageResponse->body();
                    $slugName = \Illuminate\Support\Str::slug($productName);
                    // Tambahkan uniqid() sedikit agar jika namanya sama, tidak saling timpa
                    $fileName = $originalName ?: $slugName . '-' . uniqid() . '.png'; 
                    $path = 'products/' . $websiteId . '/' . time() . '-' . $fileName;
                    
                    \Illuminate\Support\Facades\Storage::disk('public')->put($path, $imageContent);
                    return $path; // Kembalikan alamat penyimpanannya
                }
                return null;
            };
            // ------------------------------------------------------------------------

            // 3. Mulai Perburuan Gambar (Sapu Jagat: Induk & Varian)
            foreach ($products as $product) {
                
                $logKey = $product->sku ? $product->sku : 'ID-'.$product->id.' ('.$product->name.')';
                $apiUrl = rtrim($host, '/') . '/accurate/api/item/detail.do';

                $parentImagePathLocal = null; 

               // --- TAHAP A: PROSES PRODUK INDUK ---
                $cleanParentSku = trim(str_replace(["\r", "\n", "\t"], '', $product->sku ?? ''));
                $parentImagePathLocal = $product->image; // Bawaan dari DB lokal
                $isParentEmpty = empty($parentImagePathLocal);

                if ($isParentEmpty && !empty($cleanParentSku)) {
                    // Hanya tarik dari Accurate jika di lokal BENAR-BENAR kosong
                    $parentRes = \Illuminate\Support\Facades\Http::withoutRedirecting()
                        ->withHeaders([
                            'Authorization' => 'Bearer ' . $integration->access_token,
                            'X-Session-ID' => $sessionToken
                        ])->get($apiUrl, ['no' => $cleanParentSku]);

                    if ($parentRes->successful() && $parentRes->json('d')) {
                        $itemData = $parentRes->json('d');
                        if (isset($itemData['detailItemImage']) && count($itemData['detailItemImage']) > 0) {
                            $imgPath = $itemData['detailItemImage'][0]['fileName'];
                            $oriName = $itemData['detailItemImage'][0]['originalName'] ?? '';

                            $downloadedPath = $downloadImage($imgPath, $oriName, $product->name);
                            if ($downloadedPath) {
                                \Illuminate\Support\Facades\DB::table('products')
                                    ->where('id', $product->id)
                                    ->update(['image' => $downloadedPath]);
                                $parentImagePathLocal = $downloadedPath; // Update variabel lokal
                                $debugLog[$logKey] = 'Gambar Induk ditarik dari Accurate!';
                            }
                        }
                    }
                } else {
                    if (!$isParentEmpty) {
                        $debugLog[$logKey] = 'Induk diabaikan (Sudah punya gambar lokal).';
                    }
                }

                // --- TAHAP B: PROSES SEMUA ANAK VARIAN ---
                $variants = \Illuminate\Support\Facades\DB::table('product_variants')
                    ->where('product_id', $product->id)
                    ->get(); // Hapus 'whereNotNull' agar kita bisa memproses semua varian

                $firstVariantImagePathLocal = null;

                foreach ($variants as $variant) {
                    // Cek gambar varian di lokal
                    $isVariantEmpty = empty($variant->image);
                    
                    // Jika varian sudah punya gambar, simpan path-nya untuk pewarisan & skip proses download
                    if (!$isVariantEmpty) {
                        if (!$firstVariantImagePathLocal) $firstVariantImagePathLocal = $variant->image;
                        $debugLog[$variant->sku ?? 'Varian-'.$variant->id] = 'Varian diabaikan (Sudah punya gambar lokal).';
                        continue; // Lanjut ke varian berikutnya!
                    }

                    // Jika kosong dan punya SKU, baru meluncur ke Accurate
                    $cleanVariantSku = trim(str_replace(["\r", "\n", "\t"], '', $variant->sku ?? ''));
                    if (!empty($cleanVariantSku)) {
                        $variantRes = \Illuminate\Support\Facades\Http::withoutRedirecting()
                            ->withHeaders([
                                'Authorization' => 'Bearer ' . $integration->access_token,
                                'X-Session-ID' => $sessionToken
                            ])->get($apiUrl, ['no' => $cleanVariantSku]);

                        if ($variantRes->successful() && $variantRes->json('d')) {
                            $vData = $variantRes->json('d');
                            if (isset($vData['detailItemImage']) && count($vData['detailItemImage']) > 0) {
                                $vImgPath = $vData['detailItemImage'][0]['fileName'];
                                $vOriName = $vData['detailItemImage'][0]['originalName'] ?? '';

                                $vLocalPath = $downloadImage($vImgPath, $vOriName, $product->name . '-' . $variant->sku);

                                if ($vLocalPath) {
                                    \Illuminate\Support\Facades\DB::table('product_variants')
                                        ->where('id', $variant->id)
                                        ->update(['image' => $vLocalPath]);
                                    
                                    $debugLog[$variant->sku] = 'Gambar Varian ditarik dari Accurate!';
                                    if (!$firstVariantImagePathLocal) $firstVariantImagePathLocal = $vLocalPath;
                                }
                            }
                        }
                    }
                }

                // --- TAHAP C: FALLBACK / PEWARISAN (Tetap sama) ---
                if (empty($parentImagePathLocal) && !empty($firstVariantImagePathLocal)) {
                    \Illuminate\Support\Facades\DB::table('products')
                        ->where('id', $product->id)
                        ->update(['image' => $firstVariantImagePathLocal]);
                    $debugLog[$logKey] = 'Induk Kosong: Meminjam gambar dari varian.';
                }
                
            } // (Akhir dari foreach products)
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