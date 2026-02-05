<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BuilderController extends Controller
{
    // Tampilkan Halaman Editor
    public function index(Website $website)
    {
        // Security Check
        $this->authorize('viewAny', $website);
        
        return view('client.builder.index', compact('website'));
    }
   public function update(Request $request, Website $website)
{
    // $website = Website::findOrFail($id);

    // 1. Validasi Input
    $request->validate([
        'hero_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        'favicon' => 'nullable|image|mimes:ico,png,webp|max:1024',
    ]);

    // 2. Simpan Data Style & Config Dasar (Kolom Biasa)
    // Kita gunakan $request->only untuk mengambil field yang memang ada kolomnya
    $website->fill($request->only([
        'primary_color', 
        'secondary_color', 
        'hero_bg_color', 
        'font_family', 
        'base_font_size', 
        'product_image_ratio'
    ]));

    // 3. LOGIKA UTAMA: Simpan Sections JSON
    // Kita cek apakah ada input rahasia bernama 'sections_json'
    if ($request->filled('sections_json')) {
        // Decode JSON dari string menjadi Array PHP, lalu simpan
        $website->sections = json_decode($request->sections_json, true);
    }

    // 4. Handle Upload Gambar (Tetap menggunakan logika lama untuk upload)
    // Gambar disimpan di storage, tapi path-nya kita update ke Database
    if ($request->hasFile('logo')) {
        $path = $request->file('logo')->store('logos', 'public');
        $website->logo = $path;
    }

    if ($request->hasFile('hero_image')) {
        $path = $request->file('hero_image')->store('heroes', 'public');
        $website->hero_image = $path;
    }
    
    if ($request->hasFile('favicon')) {
        $path = $request->file('favicon')->store('favicons', 'public');
        $website->favicon = $path;
    }

    // Handle Hapus Gambar
    if ($request->has('remove_hero_image')) $website->hero_image = null;
    if ($request->has('remove_logo')) $website->logo = null;
    if ($request->has('remove_favicon')) $website->favicon = null;

    $website->save();

    return back()->with('success', 'Perubahan berhasil disimpan!');
}

    // Proses Simpan Perubahan Desain
    // public function update(Request $request, Website $website)
    // {
    //     if ($website->user_id !== auth()->id()) abort(403);

    //     $request->validate([
    //         'primary_color' => 'required|string|max:7',
    //         'secondary_color' => 'required|string|max:7',
    //         // Validasi Teks (Boleh kosong/nullable)
    //         'hero_title' => 'nullable|string|max:100',
    //         'hero_subtitle' => 'nullable|string|max:255',
    //         'hero_btn_text' => 'nullable|string|max:50',
    //         // ... validasi lama ...
    //         'hero_bg_color' => 'required', 
    //         'sections_json' => 'nullable|json',// Validasi baru
    //     ]);

    //     // $data = [
    //     //     'primary_color' => $request->primary_color,
    //     //     'secondary_color' => $request->secondary_color,
    //     //     'hero_bg_color' => $request->hero_bg_color, // Simpan warna banner

    //     //     // --- UPDATE BAGIAN TEKS ---
    //     //     'hero_title' => $request->hero_title,
    //     //     'hero_subtitle' => $request->hero_subtitle,
    //     //     'hero_btn_text' => $request->hero_btn_text,
    //     //     // --------------------------
    //     //     // ... data text lainnya ...
    //     //     'font_family' => $request->font_family,
    //     //     'base_font_size' => $request->base_font_size,
    //     //     'product_image_ratio' => $request->product_image_ratio,
    //     // ];

    //     $data = $request->except(['logo', 'favicon', 'hero_image', 'sections_json']);

    //     // --- LOGIKA HAPUS / RESET GAMBAR (BARU) ---
        
    //     // 1. Reset Logo
    //     if ($request->boolean('remove_logo')) {
    //         if ($website->logo && \Storage::disk('public')->exists($website->logo)) {
    //             \Storage::disk('public')->delete($website->logo);
    //         }
    //         $data['logo'] = null; // Set null di database
    //     }

    //     // 2. Reset Favicon
    //     if ($request->boolean('remove_favicon')) {
    //         if ($website->favicon && \Storage::disk('public')->exists($website->favicon)) {
    //             \Storage::disk('public')->delete($website->favicon);
    //         }
    //         $data['favicon'] = null;
    //     }

    //     // 3. Reset Banner Image
    //     if ($request->boolean('remove_hero_image')) {
    //         if ($website->hero_image && \Storage::disk('public')->exists($website->hero_image)) {
    //             \Storage::disk('public')->delete($website->hero_image);
    //         }
    //         $data['hero_image'] = null;
    //     }

    //     // --- LOGIKA UPLOAD BARU (YANG LAMA) ---
    //     // (Pastikan logika upload tetap ada di bawah logika hapus)
        
    //     if ($request->hasFile('logo')) {
    //         $data['logo'] = $request->file('logo')->store('assets/' . $website->id, 'public');
    //     }
    //     if ($request->hasFile('favicon')) {
    //         $data['favicon'] = $request->file('favicon')->store('assets/' . $website->id, 'public');
    //     }
    //     if ($request->hasFile('hero_image')) {
    //         $data['hero_image'] = $request->file('hero_image')->store('assets/' . $website->id, 'public');
    //     }

    //     // --- LOGIKA BARU: SECTIONS ---
    //     // Kita ambil string JSON dari input rahasia, decode jadi Array, lalu simpan.
    //     if ($request->filled('sections_json')) {
    //         $data['sections'] = json_decode($request->sections_json, true);
    //     }

    //     $website->update($data);

    //     return redirect()->back()->with('success', 'Tampilan berhasil diperbarui!');
    // }
}