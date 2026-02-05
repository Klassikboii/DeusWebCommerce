<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use Illuminate\Http\Request;

class AppearanceController extends Controller
{
    public function index(Website $website)
    {
        $this->authorize('viewAny', $website);

        // Menu Default jika database masih kosong
        $defaultMenu = [
            ['label' => 'Beranda', 'url' => '/'],
            ['label' => 'Produk', 'url' => '#shop'],
            ['label' => 'Blog', 'url' => '/blog'],
        ];

        // Ambil dari DB, jika null pakai default
        $menus = $website->navigation_menu ? json_decode($website->navigation_menu, true) : $defaultMenu;

        return view('client.appearance.index', compact('website', 'menus'));
    }

    public function update(Request $request, Website $website)
    {
        $this->authorize('update', $website);

        // Validasi input array
        $request->validate([
            'menus' => 'required|array',
            'menus.*.label' => 'required|string|max:20',
            'menus.*.url' => 'required|string',
        ]);

        // Simpan sebagai JSON
        $website->update([
            'navigation_menu' => json_encode($request->menus)
        ]);

        return redirect()->back()->with('success', 'Menu navigasi berhasil diperbarui!');
    }
}