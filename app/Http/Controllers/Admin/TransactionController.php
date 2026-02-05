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
        // 1. Validasi Input
        $request->validate([
            'status' => 'required|in:approved,rejected'
        ]);

        // Cek status lama agar tidak double approve
        if ($transaction->status == 'approved' && $request->status == 'approved') {
            return back()->with('error', 'Transaksi ini sudah disetujui sebelumnya.');
        }

        // 2. Update Status Transaksi
        $transaction->update([
            'status' => $request->status
        ]);
        
        // 3. LOGIKA APPROVED -> PERPANJANG / AKTIFKAN
        if ($request->status == 'approved') {
            
            // Cari Website Terkait
            $website = $transaction->website; // Pastikan relasi di model Transaction ada: return $this->belongsTo(Website::class);
            
            if ($website) {
                // Cek apakah ada langganan yang sedang aktif (atau baru expired)
                $activeSub = Subscription::where('website_id', $website->id)
                                ->latest('ends_at') // Ambil yang paling terakhir berakhir
                                ->first();

                $daysToAdd = $transaction->package->duration_days;

                if ($activeSub && $activeSub->ends_at > now()->subDays(3)) { 
                    // SKENARIO A: PERPANJANG (RENEWAL/UPGRADE)
                    // Jika langganan masih aktif atau baru saja mati (toleransi 3 hari)
                    
                    // Tentukan tanggal mulai perpanjangan:
                    // Jika belum expired, tambah dari tanggal expired. 
                    // Jika sudah expired, tambah dari hari ini.
                    $newEndDate = $activeSub->ends_at->isFuture() 
                                    ? $activeSub->ends_at->addDays($daysToAdd) 
                                    : now()->addDays($daysToAdd);

                    $activeSub->update([
                        'status' => 'active', // Pastikan aktif
                        'package_id' => $transaction->package_id, // Update ke paket baru (jika upgrade)
                        'ends_at' => $newEndDate
                    ]);
                    
                    $message = "Langganan berhasil diperpanjang hingga " . $newEndDate->format('d M Y');

                } else {
                    // SKENARIO B: BUAT BARU (NEW/SUDAH LAMA MATI)
                    // Matikan semua sub lama (jaga-jaga)
                    Subscription::where('website_id', $website->id)->update(['status' => 'expired']);

                    Subscription::create([
                        'website_id' => $transaction->website_id,
                        'package_id' => $transaction->package_id,
                        'status' => 'active',
                        'starts_at' => now(),
                        'ends_at' => now()->addDays($daysToAdd),
                    ]);

                    $message = "Paket baru berhasil diaktifkan.";
                }
            }
        } else {
            $message = 'Transaksi ditolak.';
        }

        return redirect()->back()->with('success', $message);
    }
}