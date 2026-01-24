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
            // ... validasi lama ...
            'hero_bg_color' => 'required', // Validasi baru
        ]);

        $data = [
            'primary_color' => $request->primary_color,
            'secondary_color' => $request->secondary_color,
            'hero_bg_color' => $request->hero_bg_color, // Simpan warna banner
            // ... data text lainnya ...
            'font_family' => $request->font_family,
            'base_font_size' => $request->base_font_size,
            'product_image_ratio' => $request->product_image_ratio,
        ];

        // --- LOGIKA HAPUS / RESET GAMBAR (BARU) ---
        
        // 1. Reset Logo
        if ($request->boolean('remove_logo')) {
            if ($website->logo && \Storage::disk('public')->exists($website->logo)) {
                \Storage::disk('public')->delete($website->logo);
            }
            $data['logo'] = null; // Set null di database
        }

        // 2. Reset Favicon
        if ($request->boolean('remove_favicon')) {
            if ($website->favicon && \Storage::disk('public')->exists($website->favicon)) {
                \Storage::disk('public')->delete($website->favicon);
            }
            $data['favicon'] = null;
        }

        // 3. Reset Banner Image
        if ($request->boolean('remove_hero_image')) {
            if ($website->hero_image && \Storage::disk('public')->exists($website->hero_image)) {
                \Storage::disk('public')->delete($website->hero_image);
            }
            $data['hero_image'] = null;
        }

        // --- LOGIKA UPLOAD BARU (YANG LAMA) ---
        // (Pastikan logika upload tetap ada di bawah logika hapus)
        
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('assets/' . $website->id, 'public');
        }
        if ($request->hasFile('favicon')) {
            $data['favicon'] = $request->file('favicon')->store('assets/' . $website->id, 'public');
        }
        if ($request->hasFile('hero_image')) {
            $data['hero_image'] = $request->file('hero_image')->store('assets/' . $website->id, 'public');
        }

        $website->update($data);

        return redirect()->back()->with('success', 'Desain berhasil diperbarui!');
    }
}