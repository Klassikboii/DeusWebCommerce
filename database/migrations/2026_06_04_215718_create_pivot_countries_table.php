<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pivot_countries', function (Blueprint $table) {
            $table->id();
            $table->string('code', 5)->unique(); // Menyimpan ISO Code (misal: ID, SG)
            $table->string('name'); // Menyimpan Nama Negara
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pivot_countries');
    }
};