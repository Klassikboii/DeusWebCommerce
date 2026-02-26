<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accurate_integrations', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel websites (jika website dihapus, data integrasi ikut terhapus)
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            
            // ID Database Accurate (karena 1 akun Accurate bisa punya banyak cabang/database)
            $table->string('accurate_database_id')->nullable(); 
            
            // Kredensial OAuth
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accurate_integrations');
    }
};