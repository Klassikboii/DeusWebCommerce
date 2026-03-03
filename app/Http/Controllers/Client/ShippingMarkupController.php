<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Models\City;
use App\Models\ShippingMarkup;
use Illuminate\Http\Request;

class ShippingMarkupController extends Controller
{
    // Tampilkan Halaman Pengaturan
    public function index(Website $website)
    {
        // Ambil daftar markup yang sudah diatur oleh toko ini
        $markups = $website->shippingMarkups()->with('city.province')->get();
        
        // Ambil semua data kota untuk ditaruh di dropdown
        $cities = City::with('province')->orderBy('name')->get();

        return view('client.shipping_markups.index', compact('website', 'markups', 'cities'));
    }

    // Simpan Pengaturan Markup Baru
    public function store(Request $request, Website $website)
    {
        $request->validate([
            'city_id' => 'required|exists:cities,id',
            'markup_type' => 'required|in:nominal,percent',
            'markup_value' => 'required|numeric|min:0'
        ]);

        // Kita pakai updateOrCreate agar jika kota yang sama diisi lagi, ia akan mengupdate data lama (bukan duplikat)
        ShippingMarkup::updateOrCreate(
            [
                'website_id' => $website->id,
                'city_id' => $request->city_id
            ],
            [
                'markup_type' => $request->markup_type,
                'markup_value' => $request->markup_value
            ]
        );

        return redirect()->back()->with('success', 'Aturan keuntungan ongkir berhasil disimpan!');
    }

    // Hapus Pengaturan
    public function destroy(Website $website, $id)
    {
        $markup = ShippingMarkup::where('website_id', $website->id)->findOrFail($id);
        $markup->delete();

        return redirect()->back()->with('success', 'Aturan keuntungan ongkir berhasil dihapus!');
    }
}