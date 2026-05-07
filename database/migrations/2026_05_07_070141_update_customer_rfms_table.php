<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_rfms', function (Blueprint $table) {
            // Hapus data lama agar tidak bentrok saat mengubah struktur
            \Illuminate\Support\Facades\DB::table('customer_rfms')->truncate();

            // Hapus kolom yang tidak berguna
            $table->dropColumn(['customer_whatsapp', 'customer_name']);
            
            // Tambahkan relasi ID (Pastikan tipe datanya sama dengan id di tabel customers, biasanya unsignedBigInteger)
            $table->foreignId('customer_id')->after('website_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customer_rfms', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
            $table->string('customer_whatsapp')->nullable();
            $table->string('customer_name')->nullable();
        });
    }
};