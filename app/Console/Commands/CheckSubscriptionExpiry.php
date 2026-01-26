<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use Carbon\Carbon;

class CheckSubscriptionExpiry extends Command
{
    // Nama perintah yang nanti kita ketik di terminal
    protected $signature = 'subscription:check';

    // Penjelasan perintah
    protected $description = 'Cek langganan yang sudah expired dan nonaktifkan';

    public function handle()
    {
        $this->info('Memulai pengecekan langganan...');

        // 1. Cari langganan yang Statusnya 'Active' TAPI Tanggalnya sudah lewat hari ini
        $expiredSubs = Subscription::where('status', 'active')
                                   ->where('ends_at', '<', Carbon::now())
                                   ->get();

        $count = 0;

        foreach ($expiredSubs as $sub) {
            // 2. Ubah status jadi 'expired'
            $sub->update(['status' => 'expired']);
            
            // Opsional: Kirim email notifikasi ke user "Paket Anda habis!" (Nanti)
            
            $this->info("Langganan ID {$sub->id} (Website: {$sub->website_id}) telah dinonaktifkan.");
            $count++;
        }

        $this->info("Selesai! Total {$count} langganan diproses.");
    }
}