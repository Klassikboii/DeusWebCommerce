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
        $search = $request->get('q');
        
        $query = DB::table('pivot_industries')
                    ->select('id', 'parent_industry', 'child_industry', 'mcc');

        if ($search) {
            $query->where('child_industry', 'like', "%{$search}%")
                  ->orWhere('parent_industry', 'like', "%{$search}%")
                  ->orWhere('mcc', 'like', "%{$search}%");
        }

        $industries = $query->limit(20)->get();

        // 🚨 KUNCI PERBAIKAN: Gunakan $item->id sebagai nilai 'id', bukan $item->mcc
        $formatted = $industries->map(function ($item) {
            return [
                'id' => $item->id, // Kirim Primary Key tabel ke form HTML
                'text' => $item->parent_industry . ' > ' . $item->child_industry . ' (' . $item->mcc . ')'
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