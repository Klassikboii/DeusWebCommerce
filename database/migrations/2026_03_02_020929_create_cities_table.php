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
        Schema::create('cities', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary(); // ID asli RajaOngkir
            $table->unsignedBigInteger('province_id');
            $table->string('type'); // Menyimpan tipe: 'Kabupaten' atau 'Kota'
            $table->string('name');
            $table->string('postal_code')->nullable();
            $table->timestamps();

            // Relasi ke tabel provinces
            $table->foreign('province_id')->references('id')->on('provinces')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
