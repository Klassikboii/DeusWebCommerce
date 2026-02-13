<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Models\ShippingRange;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function index(Website $website)
    {
        $this->authorize('viewAny', $website);
        $ranges = $website->shippingRanges;
        return view('client.shipping.index', compact('website', 'ranges'));
    }

    // 1. UPDATE LOKASI TOKO (LAT/LONG)
    public function updateLocation(Request $request, Website $website)
    {
        $this->authorize('update', $website);
        
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $website->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return back()->with('success', 'Lokasi toko berhasil diperbarui!');
    }

    // 2. TAMBAH RANGE HARGA
    public function store(Request $request, Website $website)
    {
        $this->authorize('update', $website);

        $request->validate([
            'min_km' => 'required|numeric|min:0',
            'max_km' => 'required|numeric|gt:min_km', // Max harus lebih besar dari Min
            'price'  => 'required|numeric|min:0',
        ]);

        $website->shippingRanges()->create([
            'min_km' => $request->min_km,
            'max_km' => $request->max_km,
            'price' => $request->price,
        ]);

        return back()->with('success', 'Range tarif berhasil ditambahkan.');
    }

    // 3. HAPUS RANGE
    public function destroy(Website $website, ShippingRange $range)
    {
        $this->authorize('update', $website);
        $range->delete();
        return back()->with('success', 'Range tarif dihapus.');
    }
}