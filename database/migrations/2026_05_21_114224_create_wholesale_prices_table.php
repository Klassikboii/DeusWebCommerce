<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wholesale_prices', function (Blueprint $table) {
            $table->id();
            // Relasi ke produk utama (wajib)
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            
            // Relasi ke varian (opsional, jika grosir spesifik untuk varian tertentu)
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            
            $table->integer('min_qty'); // Minimal jumlah beli
            $table->decimal('price', 15, 2); // Harga grosir per item (sesuaikan tipe datanya dengan kolom price di tabel products Anda)
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wholesale_prices');
    }
};