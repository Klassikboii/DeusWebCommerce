<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request, Website $website)
    {
        $this->authorize('viewAny', $website);
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        // Query Utama
        $reports = $website->orders()
            ->whereIn('status', ['paid', 'processing', 'shipped', 'completed']) // Hanya order yang sah
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // 🚨 Persiapan data untuk Grafik (Label tanggal, Nilai pendapatan)
        $chartLabels = $reports->pluck('date');
        $chartValues = $reports->pluck('revenue');

        $grandTotal = $reports->sum('revenue');
        $totalTrx = $reports->sum('total_orders');

        return view('client.reports.index', compact('website', 'reports', 'grandTotal', 'totalTrx', 'month', 'year', 'chartLabels', 'chartValues'));
    }
}