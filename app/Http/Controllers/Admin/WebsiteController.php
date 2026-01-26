<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Website;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    public function index()
    {
        // Ambil semua website, beserta info user dan paket aktifnya
        $websites = Website::with(['user', 'activeSubscription.package'])->latest()->get();
        
        return view('admin.websites.index', compact('websites'));
    }

    public function destroy(Website $website)
    {
        // Hapus website (dan otomatis menghapus produk, order, dll karena cascade)
        $website->delete();
        
        return redirect()->back()->with('success', 'Website berhasil dihapus dari sistem.');
    }
}