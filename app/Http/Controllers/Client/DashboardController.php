<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Website $website)
    {
        if ($website->user_id !== auth()->id()) abort(403);
        // Cek: "Apakah yang login ini Admin?"
        if (auth()->user()->role === 'admin') {
            // Jika YA, lempar ke Dashboard Admin
            return redirect()->route('admin.dashboard');
        }

        

        // HITUNG STATISTIK REAL
        $stats = [
            'total_revenue' => $website->orders()->where('status', '!=', 'cancelled')->sum('total_amount'),
            'total_orders' => $website->orders()->count(),
            // Nanti bisa tambah visitor count jika sudah ada fitur tracking
        ];

        return view('client.dashboard.index', compact('website', 'stats'));
    }
}