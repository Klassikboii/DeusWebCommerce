<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Withdrawal;
use App\Models\WalletMutation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WithdrawalController extends Controller
{
    // 1. Tampilkan semua antrean penarikan dana
    public function index()
    {
        // Ambil data penarikan beserta data toko/website-nya, urutkan yang terbaru
        $withdrawals = Withdrawal::with('website')->latest()->paginate(20);
        
        return view('admin.withdrawals.index', compact('withdrawals'));
    }

    // 2. Logika Menyetujui Penarikan (Admin sudah transfer uangnya)
    public function approve(Request $request, Withdrawal $withdrawal)
    {
        $request->validate([
            'transfer_proof' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Wajib upload bukti transfer
            'admin_note' => 'nullable|string|max:255'
        ]);

        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Status penarikan ini sudah diproses sebelumnya.');
        }

        try {
            // Simpan foto bukti transfer ke folder storage/app/public/withdrawals
            $proofPath = $request->file('transfer_proof')->store('withdrawals', 'public');

            $withdrawal->update([
                'status' => 'approved',
                'transfer_proof' => $proofPath,
                'admin_note' => $request->admin_note
            ]);

            return back()->with('success', 'Penarikan dana berhasil disetujui dan bukti transfer telah disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses persetujuan: ' . $e->getMessage());
        }
    }

    // 3. Logika Menolak Penarikan (Kembalikan uang ke saldo klien)
    public function reject(Request $request, Withdrawal $withdrawal)
    {
        $request->validate([
            'admin_note' => 'required|string|max:255' // Wajib beri alasan penolakan
        ]);

        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Status penarikan ini sudah diproses sebelumnya.');
        }

        try {
            DB::transaction(function () use ($withdrawal, $request) {
                // A. Ubah status jadi ditolak
                $withdrawal->update([
                    'status' => 'rejected',
                    'admin_note' => $request->admin_note
                ]);

                // B. KEMBALIKAN UANG KE SALDO KLIEN (Refund)
                $withdrawal->website->increment('wallet_balance', $withdrawal->amount);

                // C. Catat mutasi pengembalian dana
                WalletMutation::create([
                    'website_id' => $withdrawal->website_id,
                    'type' => 'credit',
                    'amount' => $withdrawal->amount,
                    'description' => "Pengembalian dana (Penarikan ditolak): {$request->admin_note}"
                ]);
            });

            return back()->with('success', 'Penarikan dana ditolak. Saldo telah dikembalikan ke dompet klien.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses penolakan: ' . $e->getMessage());
        }
    }
}