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
            ->with('customer') // 🚨 TAMBAHKAN BARIS INI
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
    /**
     * Memperbarui pengaturan diskon otomatis untuk Market Basket Analysis (Bundling).
     */
    public function updateMbaDiscount(\Illuminate\Http\Request $request, \App\Models\Website $website)
    {
        // 1. Validasi input: Pastikan nilainya berupa angka antara 0 sampai 100
        $validated = $request->validate([
            'mba_perfect_discount' => 'nullable|numeric|min:0|max:100',
            'mba_cross_discount'   => 'nullable|numeric|min:0|max:100',
        ]);

        // 2. Simpan ke database
        $website->update([
            'mba_perfect_discount' => $validated['mba_perfect_discount'] ?? 0,
            'mba_cross_discount'   => $validated['mba_cross_discount'] ?? 0,
        ]);

        // 3. Kembalikan ke halaman Insights beserta pesan sukses
        return redirect()->back()->with('success', 'Pengaturan diskon bundling AI berhasil diperbarui!');
    }
    
}