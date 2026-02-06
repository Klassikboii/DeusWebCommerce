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
            $website->sections = json_decode($request->sections_json, true);
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