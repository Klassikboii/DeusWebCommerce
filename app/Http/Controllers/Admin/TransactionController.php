<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Subscription;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        // Tampilkan transaksi terbaru dulu
        $transactions = Transaction::with(['user', 'website', 'package'])->latest()->get();
        return view('admin.transactions.index', compact('transactions'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        // Validasi input (hanya boleh approved atau rejected)
        $request->validate([
            'status' => 'required|in:approved,rejected'
        ]);

        // 1. Update Status Transaksi
        $transaction->update([
            'status' => $request->status
        ]);

        // 2. JIKA APPROVED -> AKTIFKAN PAKET LANGGANAN
        if ($request->status == 'approved') {
            
            // Nonaktifkan langganan lama (jika ada)
            Subscription::where('website_id', $transaction->website_id)
                        ->where('status', 'active')
                        ->update(['status' => 'expired']);

            // Buat Langganan Baru
            Subscription::create([
                'website_id' => $transaction->website_id,
                'package_id' => $transaction->package_id,
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => now()->addDays($transaction->package->duration_days),
            ]);
            
            $message = 'Transaksi disetujui! Paket Pro berhasil diaktifkan.';
        } else {
            $message = 'Transaksi ditolak.';
        }

        return redirect()->back()->with('success', $message);
    }
}