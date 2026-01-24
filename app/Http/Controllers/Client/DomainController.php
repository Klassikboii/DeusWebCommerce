<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    public function index(Website $website)
    {
        if ($website->user_id !== auth()->id()) abort(403);
        return view('client.domains.index', compact('website'));
    }

    public function update(Request $request, Website $website)
    {
        if ($website->user_id !== auth()->id()) abort(403);

        // Ganti regex yang rumit dengan validasi string biasa dulu untuk testing
        $request->validate([
            'custom_domain' => 'required|string|min:4|max:100', 
        ]);

        // Simpan domain dengan status 'pending' (menunggu verifikasi admin pusat)
        $website->update([
            'custom_domain' => $request->custom_domain,
            'domain_status' => 'pending'
        ]);

        return redirect()->back()->with('success', 'Request domain berhasil dikirim! Silakan ikuti instruksi DNS di bawah.');
    }
    
    // Fitur batal/hapus domain
    public function destroy(Website $website)
    {
        if ($website->user_id !== auth()->id()) abort(403);
        
        $website->update([
            'custom_domain' => null,
            'domain_status' => 'none'
        ]);
        
        return redirect()->back()->with('success', 'Custom domain dihapus. Website kembali ke subdomain.');
    }
}