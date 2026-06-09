<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request, Website $website)
    {
        $this->authorize('viewAny', $website);
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        // Query Utama Rincian Harian
        $reports = $website->orders()
            ->whereIn('status', ['paid', 'processing', 'shipped', 'completed'])
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

        // 🚨 FIX: Format tanggal menjadi 'd M' (contoh: 01 Apr, 15 May)
        $chartLabels = $reports->map(function ($item) {
            return \Carbon\Carbon::parse($item->date)->format('d');
        });
        $chartValues = $reports->pluck('revenue');

        $grandTotal = $reports->sum('revenue');
        $totalTrx = $reports->sum('total_orders');

        // 🚨 TAMBAHAN: Top 5 Produk Terlaris Bulan Ini
        $topProducts = \App\Models\OrderItem::whereHas('order', function($q) use ($website, $month, $year) {
                $q->where('website_id', $website->id)
                  ->whereIn('status', ['paid', 'processing', 'shipped', 'completed'])
                  ->whereMonth('created_at', $month)
                  ->whereYear('created_at', $year);
            })
            ->select('product_name', DB::raw('SUM(qty) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        return view('client.reports.index', compact('website', 'reports', 'grandTotal', 'totalTrx', 'month', 'year', 'chartLabels', 'chartValues', 'topProducts'));
    }

    // 🚨 TAMBAHAN FUNGSI BARU: Export ke CSV
    public function export(Request $request, Website $website)
    {
        $this->authorize('viewAny', $website);
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        $reports = $website->orders()
            ->whereIn('status', ['paid', 'processing', 'shipped', 'completed'])
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

        $namaBulan = \DateTime::createFromFormat('!m', $month)->format('F');
        $fileName = "Laporan_Penjualan_{$website->site_name}_{$namaBulan}_{$year}.csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        // Buat file CSV langsung secara native (sangat cepat & ringan)
        $callback = function() use($reports) {
            $file = fopen('php://output', 'w');
            
            // Tulis Judul Kolom
            fputcsv($file, ['Tanggal', 'Jumlah Transaksi', 'Pendapatan (Rp)']);

            // Tulis Isi Data
            foreach ($reports as $row) {
                fputcsv($file, [
                    \Carbon\Carbon::parse($row->date)->format('d F Y'),
                    $row->total_orders,
                    $row->revenue
                ]);
            }

            // Tulis Total Keseluruhan di baris paling bawah
            fputcsv($file, ['', '', '']);
            fputcsv($file, ['TOTAL KESELURUHAN=', $reports->sum('total_orders'), $reports->sum('revenue')]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}