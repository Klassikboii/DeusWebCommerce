<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::all();
        return view('admin.packages.index', compact('packages'));
    }

    // Tampilkan Form Tambah
    public function create()
    {
        return view('admin.packages.create');
    }

    // Simpan Paket Baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'slug' => 'required|string|unique:packages,slug',
            'price' => 'required|numeric',
            'duration_days' => 'required|integer',
            'max_products' => 'required|integer',
        ]);

        // 🚨 LOGIKA TEXTAREA KE ARRAY (Untuk fitur checklist)
        $featuresArray = [];
        if ($request->has('features') && !empty(trim($request->features))) {
            $featuresArray = array_values(array_filter(array_map('trim', explode("\n", $request->features))));
        }

        Package::create([
            'name' => $request->name,
            'slug' => $request->slug, 
            'price' => $request->price,
            'duration_days' => $request->duration_days,
            'max_products' => $request->max_products,
            'description' => $request->description,
            'can_custom_domain' => $request->has('custom_domain') ? 1 : 0,
            'remove_branding' => $request->has('remove_branding') ? 1 : 0,
            'features' => $featuresArray, // 🚨 TAMBAHKAN BARIS INI
            // 🚨 TAMBAHAN BARU: Tangkap dari checkbox form
            'has_ai_insights' => $request->has('has_ai_insights') ? 1 : 0,
            'has_custom_dashboard' => $request->has('has_custom_dashboard') ? 1 : 0,
            'has_shipping_markup' => $request->has('has_shipping_markup') ? 1 : 0,
        ]);

        return redirect()->route('admin.packages.index')->with('success', 'Paket baru berhasil dibuat!');
    }
    // Tampilkan Form Edit
    public function edit(Package $package)
    {
        return view('admin.packages.edit', compact('package'));
    }

    // Update Paket
    public function update(Request $request, Package $package)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'duration_days' => 'required|integer',
            'max_products' => 'required|integer',
        ]);

        // 🚨 LOGIKA TEXTAREA KE ARRAY
        $featuresArray = [];
        if ($request->has('features') && !empty(trim($request->features))) {
            $featuresArray = array_values(array_filter(array_map('trim', explode("\n", $request->features))));
        }
      
        $package->update([
            'name' => $request->name,
            'price' => $request->price,
            'duration_days' => $request->duration_days,
            'max_products' => $request->max_products,
            'description' => $request->description,
            'can_custom_domain' => $request->has('custom_domain') ? 1 : 0,
            'remove_branding' => $request->has('remove_branding') ? 1 : 0,
            'features' => $featuresArray, // 🚨 TAMBAHKAN BARIS INI
            // 🚨 TAMBAHAN BARU: Tangkap dari checkbox form
            'has_ai_insights' => $request->has('has_ai_insights') ? 1 : 0,
            'has_custom_dashboard' => $request->has('has_custom_dashboard') ? 1 : 0,
            'has_shipping_markup' => $request->has('has_shipping_markup') ? 1 : 0,
        ]);

        return redirect()->route('admin.packages.index')->with('success', 'Paket berhasil diperbarui!');
    }
    
    // Hapus Paket
    public function destroy(Package $package)
    {
        $package->delete();
        return back()->with('success', 'Paket dihapus.');
    }
}