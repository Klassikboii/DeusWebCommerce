<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shipping_markups', function (Blueprint $table) {
            $table->id();
            // Milik toko/website siapa aturan ini?
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            
            // Berlaku untuk tujuan kota mana?
            $table->unsignedBigInteger('city_id'); 
            
            // Tipe tambahan harga: Mau tambah nominal (Rp) atau persen (%)
            $table->enum('markup_type', ['nominal', 'percent'])->default('nominal');
            
            // Jumlah keuntungannya
            $table->decimal('markup_value', 15, 2)->default(0); 
            $table->timestamps();

            // Relasi ke tabel cities
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_markups');
    }
};
