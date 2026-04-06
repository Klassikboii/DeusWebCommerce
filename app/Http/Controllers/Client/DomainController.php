<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    public function index(Website $website)
    {
        $this->authorize('viewAny', $website);

        // 🚨 ALAT PELACAK: Buka baris ini satu per satu untuk melihat isinya
        // dd($website->subscription); 
        // dd($website->subscription->package);

        // Buka Controller Anda dan ubah pelacaknya menjadi ini:
    // dd([
    //     'ID_Paket_Yang_Terkait' => $website->subscription->package->id,
    //     'Nama_Paket' => $website->subscription->package->name,
    //     'Nilai_Custom_Domain' => $website->subscription->package->can_custom_domain,
    //     'Nilai_Remove_Branding' => $website->subscription->package->remove_branding,
    //     'Tipe_Data' => gettype($website->subscription->package->can_custom_domain)
    // ]);
        
        
        return view('client.domains.index', compact('website'));
    }
    //old system
    public function updateDomain(Request $request, Website $website)
{
    // 1. Validasi Input
    // Perhatikan aturan unique:websites,subdomain. Ini MENCEGAH klien 
    // memakai subdomain yang sudah dipakai klien lain (misal: 'apple').
    $request->validate([
        'subdomain' => 'required|string|alpha_dash|max:50|unique:websites,subdomain,' . $website->id,
        // Tambahkan regex ini untuk memastikan format domain benar (contoh: namatoko.com, toko.co.id)
        'custom_domain' => [
            'nullable',
            'string',
            'max:255',
            'regex:/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/'
        ]
    ], [
        // Tambahkan pesan error custom dalam bahasa Indonesia agar klien paham
        'custom_domain.regex' => 'Format domain tidak valid. Pastikan domain mengandung ekstensi seperti .com, .id, atau .asia (tanpa http/www).'
    ]);

    // 2. Cek Hak Akses Fitur (Logika Keren Milikmu)
    // Pastikan relasinya sesuai dengan yang ada di database-mu (bisa subscription atau activeSubscription)
    $website->loadMissing('subscription.package'); 
    $canUseCustomDomain = $website->subscription?->package?->can_custom_domain === true;

    // 3. Pencegatan Keamanan (Jika klien iseng menembus form html)
    if (!$canUseCustomDomain && $request->filled('custom_domain')) {
        return back()->with('error', 'Paket Anda belum mendukung Custom Domain. Silakan upgrade paket.');
    }

    // 4. Simpan Subdomain (Semua paket berhak mengubah ini)
    $website->subdomain = strtolower($request->subdomain);

    // 5. Simpan Custom Domain (Hanya jika berhak)
    if ($canUseCustomDomain && $request->filled('custom_domain')) {
        // Bersihkan inputan klien (hapus https:// dan www. agar rapi)
        $customDomain = strtolower($request->custom_domain);
        $customDomain = preg_replace('#^https?://#', '', $customDomain);
        $customDomain = preg_replace('#^www\.#', '', $customDomain);
        $website->custom_domain = rtrim($customDomain, '/');
    } elseif (!$request->filled('custom_domain')) {
        // Jika form custom domain dikosongkan, hapus dari database
        $website->custom_domain = null; 
    }

    $website->save();

    return back()->with('success', 'Identitas domain toko berhasil diperbarui!');
}
    
    // Fitur batal/hapus domain
    public function destroy(Website $website)
    {
        $this->authorize('delete', $website);
        
        $website->update([
            'custom_domain' => null,
            'domain_status' => 'none'
        ]);
        
        return redirect()->back()->with('success', 'Custom domain dihapus. Website kembali ke subdomain.');
    }
    /**
     * Mengecek apakah DNS (A Record) Custom Domain klien sudah mengarah ke VPS kita.
     */
    public function checkDomain(Request $request, Website $website)
    {
        $request->validate([
            'domain_to_check' => 'required|string'
        ]);

        // Bersihkan inputan klien (jaga-jaga jika mereka mengetik http:// atau spasi)
        $domain = strtolower(trim($request->domain_to_check));
        $domain = str_replace(['http://', 'https://', '/'], '', $domain);

        // 🚨 GANTI DENGAN IP VPS JAGOAN HOSTING MILIKMU
        $vpsIp = '157.66.34.137'; 

        try {
            // Cek A Record dari domain klien di internet secara real-time
            $records = dns_get_record($domain, DNS_A);
            $isPointed = false;

            foreach ($records as $record) {
                if (isset($record['ip']) && $record['ip'] === $vpsIp) {
                    $isPointed = true;
                    break;
                }
            }

            if ($isPointed) {
                return redirect()->back()->with('success', "Hebat! Domain {$domain} sudah terhubung dengan sempurna ke server kami.");
            } else {
                return redirect()->back()->with('error', "Domain {$domain} belum mengarah ke server. Pastikan A Record di penyedia domain Anda sudah diatur ke IP: {$vpsIp}");
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Gagal mengecek domain. Pastikan penulisan domain benar (contoh: tokosaya.com).");
        }
    }
}