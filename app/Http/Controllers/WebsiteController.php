<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Website;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WebsiteController extends Controller
{
    // Menampilkan halaman pilih website
    public function index()
    {
        // Ambil semua website milik user yang sedang login
        $websites = Website::where('user_id', Auth::id())->get();
        
        // Kita return ke view (nanti kita buat di langkah selanjutnya)
        return view('websites.index', compact('websites'));
    }

    // Proses membuat website baru
    public function store(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'subdomain' => 'required|alpha_dash|unique:websites,subdomain',
        ],
        [
            // Custom pesan error bahasa Indonesia
            'subdomain.unique' => 'Maaf, nama domain ini sudah dipakai toko lain.',
            'subdomain.alpha_dash' => 'Domain hanya boleh huruf, angka, dan strip (-).',
        ]);

        Website::create([
            'user_id' => Auth::id(),
            'site_name' => $request->site_name,
            'subdomain' => Str::slug($request->subdomain), // Pastikan format URL aman
            'template_id' => 1, // Default template ID 1 dulu
            'status' => 'draft'
        ]);

        return redirect()->back()->with('success', 'Website berhasil dibuat!');
    }
}