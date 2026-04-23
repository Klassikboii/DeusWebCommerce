<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // 🚨 TAMBAHAN PENTING: Untuk menembak API
use Illuminate\Support\Facades\Log;  // 🚨 TAMBAHAN PENTING: Untuk mencatat error

class DomainController extends Controller
{
    public function index(Website $website)
    {
        $this->authorize('viewAny', $website);
        return view('client.domains.index', compact('website'));
    }

    public function updateDomain(Request $request, Website $website)
    {
        $request->validate([
            'subdomain' => 'required|string|alpha_dash|max:50|unique:websites,subdomain,' . $website->id,
            'custom_domain' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/'
            ]
        ], [
            'custom_domain.regex' => 'Format domain tidak valid. Pastikan domain mengandung ekstensi seperti .com, .id, atau .asia (tanpa http/www).'
        ]);

        $website->loadMissing('subscription.package'); 
        $canUseCustomDomain = $website->subscription?->package?->can_custom_domain === true;

        if (!$canUseCustomDomain && $request->filled('custom_domain')) {
            return back()->with('error', 'Paket Anda belum mendukung Custom Domain. Silakan upgrade paket.');
        }

        // Simpan subdomain dasar
        $website->subdomain = strtolower($request->subdomain);

        // 🚨 LOGIKA OTOMATISASI CLOUDFLARE DIMULAI DI SINI 🚨
        $oldCustomDomain = $website->custom_domain;

        if ($canUseCustomDomain && $request->filled('custom_domain')) {
            // Bersihkan inputan
            $customDomain = strtolower($request->custom_domain);
            $customDomain = preg_replace('#^https?://#', '', $customDomain);
            $customDomain = preg_replace('#^www\.#', '', $customDomain);
            $customDomain = rtrim($customDomain, '/');

            // Jika domainnya BARU atau BERUBAH dari yang sebelumnya
            if ($oldCustomDomain !== $customDomain) {
                
                // 1. Hapus domain lama di Cloudflare (jika sebelumnya punya)
                if (!empty($oldCustomDomain)) {
                    $this->removeCloudflareHostname($oldCustomDomain);
                }

                // 2. Daftarkan domain baru ke Cloudflare
                $cloudflareSuccess = $this->addCloudflareHostname($customDomain);
                
                if (!$cloudflareSuccess) {
                    return back()->with('error', 'Gagal mendaftarkan domain ke sistem keamanan Cloudflare. Silakan hubungi admin.');
                }
            }

            $website->custom_domain = $customDomain;

        } elseif (!$request->filled('custom_domain')) {
            // Jika form custom domain Dikosongkan (klien ingin berhenti pakai custom domain)
            if (!empty($oldCustomDomain)) {
                $this->removeCloudflareHostname($oldCustomDomain);
            }
            $website->custom_domain = null; 
        }

        $website->save();

        return back()->with('success', 'Identitas domain toko berhasil diperbarui!');
    }
    
    // Fitur batal/hapus domain
    public function destroy(Website $website)
    {
        $this->authorize('delete', $website);
        
        // Hapus dari Cloudflare jika ada
        if (!empty($website->custom_domain)) {
            $this->removeCloudflareHostname($website->custom_domain);
        }

        $website->update([
            'custom_domain' => null,
            // 'domain_status' => 'none' // Uncomment jika masih menggunakan kolom ini
        ]);
        
        return redirect()->back()->with('success', 'Custom domain dihapus. Website kembali ke subdomain.');
    }

    public function checkDomain(Request $request, Website $website)
    {
        $request->validate([
            'domain_to_check' => 'required|string'
        ]);

        $domain = strtolower(trim($request->domain_to_check));
        $domain = str_replace(['http://', 'https://', '/'], '', $domain);

        // 🚨 GANTI DENGAN IP VPS JAGOAN HOSTING MILIKMU ATAU IP PROXY CLOUDFLARE
        // Catatan: Karena Anda pakai Cloudflare, ping ke CNAME klien biasanya 
        // akan menghasilkan IP Cloudflare. Namun, untuk validasi awal, ini masih oke.
        $vpsIp = '157.66.34.137'; 

        try {
            $records = dns_get_record($domain, DNS_A);
            $isPointed = false;

            foreach ($records as $record) {
                // Modifikasi: Karena lewat Cloudflare, klien bisa diarahkan via CNAME 
                // ke deusserver.ashop.asia. Kita bisa sesuaikan logikanya di kemudian hari.
                if (isset($record['ip']) && $record['ip'] === $vpsIp) {
                    $isPointed = true;
                    break;
                }
            }

            if ($isPointed) {
                return redirect()->back()->with('success', "Hebat! Domain {$domain} sudah terhubung dengan sempurna ke server kami.");
            } else {
                return redirect()->back()->with('error', "Domain {$domain} belum mengarah ke server. Pastikan Anda mengatur CNAME / A Record di panel domain Anda.");
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Gagal mengecek domain. Pastikan penulisan domain benar.");
        }
    }


    /* ====================================================================
     * 🛠️ FUNGSI BANTUAN CLOUDFLARE (PRIVATE METHODS)
     * ==================================================================== */

    /**
     * Menambahkan Custom Hostname ke Cloudflare untuk dibuatkan SSL otomatis.
     */
    private function addCloudflareHostname($hostname)
    {
        $token = env('CLOUDFLARE_API_TOKEN');
        $zoneId = env('CLOUDFLARE_ZONE_ID');

        if (!$token || !$zoneId) {
            Log::error('Cloudflare API Token atau Zone ID belum diatur di .env');
            return false;
        }

        $response = Http::withToken($token)
            ->post("https://api.cloudflare.com/client/v4/zones/{$zoneId}/custom_hostnames", [
                'hostname' => $hostname,
                'ssl' => [
                    'method' => 'http',
                    'type' => 'dv', // Domain Validation (Standar SSL Let's Encrypt / Google)
                    'settings' => [
                        'min_tls_version' => '1.2'
                    ]
                ]
            ]);

        if ($response->successful()) {
            return true;
        }

        Log::error('Gagal tambah Cloudflare Hostname: ' . $response->body());
        return false;
    }

    /**
     * Menghapus Custom Hostname dari Cloudflare jika klien mengganti/membatalkan domainnya.
     */
    private function removeCloudflareHostname($hostname)
    {
        $token = env('CLOUDFLARE_API_TOKEN');
        $zoneId = env('CLOUDFLARE_ZONE_ID');

        if (!$token || !$zoneId) return false;

        // Langkah 1: Kita harus mencari tahu ID dari hostname tersebut di Cloudflare
        $searchResponse = Http::withToken($token)
            ->get("https://api.cloudflare.com/client/v4/zones/{$zoneId}/custom_hostnames", [
                'hostname' => $hostname
            ]);

        if ($searchResponse->successful() && !empty($searchResponse->json()['result'])) {
            $hostnameId = $searchResponse->json()['result'][0]['id'];

            // Langkah 2: Lakukan eksekusi hapus berdasarkan ID
            $deleteResponse = Http::withToken($token)
                ->delete("https://api.cloudflare.com/client/v4/zones/{$zoneId}/custom_hostnames/{$hostnameId}");

            return $deleteResponse->successful();
        }

        return false; // Domain tidak ditemukan di Cloudflare
    }
}