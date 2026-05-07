<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if(!Schema::hasColumn('products', 'moving_class')){
        // 1. Suntik ke tabel produk tunggal
        Schema::table('products', function (Blueprint $table) {
            // Kolom input manual Klien (Domain Knowledge)
            $table->string('moving_class')->default('normal')->after('stock'); // Pilihan: fast, normal, slow
            
            // // Kolom hasil hitungan Mesin (Data Facts)
            // $table->decimal('velocity', 8, 4)->nullable()->after('moving_class'); // Kecepatan jual per hari (bisa desimal)
            // $table->integer('runway_days')->nullable()->after('velocity');        // Sisa hari sebelum habis
            // $table->string('stock_status')->default('Safe')->after('runway_days'); // Safe, Warning, Critical, Empty, Overstock
        });
         if(!Schema::hasColumn('product_variants', 'moving_class')){
        // 2. Suntik ke tabel produk varian (Karena stok varian dihitung terpisah)
        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('moving_class')->default('normal')->after('stock');
            // $table->swring('stock_status')->default('Safe')->after('runway_days');
        });
         }
        }
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['moving_class']);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['moving_class']);
        });
    }
};