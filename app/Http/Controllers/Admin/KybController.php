<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MerchantKybDetail;
use Illuminate\Http\Request;

class KybController extends Controller
{
    // 1. Menampilkan daftar antrean verifikasi
    public function index()
    {
        // Ambil data KYB yang masih pending di urutan teratas
        $submissions = MerchantKybDetail::with('website')
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->latest()
            ->paginate(20);

        return view('admin.kyb.index', compact('submissions'));
    }

    // 2. Menampilkan detail data untuk di-copy paste ke Pivot
    public function show($id)
    {
        $kyb = MerchantKybDetail::with('website')->findOrFail($id);
        
        return view('admin.kyb.show', compact('kyb'));
    }

    // 3. Memproses Approval dan menyimpan Sub-Account ID dari Pivot
    public function approve(Request $request, $id)
{
    $request->validate([
        'pivot_sub_account_id' => 'required',
        'merchant_id' => 'required',
        'merchant_secret' => 'required',
    ]);

    $kyb = MerchantKybDetail::findOrFail($id);
    $kyb->update([
        'status' => 'approved',
        'pivot_sub_account_id' => $request->pivot_sub_account_id,
        'merchant_id' => $request->merchant_id,
        'merchant_secret' => $request->merchant_secret,
        'callback_url' => $request->callback_url,
    ]);

    return redirect()->route('admin.kyb.index')->with('success', 'Toko Berhasil Diaktifkan!');
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