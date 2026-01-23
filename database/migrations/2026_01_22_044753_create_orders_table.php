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
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->foreignId('website_id')->constrained()->onDelete('cascade'); // Order milik toko mana
        
        $table->string('order_number')->unique(); // No Invoice (misal: INV-202601-001)
        
        // Data Pelanggan (Guest Checkout dulu)
        $table->string('customer_name');
        $table->string('customer_whatsapp'); // Penting untuk notifikasi
        $table->text('customer_address')->nullable();
        
        // Data Keuangan
        $table->decimal('total_amount', 12, 0);
        $table->string('payment_method')->default('transfer'); // transfer / cod
        $table->string('payment_status')->default('unpaid'); // unpaid / paid
        
        // Status Order
        $table->enum('status', ['pending', 'processing', 'shipped', 'completed', 'cancelled'])->default('pending');
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
