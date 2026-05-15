<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PivotReferenceController extends Controller
{
    // API Pencarian Industri
    public function searchIndustries(Request $request)
    {
        $search = $request->get('q'); // Kata kunci yang diketik klien

        $query = DB::table('pivot_industries')
            ->select('id', 'parent_industry', 'child_industry', 'mcc');

        if ($search) {
            $query->where('child_industry', 'LIKE', "%{$search}%")
                  ->orWhere('parent_industry', 'LIKE', "%{$search}%");
        }

        // Ambil 50 data teratas agar tidak berat
        $industries = $query->limit(50)->get();

        // Format data sesuai permintaan library Select2
        $formatted = $industries->map(function ($item) {
            return [
                'id' => $item->mcc, // Kita simpan kode MCC-nya di database
                'text' => $item->parent_industry . ' - ' . $item->child_industry
            ];
        });

        return response()->json($formatted);
    }

    // API Pencarian Kecamatan
    public function searchDistricts(Request $request)
    {
        $search = $request->get('q');

        $query = DB::table('pivot_districts')->select('id', 'name');

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $districts = $query->limit(50)->get();

        $formatted = $districts->map(function ($item) {
            return [
                'id' => $item->id, // Ini adalah District ID dari Pivot
                'text' => $item->name . ' (ID: ' . $item->id . ')'
            ];
        });

        return response()->json($formatted);
    }
    public function searchBanks(Request $request)
{
    $search = $request->get('q');
    $banks = \DB::table('pivot_banks')
        ->where('bank_name', 'LIKE', "%{$search}%")
        ->orWhere('channel_code', 'LIKE', "%{$search}%")
        ->limit(20)
        ->get();

    return response()->json($banks->map(fn($item) => [
        'id' => $item->channel_code, // Value yang akan disimpan
        'text' => $item->bank_name
    ]));
}
}