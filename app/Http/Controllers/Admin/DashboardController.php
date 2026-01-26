<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Website;
use Illuminate\Http\Request;
use App\Models\Transaction; // <--- Jangan lupa import ini

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Hitung Total User (Klien)
        $totalUsers = User::where('role', 'client')->count();
        
        // 2. Hitung Total Website
        $totalWebsites = Website::count();

        // 3. HITUNG DUIT (REVENUE)
        // Ambil semua transaksi yang statusnya 'approved', lalu jumlahkan kolom 'amount'
        $totalRevenue = Transaction::where('status', 'approved')->sum('amount');
        
        return view('admin.dashboard', compact('totalUsers', 'totalWebsites', 'totalRevenue'));
    }
}