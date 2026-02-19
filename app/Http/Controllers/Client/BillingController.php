<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Models\Package;
use App\Models\Transaction;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index(Website $website)
    {
        $this->authorize('viewAny', $website);

        // Ambil paket yang sedang aktif
        $currentSubscription = $website->activeSubscription;

        // Ambil semua pilihan paket berbayar (kecuali free)
        $packages = Package::where('price', '>', 0)->get();

        // Cek apakah ada transaksi pending (menunggu konfirmasi)
        $pendingTransaction = Transaction::where('website_id', $website->id)
                                         ->where('status', 'pending')
                                         ->latest()
                                         ->first();
        // Ambil data user Super Admin pertama di database
        $admin = \App\Models\User::where('role', 'admin')->first();
        return view('client.billing.index', compact('website', 'currentSubscription', 'packages', 'pendingTransaction', 'admin'));
    }

    public function store(Request $request, Website $website)
    {
        $this->authorize('viewAny', $website);

        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'proof_image' => 'required|image|max:2048', // Bukti transfer
        ]);

        $package = Package::find($request->package_id);

        // Simpan Transaksi
        Transaction::create([
            'user_id' => auth()->id(),
            'website_id' => $website->id,
            'package_id' => $package->id,
            'amount' => $package->price,
            'status' => 'pending',
            'proof_image' => $request->file('proof_image')->store('payment_proofs', 'public'),
        ]);

        return redirect()->back()->with('success', 'Bukti pembayaran dikirim! Mohon tunggu verifikasi Admin.');
    }
}