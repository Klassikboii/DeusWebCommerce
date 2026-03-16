<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Models\CustomerRfm;
use Illuminate\Http\Request;

class CustomerInsightController extends Controller
{
    public function index(\App\Models\Website $website)
    {
        // Pastikan hanya pemilik yang bisa melihat
        $this->authorize('view', $website);

        // ==========================================
        // 1. DATA RFM (SEGMEN PELANGGAN)
        // ==========================================
        $rfmData = \App\Models\CustomerRfm::where('website_id', $website->id)
            ->orderBy('monetary_value', 'desc')
            ->get();

        $segmentCounts = $rfmData->groupBy('segment')->map(function ($row) {
            return $row->count();
        });

        // ==========================================
        // 2. DATA MBA (MARKET BASKET ANALYSIS)
        // ==========================================
        // Kita tarik data rekomendasi, urutkan berdasarkan nilai Lift (Kekuatan Relasi) paling tinggi
        $mbaData = \App\Models\ProductRecommendation::where('website_id', $website->id)
            ->with(['product', 'recommendedProduct']) // Tarik relasi nama produknya
            ->orderBy('lift', 'desc')
            ->get();

        return view('client.insights.index', compact('website', 'rfmData', 'segmentCounts', 'mbaData'));
    }
}