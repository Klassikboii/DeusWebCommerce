<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Models\ShippingRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShippingController extends Controller
{
    public function index(Website $website)
    {
        $this->authorize('viewAny', $website);
        $rates = $website->shippingRates()
                        ->orderBy('origin_city', 'asc')
                        ->orderBy('destination_city', 'asc')
                        ->paginate(20);
        return view('client.shipping.index', compact('website', 'rates'));
    }

    public function store(Request $request, Website $website)
    {
        $this->authorize('update', $website);

        $request->validate([
            'origin_city' => 'required|string|max:100',
            'destination_city' => 'required|string|max:100',
            'courier_name' => 'required|string|max:50',
            'rate_per_kg' => 'required|numeric|min:0',
        ]);

        $website->shippingRates()->create([
            'origin_city' => $request->origin_city,
            'destination_city' => $request->destination_city,
            'courier_name' => $request->courier_name,
            'service_name' => $request->service_name ?? 'Reguler', // Default Reguler jika kosong
            'rate_per_kg' => $request->rate_per_kg,
            'min_weight' => $request->min_weight ?? 1,
            'min_day' => $request->min_day,
            'max_day' => $request->max_day,
        ]);

        return back()->with('success', 'Ongkos kirim berhasil ditambahkan.');
    }

    public function destroy(Website $website, ShippingRate $rate)
    {
        $this->authorize('update', $website);
        if($rate->website_id !== $website->id) abort(403);
        $rate->delete();
        return back()->with('success', 'Data ongkir dihapus.');
    }

    // === FITUR BARU: HAPUS SEMUA DATA ===
    public function clear(Website $website)
    {
        $this->authorize('update', $website);
        
        // Hapus semua ongkir milik website ini
        $website->shippingRates()->delete();

        return back()->with('success', 'Semua data ongkos kirim berhasil dikosongkan.');
    }

    public function downloadTemplate()
    {
        $fileName = 'template_ongkir_lengkap.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        // Header 8 Kolom
        $columns = ['Kota Asal', 'Kota Tujuan', 'Kurir', 'Layanan', 'Tarif', 'Min Berat', 'Est Min', 'Est Max'];

        $callback = function() use($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            // Contoh Data
            fputcsv($file, ['Surabaya', 'Jakarta', 'JNE', 'REG', '12000', '1', '2', '3']);
            fputcsv($file, ['Surabaya', 'Bandung', 'SiCepat', 'HALU', '10000', '1', '3', '5']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // === LOGIKA IMPORT 8 KOLOM ===
    public function import(Request $request, Website $website)
    {
        $this->authorize('update', $website);

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120' // Max 5MB
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), "r");
        
        // Skip Header
        fgetcsv($handle); 

        DB::beginTransaction();
        try {
            $count = 0;
            while (($row = fgetcsv($handle)) !== FALSE) {
                
                // Fix: Pecah jika terbaca 1 baris string panjang
                if (count($row) == 1 && strpos($row[0], ',') !== false) {
                    $row = str_getcsv($row[0]);
                }

                // Skip jika kolom kurang dari 5 (Asal, Tujuan, Kurir, Layanan, Tarif)
                if(count($row) < 5) continue; 

                // Mapping 8 Kolom:
                // [0] Origin
                // [1] Destination
                // [2] Courier (JNE)
                // [3] Service (REG)
                // [4] Rate
                // [5] Min Weight
                // [6] Min Day
                // [7] Max Day

                $website->shippingRates()->create([
                    'origin_city'      => trim($row[0]),
                    'destination_city' => trim($row[1]),
                    'courier_name'     => trim($row[2]),
                    'service_name'     => trim($row[3]), // Mengambil kolom Layanan
                    'rate_per_kg'      => intval(preg_replace('/[^0-9]/', '', $row[4])),
                    'min_weight'       => isset($row[5]) && is_numeric($row[5]) ? intval($row[5]) : 1,
                    'min_day'          => isset($row[6]) && is_numeric($row[6]) ? intval($row[6]) : null,
                    'max_day'          => isset($row[7]) && is_numeric($row[7]) ? intval($row[7]) : null,
                ]);
                $count++;
            }
            
            DB::commit();
            fclose($handle);
            return back()->with('success', "Import berhasil! {$count} data tarif ditambahkan.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }
}