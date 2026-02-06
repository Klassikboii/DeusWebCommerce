<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use Illuminate\Http\Request;

class AppearanceController extends Controller
{
    public function index(Website $website)
    {
        // Hapus authorize jika bikin ribet saat dev, atau pastikan policy ada
        // $this->authorize('viewAny', $website); 

        $defaultMenu = [
            ['label' => 'Beranda', 'url' => '/'],
            ['label' => 'Produk', 'url' => '#shop'],
            ['label' => 'Blog', 'url' => '/blog'],
        ];

        // Karena sudah dicast di Model, ini otomatis jadi Array (tidak perlu json_decode)
        $menus = $website->navigation_menu ?? $defaultMenu;

        return view('client.appearance.index', compact('website', 'menus'));
    }

    public function update(Request $request, Website $website)
    {
        // $this->authorize('update', $website);

        $request->validate([
            'menus' => 'required|array',
            'menus.*.label' => 'required|string|max:20',
            'menus.*.url' => 'required|string',
        ]);

        // LANGSUNG SIMPAN ARRAY (Jangan di-json_encode lagi)
        // Laravel akan otomatis mengubahnya jadi JSON karena $casts di Model
        $website->update([
            'navigation_menu' => $request->menus
        ]);

        return redirect()->back()->with('success', 'Menu navigasi berhasil diperbarui!');
    }
}