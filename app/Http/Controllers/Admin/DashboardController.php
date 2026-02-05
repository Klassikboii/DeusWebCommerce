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
        $totalUsers = User::where('role', 'client')->count();
        $totalWebsites = Website::count();
        
        // Asumsi Anda punya model Transaction
        // Total uang masuk (status approved)
        $totalRevenue = \App\Models\Transaction::where('status', 'approved')->sum('amount');
        
        // 1. Cek berapa yang statusnya 'pending' (Butuh aksi Anda segera!)
        $pendingTransactions = \App\Models\Transaction::where('status', 'pending')->count();

        // 2. Ambil 5 Transaksi Terakhir
        $latestTransactions = \App\Models\Transaction::with('user')->latest()->take(5)->get();

        // 3. Ambil 5 Website Terbaru
        $latestWebsites = Website::with('user')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalUsers', 'totalWebsites', 'totalRevenue', 
            'pendingTransactions', 'latestTransactions', 'latestWebsites'
        ));
    }
}