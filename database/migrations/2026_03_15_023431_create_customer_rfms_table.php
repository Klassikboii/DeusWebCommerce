<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_rfms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            
            // Identitas Pelanggan (Kita pakai WA sebagai ID unik karena di E-Commerce nomor WA adalah identitas paling valid)
            $table->string('customer_whatsapp');
            $table->string('customer_name')->nullable();
            
            // Data Mentah RFM
            $table->integer('recency_days');      // Berapa hari sejak order terakhir?
            $table->integer('frequency_count');   // Berapa kali belanja?
            $table->decimal('monetary_value', 15, 2); // Total uang yang dihabiskan
            
            // Skor Data Science (1 - 5)
            $table->integer('r_score');
            $table->integer('f_score');
            $table->integer('m_score');
            
            // Hasil Prediksi Segmen
            $table->string('segment'); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_rfms');
    }
};