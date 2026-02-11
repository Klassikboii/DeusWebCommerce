<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel products utama
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // Nama variasi gabungan (opsional, tapi memudahkan display)
            // Contoh: "Merah - L" atau "Blue - 128GB"
            $table->string('name')->nullable();
            
            // Data JSON untuk menyimpan opsi (Kunci agar fleksibel!)
            // Contoh isi: {"Warna": "Merah", "Ukuran": "L"}
            // Ini memungkinkan kita memfilter nanti.
            $table->json('options'); 
            
            // Data spesifik varian (Jika null, bisa fallback ke parent product)
            $table->string('sku')->nullable();
            $table->integer('stock')->default(0);
            $table->decimal('price', 12, 0)->nullable();
            $table->integer('weight')->nullable(); // Berat bisa beda per ukuran
            $table->string('image')->nullable();   // Gambar spesifik varian (misal: baju merah)
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_variants');
    }
};