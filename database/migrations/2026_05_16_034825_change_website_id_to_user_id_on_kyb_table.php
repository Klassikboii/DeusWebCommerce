<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merchant_kyb_details', function (Blueprint $table) {
            // 1. Hapus relasi ke website
            $table->dropForeign(['website_id']);
            $table->dropColumn('website_id');
            
            // 2. Tambahkan relasi ke user (klien)
            $table->foreignId('user_id')->after('id')->nullable()->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('merchant_kyb_details', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->foreignId('website_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }
};