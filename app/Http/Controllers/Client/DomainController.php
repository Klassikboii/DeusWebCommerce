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
        return view('client.domains.index', compact('website'));
    }

   public function update(Request $request)
    {
        $website = $request->website;

        // Validasi input
        $request->validate([
            'custom_domain' => 'nullable|string|max:255'
        ]);

        $cleanDomain = $request->custom_domain;

        if ($cleanDomain) {
            // 🚨 PEMBERSIHAN OTOMATIS (SANITIZATION)
            // Klien sering iseng mengetik http:// atau www., kita harus bersihkan
            // karena Laravel Middleware kita mencari domain mentahnya saja.
            
            // 1. Hapus http:// atau https://
            $cleanDomain = preg_replace('/^https?:\/\//', '', $cleanDomain);
            
            // 2. Hapus www.
            $cleanDomain = preg_replace('/^www\./', '', $cleanDomain);
            
            // 3. Hapus garis miring di akhir (jika ada)
            $cleanDomain = rtrim($cleanDomain, '/');
            
            // 4. Jadikan huruf kecil semua
            $cleanDomain = strtolower($cleanDomain);
        }

        // Simpan ke database
        $website->update([
            'custom_domain' => $cleanDomain
        ]);

        return redirect()->back()->with('success', 'Custom Domain berhasil disimpan! Pastikan Anda telah mengarahkan DNS domain Anda ke IP server kami.');
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