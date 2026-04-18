<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index(Website $website)
    {
        $this->authorize('viewAny', $website);
        
        // Data Dummy Template yang tersedia
        $templates = [
            [
                'id' => 'modern',
                'name' => 'Modern Blue',
                'description' => 'Tampilan profesional dengan banner besar.',
                'image' => 'https://via.placeholder.com/300x200?text=Modern+Theme' // Nanti bisa diganti screenshot asli
            ],
            [
                'id' => 'classic', // <-- TEMA BARU KITA
                'name' => 'Classic Elegant',
                'description' => 'Desain premium bergaya butik dengan logo di tengah dan font Serif.',
                'image' => 'https://via.placeholder.com/300x200?text=Classic+Theme'
            ],
            [
                'id' => 'simple',
                'name' => 'Clean Minimalist',
                'description' => 'Fokus pada produk dengan desain putih bersih.',
                'image' => 'https://via.placeholder.com/300x200?text=Simple+Theme'
            ]
        ];

        return view('client.templates.index', compact('website', 'templates'));
    }

    public function update(Request $request, Website $website)
    {
        $this->authorize('update', $website);
        $request->validate(['template_id' => 'required|string|in:modern,classic,simple']);

        $templateId = $request->template_id;

        // 1. Siapkan Preset Gaya (Vibe) untuk masing-masing template
        $newConfig = $website->theme_config ?? [];

        if ($templateId === 'classic') {
            $newConfig['typography']['main'] = 'Playfair Display'; // Font elegan
            $newConfig['shapes']['radius'] = '0px';                // Sudut tajam
            $newConfig['shapes']['shadow'] = 'none';               // Flat design
            $newConfig['colors']['primary'] = '#000000';           // Hitam premium
            $newConfig['colors']['bg_base'] = '#Fcfcfc';           // Off-white
        } 
        elseif ($templateId === 'modern') {
            $newConfig['typography']['main'] = 'Inter';            // Font santai
            $newConfig['shapes']['radius'] = '0.75rem';            // Melengkung (12px)
            $newConfig['shapes']['shadow'] = '0 10px 15px -3px rgba(0,0,0,0.1)'; // Bayangan melayang
            $newConfig['colors']['primary'] = '#0d6efd';           // Biru modern
            $newConfig['colors']['bg_base'] = '#f8f9fa';           // Abu-abu sangat muda
        }
        elseif ($templateId === 'simple') {
            $newConfig['typography']['main'] = 'Roboto';
            $newConfig['shapes']['radius'] = '0.25rem';            // Sedikit melengkung (4px)
            $newConfig['shapes']['shadow'] = '0 1px 3px rgba(0,0,0,0.1)'; // Bayangan tipis
            $newConfig['colors']['primary'] = '#333333';
            $newConfig['colors']['bg_base'] = '#ffffff';           // Putih bersih
        }

        // 2. Simpan Template Aktif & Config Baru
        // KITA TIDAK MENYENTUH KOLOM 'sections' SAMA SEKALI! KONTEN AMAN!
        $website->update([
            'active_template' => $templateId,
            'theme_config' => $newConfig
        ]);

        return redirect()->back()->with('success', 'Template berhasil diganti! Tampilan web Anda telah menyesuaikan gaya baru.');
    }
}