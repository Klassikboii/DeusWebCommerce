<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Website;
use App\Models\Package;      // Tambah
use App\Models\Subscription; // Tambah
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class WebsiteController extends Controller
{
    public function index()
    {
        // Ambil semua website milik user yang sedang login
        $websites = Website::where('user_id', Auth::id())->get();
        // Tarik definisi tema dari Model
        $availableTemplates = Website::getAvailableTemplates();
        
        // Kita return ke view (nanti kita buat di langkah selanjutnya)
        return view('websites.index', compact('websites', 'availableTemplates'));
    }

    public function store(Request $request)
    {
        // 1. Validasi Input (Termasuk Template)
        $request->validate([
            'site_name' => 'required|string|max:50',
            'subdomain' => 'required|alpha_dash|unique:websites,subdomain|max:30',
            'template'  => 'required|in:simple,modern,classic', // Pilihan Template,
            
        ], [
            'subdomain.unique' => 'Maaf, nama domain ini sudah dipakai orang lain.',
            'subdomain.alpha_dash' => 'Domain hanya boleh huruf, angka, dan strip (-).',
        ]);
        $user = Auth::user();
        $templateId = $request->template;

        // 2. LOGIKA TEMA (Sama persis seperti di TemplateController)
        $themeConfig = [];
        $heroBgColor = '#ffffff'; // Default fallback aman

        if ($templateId === 'classic') {
            $themeConfig['typography']['heading'] = 'Playfair Display'; 
            $themeConfig['typography']['body'] = 'Lora';
            $themeConfig['shapes']['radius'] = '0px';                
            $themeConfig['shapes']['shadow'] = 'none';               
            $themeConfig['colors']['primary'] = '#000000';           
            $themeConfig['colors']['bg_base'] = '#Fcfcfc';
            
            $heroBgColor = '#e9ecef'; // Abu-abu elegan
        } 
        elseif ($templateId === 'modern') {
            $themeConfig['typography']['heading'] = 'Plus Jakarta Sans'; 
            $themeConfig['typography']['body'] = 'Inter';           
            $themeConfig['shapes']['radius'] = '0.75rem';            
            $themeConfig['shapes']['shadow'] = '0 10px 15px -3px rgba(0,0,0,0.1)'; 
            $themeConfig['colors']['primary'] = '#0d6efd';           
            $themeConfig['colors']['bg_base'] = '#f8f9fa';
            
            $heroBgColor = '#212529'; // Gelap pekat
        }
        elseif ($templateId === 'simple') {
            $themeConfig['typography']['heading'] = 'Montserrat';       
            $themeConfig['typography']['body'] = 'Lato';
            $themeConfig['shapes']['radius'] = '0.25rem';            
            $themeConfig['shapes']['shadow'] = '0 1px 3px rgba(0,0,0,0.1)'; 
            $themeConfig['colors']['primary'] = '#333333';
            $themeConfig['colors']['bg_base'] = '#ffffff';
            
            $heroBgColor = '#f8f9fa'; // Abu-abu terang (agar berpisah dari background putih)
        }

        // 2. Siapkan Data JSON Default (Sesuai Template)
        // 2. Siapkan Data JSON Default (Sesuai Template)
        $defaultSections = [];

        if ($request->template == 'simple') {
            $defaultSections = [
                ["id" => "hero-1", "type" => "hero", "visible" => true, "data" => ["title" => "Selamat Datang di Toko Kami", "subtitle" => "Belanja kebutuhan Anda dengan mudah dan cepat.", "button_text" => "Lihat Koleksi", "button_link" => "#products"]],
                ["id" => "products", "type" => "products", "visible" => true, "data" => ["title" => "Produk Pilihan", "limit" => 8]]
            ];
        } 
        elseif ($request->template == 'modern') {
             $defaultSections = [
                ["id" => "hero-1", "type" => "hero", "visible" => true, "data" => ["title" => "Modern & Dinamis", "subtitle" => "Gaya hidup masa kini dimulai dari sini.", "button_text" => "Shop Now", "button_link" => "#products"]],
                ["id" => "features", "type" => "features", "visible" => true, "data" => ["title" => "Keunggulan Kami"]],
                ["id" => "products", "type" => "products", "visible" => true, "data" => ["title" => "Koleksi Eksklusif", "limit" => 12]]
            ];
        }
        // 🚨 TAMBAHAN UNTUK TEMA CLASSIC
        elseif ($request->template == 'classic') {
             $defaultSections = [
                ["id" => "hero-1", "type" => "hero", "visible" => true, "data" => ["title" => "Elegansi Klasik", "subtitle" => "Kualitas premium untuk Anda yang mengutamakan cita rasa.", "button_text" => "Lihat Produk", "button_link" => "#products"]],
                ["id" => "products", "type" => "products", "visible" => true, "data" => ["title" => "Koleksi Premium", "limit" => 8]],
                // ["id" => "testimonial", "type" => "testimonial", "visible" => true, "data" => ["title" => "Apa Kata Mereka"]]
            ];
        }

        // 3. Buat Website
        $website = Website::create([
            'user_id' => auth()->id(),
            'site_name' => $request->site_name,
            'subdomain' => strtolower($request->subdomain),
            'active_template' => $request->template ?? 'simple', // Mengambil dari modal popup tadi
            'theme_config' => $themeConfig,
            'hero_bg_color' => $heroBgColor,
            'primary_color' => ($request->template == 'modern') ? '#111111' : '#0d6efd', // Warna default beda tiap template
            'sections' => $defaultSections,

            'navigation_menu' => [
                ['label' => 'Beranda', 'url' => '/'],
                ['label' => 'Produk', 'url' => '/products'], // <-- Ubah di sini
                ['label' => 'Blog', 'url' => '/blog'],       // <-- Ubah di sini
            ],
        ]);

        // 4. OTOMATIS BERIKAN PAKET FREE TRIAL
        $freePackage = Package::where('slug', 'free')->first();
        if (!$freePackage) $freePackage = Package::first(); // Fallback

        if ($freePackage) {
            Subscription::create([
                'website_id' => $website->id,
                'package_id' => $freePackage->id,
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => now()->addDays($freePackage->duration_days ?? 30),
            ]);
        }

        return redirect()->back()->with('success', 'Toko berhasil dibuat! Paket Free Trial telah aktif.');
    }
}