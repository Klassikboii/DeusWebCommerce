<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Tambah Koordinat Toko di tabel websites
        Schema::table('websites', function (Blueprint $table) {
            // Koordinat default (contoh: Surabaya Pusat)
            $table->double('latitude')->nullable()->default(-7.2575); 
            $table->double('longitude')->nullable()->default(112.7521);
        });

        // 2. Hapus tabel ongkir lama
        Schema::dropIfExists('shipping_rates');

        // 3. Buat tabel ongkir baru (Sistem Range KM)
        Schema::create('shipping_ranges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('website_id');
            
            $table->double('min_km'); // 0
            $table->double('max_km'); // 5
            $table->decimal('price', 12, 0); // 10000
            
            $table->timestamps();
            
            $table->foreign('website_id')->references('id')->on('websites')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipping_ranges');
        Schema::table('websites', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
        // Note: Tabel shipping_rates lama tidak dikembalikan di down() karena ribet, 
        // asumsikan kita move on total.
    }
};