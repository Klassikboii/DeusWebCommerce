<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('vouchers', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('website_id'); // Kunci Multi-Tenant
        
        // IDENTITAS VOUCHER
        $table->string('code')->index(); // Contoh: DEUS20, PROMO-VIP
        $table->enum('discount_type', ['nominal', 'percent'])->default('nominal');
        $table->decimal('discount_value', 15, 2); // Nominal Rp atau Angka %
        
        // BATASAN (CAP & SYARAT)
        $table->decimal('max_discount_amount', 15, 2)->nullable(); // Maksimal potongan (untuk persen)
        $table->decimal('min_purchase', 15, 2)->default(0); // Syarat minimal belanja
        
        // KUOTA & WAKTU
        $table->integer('max_uses')->nullable(); // Null = Kuota tak terbatas
        $table->integer('used_count')->default(0); // Menghitung berapa kali sudah dipakai
        $table->dateTime('valid_from')->nullable();
        $table->dateTime('valid_until')->nullable(); // Null = Tidak pernah expired
        
        // PRIVILEGE (KUNCI RFM / CUSTOMER)
        $table->string('target_rfm_segment')->nullable(); // Contoh: 'At Risk', 'Champions'
        $table->unsignedBigInteger('target_customer_id')->nullable(); // Khusus untuk 1 orang
        
        // STATUS
        $table->boolean('is_active')->default(true);
        $table->timestamps();

        // RELASI
        $table->foreign('website_id')->references('id')->on('websites')->onDelete('cascade');
        $table->foreign('target_customer_id')->references('id')->on('customers')->onDelete('set null');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
