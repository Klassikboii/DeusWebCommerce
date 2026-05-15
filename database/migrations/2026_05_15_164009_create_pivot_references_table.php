<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel untuk Industri
        Schema::create('pivot_industries', function (Blueprint $table) {
            $table->id();
            $table->string('parent_industry');
            $table->string('child_industry');
            $table->string('mcc');
            $table->timestamps();
        });

        // Tabel untuk Kecamatan (Districts)
        Schema::create('pivot_districts', function (Blueprint $table) {
            // Kita set 'id' persis sesuai District ID dari Pivot agar mudah
            $table->unsignedBigInteger('id')->primary(); 
            $table->unsignedBigInteger('city_id');
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pivot_districts');
        Schema::dropIfExists('pivot_industries');
    }
};