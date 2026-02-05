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
        
        // Kita return ke view (nanti kita buat di langkah selanjutnya)
        return view('websites.index', compact('websites'));
    }

    public function store(Request $request)
    {
        // 1. Validasi Input (Termasuk Template)
        $request->validate([
            'site_name' => 'required|string|max:50',
            'subdomain' => 'required|alpha_dash|unique:websites,subdomain|max:30',
            'template'  => 'required|in:simple,modern', // Pilihan Template
        ], [
            'subdomain.unique' => 'Maaf, nama domain ini sudah dipakai orang lain.',
            'subdomain.alpha_dash' => 'Domain hanya boleh huruf, angka, dan strip (-).',
        ]);

        // 2. Siapkan Data JSON Default (Sesuai Template)
        $defaultSections = [];

        if ($request->template == 'simple') {
            $defaultSections = [
                ["id" => "hero-1", "type" => "hero", "visible" => true, "data" => ["title" => "Selamat Datang", "subtitle" => "Belanja Murah & Berkualitas", "button_text" => "Lihat Produk", "button_link" => "#products"]],
                ["id" => "products", "type" => "products", "visible" => true, "data" => ["title" => "Produk Terbaru", "limit" => 8]]
            ];
        } elseif ($request->template == 'modern') {
             $defaultSections = [
                // Template Modern mungkin punya hero slider atau layout beda
                ["id" => "hero-1", "type" => "hero", "visible" => true, "data" => ["title" => "Modern Store", "subtitle" => "Gaya Baru Belanja Online", "button_text" => "Shop Now", "button_link" => "#products"]],
                ["id" => "features", "type" => "features", "visible" => true, "data" => ["title" => "Keunggulan Kami"]],
                ["id" => "products", "type" => "products", "visible" => true, "data" => ["title" => "Koleksi Eksklusif", "limit" => 12]]
            ];
        }

        // 3. Buat Website
        $website = Website::create([
            'user_id' => auth()->id(),
            'site_name' => $request->site_name,
            'subdomain' => strtolower($request->subdomain),
            'active_template' => $request->template, // Simpan pilihan user
            'primary_color' => ($request->template == 'modern') ? '#111111' : '#0d6efd', // Warna default beda tiap template
            'sections' => $defaultSections,
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