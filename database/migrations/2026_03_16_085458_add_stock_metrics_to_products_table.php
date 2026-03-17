<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Kecepatan barang keluar (Rata-rata item per hari)
            $table->decimal('velocity', 8, 4)->default(0)->after('stock');
            
            // Prediksi sisa hari sampai stok habis
            $table->integer('runway_days')->nullable()->after('velocity');
            
            // Status cerdas (Safe, Critical, Overstock, Empty)
            $table->string('stock_status')->default('Normal')->after('runway_days');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['velocity', 'runway_days', 'stock_status']);
        });
    }
};