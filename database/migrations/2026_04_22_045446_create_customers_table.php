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
    Schema::create('customers', function (Blueprint $table) {
        $table->id();
        $table->foreignId('website_id')->constrained()->onDelete('cascade');
        $table->string('name');
        $table->string('whatsapp')->nullable();
        $table->string('email')->nullable();
        $table->string('password'); // Untuk login nanti
        $table->rememberToken();
        $table->timestamps();

        // Unique per website: satu email hanya bisa daftar sekali di toko yang sama
        $table->unique(['website_id', 'email']);
        $table->unique(['website_id', 'whatsapp']);
    });

    // Tambahkan kolom customer_id ke tabel orders
    Schema::table('orders', function (Blueprint $table) {
        $table->foreignId('customer_id')->nullable()->after('website_id')->constrained();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
