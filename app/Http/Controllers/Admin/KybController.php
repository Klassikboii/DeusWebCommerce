<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MerchantKybDetail;
use App\Services\Payment\PivotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KybController extends Controller
{
    protected $pivotService;
    public function __construct(PivotService $pivotService)
    {
        $this->pivotService = $pivotService;
    }
    // 1. Menampilkan daftar antrean verifikasi
    public function index()
    {
        // 🚨 UBAH dengan('website') menjadi with('user')
        $submissions = MerchantKybDetail::with('user')
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->latest()
            ->paginate(20);

        return view('admin.kyb.index', compact('submissions'));
    }

    // 2. Menampilkan detail data untuk di-copy paste ke Pivot
    public function show($id)
    {
        // 🚨 UBAH dengan('website') menjadi with('user')
        $kyb = MerchantKybDetail::with('user')->findOrFail($id);
        
        return view('admin.kyb.show', compact('kyb'));
    }
public function approve(Request $request, $id, PivotService $pivotService)
    {
        $kyb = MerchantKybDetail::findOrFail($id);

        // 🚨 JIKA SUPERADMIN KLIK TOMBOL "AUTO"
        if ($request->input('action') === 'auto') {
            try {
                // Panggil API Pivot
                $pivotResponse = $pivotService->createSubAccount($kyb);
                
                // Ambil ID dari response Pivot
                $subAccountId = $pivotResponse['id'] ?? null;

                if (!$subAccountId) {
                    throw new \Exception('ID Sub-Account tidak ditemukan pada respons server Pivot.');
                }

                $kyb->update([
                    'status' => 'approved',
                    'pivot_sub_account_id' => $subAccountId,
                    // Note: Jika API otomatis juga me-return merchant_id/secret, masukkan di sini
                ]);

                return redirect()->route('admin.kyb.index')
                    ->with('success', "Toko Berhasil Diaktifkan Otomatis! (Sub-Account ID: {$subAccountId})");

            } catch (\Exception $e) {
                Log::error('API Pivot Error: ' . $e->getMessage());
                // Kembali ke halaman sebelumnya dan tampilkan pesan error API
                return back()->with('error', 'Gagal memanggil API Pivot: ' . $e->getMessage() . '. Silakan gunakan fallback manual.');
            }
        } 
        
        // 🚨 JIKA SUPERADMIN KLIK TOMBOL "MANUAL"
        else {
            $request->validate([
                // 'pivot_sub_account_id' => 'required',
                'merchant_id' => 'required',
                // 'merchant_secret' => 'required',
            ]);

            $kyb->update([
                'status' => 'approved',
                'pivot_sub_account_id' => $request->pivot_sub_account_id,
                'merchant_id' => $request->merchant_id,
                'merchant_secret' => $request->merchant_secret,
                'callback_url' => $request->callback_url,
            ]);

            return redirect()->route('admin.kyb.index')
                ->with('success', 'Toko Berhasil Diaktifkan secara Manual!');
        }
    }

    // 4. Memproses Penolakan (Misal: Rekening tidak valid)
    public function reject(Request $request, $id)
    {
        $kyb = MerchantKybDetail::findOrFail($id);
        $kyb->update([
            'status' => 'rejected'
        ]);

        return redirect()->route('admin.kyb.index')->with('success', 'Pengajuan KYB ditolak.');
    }
}