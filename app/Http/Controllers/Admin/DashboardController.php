<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Website;
use Illuminate\Http\Request;
use App\Models\Transaction; 
use App\Models\Order; // <--- Import model Order untuk hitung GMV
use App\Models\MerchantKybDetail; // <--- Import model KYB

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::where('role', 'client')->count();
        $totalWebsites = Website::count();
        
        // 1. PENDAPATAN SAAS (Dari Transaksi Langganan Paket)
        $totalRevenue = Transaction::where('status', 'approved')->sum('amount');
        
        // 2. 🚀 TOTAL GMV PLATFORM BULAN INI (Perputaran Uang Seluruh Toko)
        $startOfMonth = now()->startOfMonth();
        // Kita hitung order yang sudah dibayar/selesai
        $totalGMV = Order::whereIn('status', ['paid', 'processing', 'shipped', 'completed'])
                         ->where('created_at', '>=', $startOfMonth)
                         ->sum('total_amount');
        
        // 3. PENDING ACTIONS (Pekerjaan Admin yang Menunggu)
        $pendingTransactions = Transaction::where('status', 'pending')->count();
        $pendingKybCount = MerchantKybDetail::where('status', 'pending')->count();

        // 4. DATA TERBARU (Untuk List di Bawah)
        $latestTransactions = Transaction::with('user')->latest()->take(5)->get();
        $latestWebsites = Website::with('user')->latest()->take(5)->get();
        $latestKyb = MerchantKybDetail::latest()->take(5)->get(); // 🚨 KYB Terbaru

        return view('admin.dashboard', compact(
            'totalUsers', 'totalWebsites', 'totalRevenue', 'totalGMV',
            'pendingTransactions', 'pendingKybCount', 
            'latestTransactions', 'latestWebsites', 'latestKyb'
        ));
    }
}