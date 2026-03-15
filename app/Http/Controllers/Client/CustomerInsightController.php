<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Models\CustomerRfm;
use Illuminate\Http\Request;

class CustomerInsightController extends Controller
{
    public function index(Website $website)
    {
        // Pastikan hanya pemilik yang bisa melihat
        $this->authorize('view', $website);

        // Ambil data RFM yang sudah dihitung oleh AI kita
        $rfmData = CustomerRfm::where('website_id', $website->id)
            ->orderBy('monetary_value', 'desc') // Urutkan dari yang belanjanya paling banyak
            ->get();

        // Siapkan data untuk Grafik (Pie/Donut Chart)
        // Menghitung jumlah orang per segmen (Misal: Champions ada 10 orang, At Risk ada 5 orang)
        $segmentCounts = $rfmData->groupBy('segment')->map(function ($row) {
            return $row->count();
        });

        return view('client.insights.index', compact('website', 'rfmData', 'segmentCounts'));
    }
}