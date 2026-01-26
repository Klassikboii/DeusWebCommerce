<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Package;

class LandingController extends Controller
{
    public function index()
    {
        // Jika user sudah login, langsung lempar ke Dashboard masing-masing
        if (auth()->check()) {
            if (auth()->user()->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('client.websites');
        }

        // Jika belum login, tampilkan Landing Page
        // Kita ambil data paket untuk ditampilkan di bagian Pricing
        $packages = Package::all();
        
        return view('landing', compact('packages'));
    }
}