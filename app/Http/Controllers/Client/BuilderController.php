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
        // 1. Validasi Input (Rehaul Warna)
        $request->validate([
            'primary_color'   => 'nullable|string|max:20',
            'secondary_color' => 'nullable|string|max:20',
            'accent_color'    => 'nullable|string|max:20', // Tambahan untuk Warna Brand
            'text_base_color' => 'nullable|string|max:20',
            'font_family'     => 'nullable|string|max:50',
            
            // Validasi Gambar
            'hero_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'logo'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'favicon'    => 'nullable|image|mimes:ico,png,webp|max:1024',
        ]);

        // 2. Simpan Config Dasar (Dibungkus ke dalam JSON theme_config)
        $themeConfig = $website->theme_config ?? []; 

        // REHAUL STRUKTUR WARNA
        $themeConfig['colors'] = [
            // Primary sekarang berfungsi sebagai Latar Belakang (Background)
            'primary'   => $request->primary_color ?? ($themeConfig['colors']['primary'] ?? '#f8f9fa'),
            // Secondary berfungsi sebagai Elemen (Card, Navbar)
            'secondary' => $request->secondary_color ?? ($themeConfig['colors']['secondary'] ?? '#ffffff'),
            // Accent berfungsi sebagai Warna Brand (Tombol, Link, Badge)
            'accent'    => $request->accent_color ?? ($themeConfig['colors']['accent'] ?? '#0d6efd'),
            // Text Base untuk tulisan utama
            'text_base' => $request->text_base_color ?? ($themeConfig['colors']['text_base'] ?? '#212529'),
            
            // Backward Compatibility: Jika ada template lama yang masih memanggil bg_base atau bg_hero, arahkan ke primary
            'bg_hero'   => $request->primary_color ?? ($themeConfig['colors']['primary'] ?? '#f8f9fa'),
            'bg_base'   => $request->primary_color ?? ($themeConfig['colors']['primary'] ?? '#f8f9fa'),
        ];

        $themeConfig['typography'] = [
            'heading' => $request->font_heading ?? ($themeConfig['typography']['heading'] ?? 'Playfair Display'),
            'body'    => $request->font_body ?? ($themeConfig['typography']['body'] ?? 'Inter'),
            'main'    => $request->font_heading ?? ($themeConfig['typography']['main'] ?? 'Inter'),
        ];

        $themeConfig['shapes'] = [
            'product_ratio' => $request->product_image_ratio ?? ($themeConfig['shapes']['product_ratio'] ?? '1/1'),
            'radius'        => $request->border_radius ?? ($themeConfig['shapes']['radius'] ?? '0.5rem'),
            'shadow'        => $request->box_shadow ?? ($themeConfig['shapes']['shadow'] ?? '0 0.125rem 0.25rem rgba(0,0,0,0.075)'),
        ];
        
        $website->theme_config = $themeConfig;

        // 3. Simpan Sections JSON
        if ($request->filled('sections_json')) {
            $sections = json_decode($request->sections_json, true);

            // Handle Upload Gambar Dinamis (Text & Image)
            if ($request->hasFile('section_images')) {
                foreach ($request->file('section_images') as $sectionId => $file) {
                    $path = $file->store('sections', 'public');
                    foreach ($sections as &$sec) {
                        if ($sec['id'] === $sectionId) {
                            $sec['data']['image'] = $path;
                            break;
                        }
                    }
                }
            }
            
            $website->sections = $sections;
        }
        
        // 4. Handle Gambar (Dengan Penjaga Keamanan / Gatekeeper Hapus)
        // (Sama seperti kode asli Anda)
        if ($request->hasFile('logo')) {
            if ($website->logo && str_starts_with($website->logo, 'logos/')) {
                Storage::disk('public')->delete($website->logo); 
            }
            $website->logo = $request->file('logo')->store('logos', 'public');
        }

        if ($request->hasFile('hero_image')) {
            if ($website->hero_image && str_starts_with($website->hero_image, 'heroes/')) {
                Storage::disk('public')->delete($website->hero_image);
            }
            $website->hero_image = $request->file('hero_image')->store('heroes', 'public');
        }
        
        if ($request->hasFile('favicon')) {
            if ($website->favicon && str_starts_with($website->favicon, 'favicons/')) {
                Storage::disk('public')->delete($website->favicon);
            }
            $website->favicon = $request->file('favicon')->store('favicons', 'public');
        }

        if ($request->boolean('remove_hero_image')) {
            if ($website->hero_image && str_starts_with($website->hero_image, 'heroes/')) {
                Storage::disk('public')->delete($website->hero_image);
            }
            $website->hero_image = null;
        }
        
        if ($request->boolean('remove_logo')) {
            if ($website->logo && str_starts_with($website->logo, 'logos/')) {
                Storage::disk('public')->delete($website->logo);
            }
            $website->logo = null;
        }

        if ($request->boolean('remove_favicon')) {
            if ($website->favicon && str_starts_with($website->favicon, 'favicons/')) {
                Storage::disk('public')->delete($website->favicon);
            }
            $website->favicon = null;
        }

        $website->save();

        \App\Models\UserActivity::log(
            'update_website_frontend', 
            "Memperbarui frontend toko: {$website->name}"
        );

        return back()->with('success', 'Perubahan berhasil disimpan!');
    }
}