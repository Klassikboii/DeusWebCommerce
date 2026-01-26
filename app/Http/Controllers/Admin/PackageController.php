<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    // MENAMPILKAN DAFTAR PAKET
    public function index()
    {
        $packages = Package::all();
        return view('admin.packages.index', compact('packages'));
    }

    // FORM EDIT PAKET
    public function edit(Package $package)
    {
        return view('admin.packages.edit', compact('package'));
    }

    // SIMPAN PERUBAHAN
    public function update(Request $request, Package $package)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'max_products' => 'required|numeric',
            // can_custom_domain dll itu boolean (checkbox), jadi tidak perlu required
        ]);

        $package->update([
            'name' => $request->name,
            'price' => $request->price,
            'max_products' => $request->max_products,
            'can_custom_domain' => $request->has('can_custom_domain'),
            'remove_branding' => $request->has('remove_branding'),
        ]);

        return redirect()->route('admin.packages.index')->with('success', 'Paket berhasil diperbarui!');
    }
}