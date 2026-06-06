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
        
        // 🚨 Cukup panggil dari Model. 
        // Array panjang yang sebelumnya ada di sini SUDAH KITA HAPUS!
        $templates = \App\Models\Website::getAvailableTemplates();

        return view('client.templates.index', compact('website', 'templates'));
    }

    public function update(Request $request, Website $website)
    {
        $this->authorize('update', $website);
        $request->validate(['template_id' => 'required|string|in:modern,classic,simple']);

        $templateId = $request->template_id;

        // 1. Siapkan Preset Gaya (Vibe) untuk masing-masing template
        $newConfig = $website->theme_config ?? [];
        
        // 🚨 SIAPKAN VARIABEL UNTUK WARNA HERO
        $heroBgColor = $website->hero_bg_color; 

        if ($templateId === 'classic') {
            $newConfig['typography']['heading'] = 'Playfair Display'; 
            $newConfig['typography']['body'] = 'Lora';
            $newConfig['shapes']['radius'] = '0px';                
            $newConfig['shapes']['shadow'] = 'none';               
            $newConfig['colors']['primary'] = '#000000';           
            $newConfig['colors']['bg_base'] = '#Fcfcfc';
            
            // Hero abu-abu elegan agar kontras dengan body Fcfcfc
            $heroBgColor = '#e9ecef'; 
        } 
        elseif ($templateId === 'modern') {
            $newConfig['typography']['heading'] = 'Plus Jakarta Sans'; 
            $newConfig['typography']['body'] = 'Inter';           
            $newConfig['shapes']['radius'] = '0.75rem';            
            $newConfig['shapes']['shadow'] = '0 10px 15px -3px rgba(0,0,0,0.1)'; 
            $newConfig['colors']['primary'] = '#0d6efd';           
            $newConfig['colors']['bg_base'] = '#f8f9fa';
            
            // Hero gelap pekat agar terlihat modern dan mencolok
            $heroBgColor = '#212529'; 
        }
        elseif ($templateId === 'simple') {
            $newConfig['typography']['heading'] = 'Montserrat';       
            $newConfig['typography']['body'] = 'Lato';
            $newConfig['shapes']['radius'] = '0.25rem';            
            $newConfig['shapes']['shadow'] = '0 1px 3px rgba(0,0,0,0.1)'; 
            $newConfig['colors']['primary'] = '#333333';
            $newConfig['colors']['bg_base'] = '#ffffff';
            
            // Hero abu-abu sangat muda agar berpisah dari background putih bersih
            $heroBgColor = '#f8f9fa'; 
        }

        // 2. Simpan Template Aktif, Config Baru, DAN Warna Hero Baru
        $website->update([
            'active_template' => $templateId,
            'theme_config' => $newConfig,
            'hero_bg_color' => $heroBgColor // 🚨 Suntikkan warna ke tabel websites
        ]);

        return redirect()->back()->with('success', 'Template berhasil diganti! Tampilan web Anda telah menyesuaikan gaya baru.');
    }
}