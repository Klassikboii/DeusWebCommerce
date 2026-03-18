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
    public function update(Request $request, $id)
    {
        $website = Website::where('user_id', auth()->id())->findOrFail($id);

        // 1. Validasi Input
        $request->validate([
            'custom_domain' => [
                'required', 
                'string', 
                'unique:websites,custom_domain,' . $id, // Ignore diri sendiri
                'regex:/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i' // Validasi format domain (harus ada titiknya)
            ]
        ], [
            'custom_domain.regex' => 'Format domain salah. Gunakan format: contoh.com (tanpa http://)'
        ]);

        // 2. Bersihkan Input (Jaga-jaga user copas pakai http)
        $domain = strtolower($request->custom_domain);
        $domain = str_replace(['http://', 'https://', '/'], '', $domain);

        // 3. Simpan ke Database
        $website->update([
            'custom_domain' => $domain
        ]);

        return back()->with('success', 'Domain berhasil dihubungkan! Silakan tunggu propagasi DNS.');
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
}