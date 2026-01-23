<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use Illuminate\Http\Request;

class BuilderController extends Controller
{
    // Tampilkan Halaman Editor
    public function index(Website $website)
    {
        // Security Check
        if ($website->user_id !== auth()->id()) abort(403);
        
        return view('client.builder.index', compact('website'));
    }

    // Proses Simpan Perubahan Desain
    public function update(Request $request, Website $website)
    {
        if ($website->user_id !== auth()->id()) abort(403);

        $request->validate([
            'primary_color' => 'required|string|max:7',
            'secondary_color' => 'required|string|max:7',
            // Validasi Teks (Boleh kosong/nullable)
            'hero_title' => 'nullable|string|max:100',
            'hero_subtitle' => 'nullable|string|max:255',
            'hero_btn_text' => 'nullable|string|max:50',
        ]);

        $website->update([
            'primary_color' => $request->primary_color,
            'secondary_color' => $request->secondary_color,
            // Simpan Teks Baru
            'hero_title' => $request->hero_title,
            'hero_subtitle' => $request->hero_subtitle,
            'hero_btn_text' => $request->hero_btn_text,
        ]);

        return redirect()->back()->with('success', 'Desain & Konten berhasil disimpan!');
    }
}