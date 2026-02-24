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
        // 1. Validasi Input (PERBAIKAN: Tambahkan validasi warna)
        $request->validate([
            'primary_color' => 'nullable|string|max:20',
            'secondary_color' => 'nullable|string|max:20',
            'hero_bg_color' => 'nullable|string|max:20',
            'font_family' => 'nullable|string|max:50',
            
            // Validasi Gambar
            'hero_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'favicon' => 'nullable|image|mimes:ico,png,webp|max:1024',
        ]);

        // 2. Simpan Config Dasar
        $website->fill([
            'primary_color' => $request->primary_color ?? '#0d6efd', // Default Bootstrap Blue
            'secondary_color' => $request->secondary_color ?? '#6c757d', // Default Grey
            'hero_bg_color' => $request->hero_bg_color ?? '#333333',
            'font_family' => $request->font_family ?? 'Inter',
            'base_font_size' => $request->base_font_size ?? 16,
            'product_image_ratio' => $request->product_image_ratio ?? '1/1',
        ]);

        // 3. Simpan Sections JSON
        if ($request->filled('sections_json')) {
            $sections = json_decode($request->sections_json, true);

            // --- TAMBAHAN BARU: Handle Upload Gambar Dinamis (Text & Image) ---
            if ($request->hasFile('section_images')) {
                foreach ($request->file('section_images') as $sectionId => $file) {
                    // Simpan gambar ke folder 'sections'
                    $path = $file->store('sections', 'public');
                    
                    // Cari section mana yang punya ID ini, lalu masukkan path gambarnya ke data JSON
                    foreach ($sections as &$sec) {
                        if ($sec['id'] === $sectionId) {
                            $sec['data']['image'] = $path;
                            break;
                        }
                    }
                }
            }
        }

        // 4. Handle Gambar (Kode Anda sudah benar, saya rapikan sedikit)
        if ($request->hasFile('logo')) {
            if ($website->logo) Storage::disk('public')->delete($website->logo); // Hapus lama
            $website->logo = $request->file('logo')->store('logos', 'public');
        }

        if ($request->hasFile('hero_image')) {
            if ($website->hero_image) Storage::disk('public')->delete($website->hero_image);
            $website->hero_image = $request->file('hero_image')->store('heroes', 'public');
        }
        
        if ($request->hasFile('favicon')) {
            if ($website->favicon) Storage::disk('public')->delete($website->favicon);
            $website->favicon = $request->file('favicon')->store('favicons', 'public');
        }

        // Handle Hapus
        if ($request->boolean('remove_hero_image')) {
            if ($website->hero_image) Storage::disk('public')->delete($website->hero_image);
            $website->hero_image = null;
        }
        if ($request->boolean('remove_logo')) {
             if ($website->logo) Storage::disk('public')->delete($website->logo);
             $website->logo = null;
        }

        $website->save();

        return back()->with('success', 'Perubahan berhasil disimpan!');
    }
    // Fungsi khusus untuk menerima upload gambar via AJAX
    public function uploadImage(Request $request, Website $website)
    {
        $this->authorize('update', $website);

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Simpan gambar ke storage public/sections
        $path = $request->file('image')->store('sections', 'public');

        // Kembalikan respon JSON ke JavaScript
        return response()->json([
            'success' => true,
            'path' => $path, // path relatif (misal: sections/abc.jpg)
            'url' => asset('storage/' . $path) // URL penuh untuk preview
        ]);
    }
    
    // }
}