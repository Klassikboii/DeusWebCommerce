<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Website; // 🚨 PENTING: Panggil Model Website
use App\Models\WalletMutation;
use App\Models\Withdrawal;

class WalletController extends Controller
{
    // 🚨 Tangkap $website dari URL: manage/{website}/wallet
    public function index(Website $website)
    {
        $mutations = $website->walletMutations()->latest()->paginate(10);
        $withdrawals = $website->withdrawals()->latest()->paginate(10);

        return view('client.wallet.index', compact('website', 'mutations', 'withdrawals'));
    }

    // 🚨 Tangkap $website dari URL juga untuk aksi withdraw
    public function withdraw(Request $request, Website $website)
    {
        $request->validate([
            'amount' => 'required|numeric|min:50000',
        ], [
            'amount.min' => 'Minimal penarikan dana adalah Rp 50.000'
        ]);

        $amount = $request->amount;

        if ($website->wallet_balance < $amount) {
            return back()->with('error', 'Saldo Anda tidak mencukupi untuk penarikan ini.');
        }

        if (empty($website->bank_name) || empty($website->bank_account_number) || empty($website->bank_account_holder)) {
            return back()->with('error', 'Silakan lengkapi data rekening bank Anda di Pengaturan Toko terlebih dahulu.');
        }

        try {
            DB::transaction(function () use ($website, $amount) {
                $website->decrement('wallet_balance', $amount);

                WalletMutation::create([
                    'website_id' => $website->id,
                    'type' => 'debit',
                    'amount' => $amount,
                    'description' => "Penarikan dana ke {$website->bank_name} ({$website->bank_account_number})"
                ]);

                Withdrawal::create([
                    'website_id' => $website->id,
                    'amount' => $amount,
                    'bank_name' => $website->bank_name,
                    'bank_account_number' => $website->bank_account_number,
                    'bank_account_name' => $website->bank_account_holder,
                    'status' => 'pending'
                ]);
            });

            return back()->with('success', 'Permintaan penarikan dana berhasil dikirim dan sedang diproses oleh tim kami.');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}