<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use Illuminate\Http\Request;
use App\Models\Order;    // Pastikan Model Order di-import
use App\Models\Product;  // Pastikan Model Product di-import
use App\Models\OrderItem; // Pastikan Model OrderItem di-import
use Illuminate\Support\Facades\DB; // Untuk query builder

class DashboardController extends Controller
{
    public function index($website_id)
    {
        $website = Website::findOrFail($website_id);
        
        $this->authorize('view', $website);

        // --- DEFINISI STATUS YANG DIANGGAP "PENDAPATAN" ---
        // Kita anggap uang masuk jika statusnya: paid, processing, shipped, atau completed
        $paidStatuses = ['completed'];

        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // 1. PENDAPATAN BULAN INI
        $revenueThisMonth = Order::where('website_id', $website->id)
            ->whereIn('status', $paidStatuses) // <--- PERBAIKAN DISINI
            ->where('created_at', '>=', $startOfMonth)
            ->sum('total_amount');

        // 2. PENDAPATAN BULAN LALU
        $revenueLastMonth = Order::where('website_id', $website->id)
            ->whereIn('status', $paidStatuses) // <--- PERBAIKAN DISINI
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->sum('total_amount');

        // Hitung Growth
        $revenueGrowth = 0;
        if ($revenueLastMonth > 0) {
            $revenueGrowth = (($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100;
        } elseif ($revenueThisMonth > 0) {
            $revenueGrowth = 100;
        }

        // 3. STATISTIK COUNTER
        $totalOrder = Order::where('website_id', $website->id)->count();
        $pendingOrders = Order::where('website_id', $website->id)->where('status', 'pending')->count();
        $totalProduk = Product::where('website_id', $website->id)->count();

        // 4. GRAFIK (30 HARI TERAKHIR)
        $salesData = Order::where('website_id', $website->id)
            ->whereIn('status', $paidStatuses) // <--- PERBAIKAN DISINI
            ->where('created_at', '>=', $now->copy()->subDays(30))
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        $chartLabels = [];
        $chartValues = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i)->format('Y-m-d');
            $chartLabels[] = $now->copy()->subDays($i)->format('d M');
            $sale = $salesData->firstWhere('date', $date);
            $chartValues[] = $sale ? (int)$sale->total : 0;
        }

        // 5. PRODUK TERLARIS
        $topProducts = OrderItem::whereHas('order', function($q) use ($website, $paidStatuses) {
                $q->where('website_id', $website->id)
                  ->whereIn('status', $paidStatuses); // <--- PERBAIKAN DISINI
            })
            ->select('product_name', DB::raw('SUM(qty) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        // 6. ORDER TERAKHIR
        $recentOrders = Order::where('website_id', $website->id)
                             ->latest()
                             ->take(5)
                             ->get();

        // 7. TAMBAHAN: STOK MENIPIS (Kurang dari 5)
        $lowStockProducts = Product::where('website_id', $website->id)
        ->where('stock', '<=', 5) // Ambang batas stok
        ->orderBy('stock', 'asc') // Urutkan dari yang paling sedikit
        ->take(5)
        ->get();                     

        return view('client.dashboard.index', compact(
            'website', 
            'revenueThisMonth', 'revenueLastMonth', 'revenueGrowth',
            'totalOrder', 'pendingOrders', 'totalProduk',
            'chartLabels', 'chartValues', 'topProducts', 'recentOrders', 'lowStockProducts'
        ));
        
    }
}