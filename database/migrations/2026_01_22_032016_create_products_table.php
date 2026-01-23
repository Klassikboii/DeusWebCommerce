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
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->foreignId('website_id')->constrained()->onDelete('cascade'); // Wajib: Produk ini milik website mana
        $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null'); // Opsional: Kategorinya apa
        
        $table->string('name');
        $table->string('slug');
        $table->string('sku')->nullable(); // Kode stok barang (opsional)
        
        $table->decimal('price', 12, 0); // Harga (maksimal ratusan miliar)
        $table->integer('stock')->default(0); // Stok
        $table->integer('weight')->default(0); // Berat (gram) untuk ongkir
        
        $table->text('description')->nullable();
        $table->string('image')->nullable(); // URL Gambar Utama
        
        $table->boolean('is_active')->default(true); // Status: Aktif/Arsip
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
