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

        // 2. Simpan Config Dasar (Dibungkus ke dalam JSON theme_config)
        
        // Ambil konfigurasi yang sudah ada (jika ada), atau array kosong
        $themeConfig = $website->theme_config ?? []; 

        // Susun ulang menjadi struktur JSON yang rapi
        $themeConfig['colors'] = [
            'primary' => $request->primary_color ?? ($themeConfig['colors']['primary'] ?? '#0d6efd'),
            'secondary' => $request->secondary_color ?? ($themeConfig['colors']['secondary'] ?? '#6c757d'),
            'bg_hero' => $request->hero_bg_color ?? ($themeConfig['colors']['bg_hero'] ?? '#333333'),

            'bg_base' => $request->bg_base_color ?? ($themeConfig['colors']['bg_base'] ?? '#ffffff'),
            'text_base' => $request->text_base_color ?? ($themeConfig['colors']['text_base'] ?? '#212529'),
        ];

        $themeConfig['typography'] = [
            'main' => $request->font_family ?? ($themeConfig['typography']['main'] ?? 'Inter'),
        ];

        // 👇 PERUBAHAN DI SINI: Tambah Radius & Shadow
        $themeConfig['shapes'] = [
            'product_ratio' => $request->product_image_ratio ?? ($themeConfig['shapes']['product_ratio'] ?? '1/1'),
            'radius' => $request->border_radius ?? ($themeConfig['shapes']['radius'] ?? '0.5rem'),
            'shadow' => $request->box_shadow ?? ($themeConfig['shapes']['shadow'] ?? '0 0.125rem 0.25rem rgba(0,0,0,0.075)'),
        ];
        

        // Masukkan kembali ke variabel model
        $website->theme_config = $themeConfig;

        // 3. Simpan Sections JSON
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
            
            // 👇 INI BARIS YANG HILANG SEBELUMNYA! WAJIB ADA 👇
            $website->sections = $sections;
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