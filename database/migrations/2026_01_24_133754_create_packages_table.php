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
    Schema::create('packages', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // Contoh: "Starter", "Pro", "Business"
        $table->decimal('price', 15, 2); // Harga bulanan (Rp 0 sampai Rp Juta-an)
        $table->integer('duration_days')->default(30); // Durasi (biasanya 30 hari)
        
        // --- BATASAN FITUR (LIMITS) ---
        $table->integer('max_products')->default(10); // Limit jumlah produk
        $table->boolean('can_custom_domain')->default(false); // Boleh pakai domain sendiri?
        $table->boolean('remove_branding')->default(false); // Boleh hapus tulisan "Powered by"?
        
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('packages');
}
};
