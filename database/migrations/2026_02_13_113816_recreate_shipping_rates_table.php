<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Pastikan tabel tidak ada sebelum membuatnya
        Schema::dropIfExists('shipping_rates');

        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('website_id');
            
            // Struktur Lengkap (Sesuai CSV terakhir)
            $table->string('origin_city');       // Kota Asal
            $table->string('destination_city');  // Kota Tujuan
            $table->string('courier_name');      // JNE
            $table->string('service_name')->nullable(); // REG
            
            $table->decimal('rate_per_kg', 12, 0); // Harga
            $table->integer('min_weight')->default(1); 
            
            $table->integer('min_day')->nullable(); // Est Min
            $table->integer('max_day')->nullable(); // Est Max
            
            $table->timestamps();

            // Index biar pencarian cepat
            $table->index(['website_id', 'destination_city']);
            $table->foreign('website_id')->references('id')->on('websites')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipping_rates');
    }
};