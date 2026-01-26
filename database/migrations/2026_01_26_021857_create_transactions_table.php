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
    Schema::create('transactions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Siapa yang bayar
        $table->foreignId('website_id')->constrained()->cascadeOnDelete(); // Untuk toko mana
        $table->foreignId('package_id')->constrained(); // Paket yang dibeli
        
        $table->decimal('amount', 15, 2); // Jumlah uang
        $table->string('status')->default('pending'); // pending, approved, rejected
        $table->string('proof_image')->nullable(); // Foto bukti transfer
        
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('transactions');
}
};
