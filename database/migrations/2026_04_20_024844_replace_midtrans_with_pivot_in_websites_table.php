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
        Schema::table('websites', function (Blueprint $table) {
            // 1. Buang kunci Midtrans lama
            $table->dropColumn([
                'midtrans_server_key', 
                'midtrans_client_key', 
                'midtrans_is_production'
            ]);

            // 2. Tambahkan kunci Pivot baru (letakan setelah kolom 'navigation_menu' agar rapi)
            $table->string('pivot_server_key')->nullable()->after('navigation_menu');
            $table->string('pivot_client_key')->nullable()->after('pivot_server_key');
            $table->boolean('pivot_is_production')->default(false)->after('pivot_client_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            // Kembalikan Midtrans jika kita ingin membatalkan migrasi (Rollback)
            $table->string('midtrans_server_key')->nullable();
            $table->string('midtrans_client_key')->nullable();
            $table->boolean('midtrans_is_production')->default(false);

            // Buang Pivot
            $table->dropColumn([
                'pivot_server_key', 
                'pivot_client_key', 
                'pivot_is_production'
            ]);
        });
    }
};