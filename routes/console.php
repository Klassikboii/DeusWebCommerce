<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('subscription:check')->daily();

// Jalankan perintah cek order setiap jam
Schedule::command('orders:cancel-unpaid')->hourly();

// Menjalankan analisis kecepatan stok setiap 3 jam 
// (Agar jika ada transaksi besar siang hari, status stok cepat terupdate)
Schedule::command('stock:analyze')->everyThreeHours();

Schedule::command('accurate:renew-webhooks')->cron('0 2 */3 * *');

