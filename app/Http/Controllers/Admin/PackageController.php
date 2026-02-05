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

        Package::create([
            'name' => $request->name,
            'slug' => $request->slug, // misal: pro-monthly
            'price' => $request->price,
            'duration_days' => $request->duration_days,
            'max_products' => $request->max_products,
            'can_custom_domain' => $request->has('can_custom_domain'), // Checkbox
            'remove_branding' => $request->has('remove_branding'),     // Checkbox
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

        $package->update([
            'name' => $request->name,
            'price' => $request->price,
            'duration_days' => $request->duration_days,
            'max_products' => $request->max_products,
            'can_custom_domain' => $request->has('can_custom_domain'),
            'remove_branding' => $request->has('remove_branding'),
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