<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_recommendations', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke Website (SaaS)
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            
            // Relasi ke Produk Pemicu
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            
            // Relasi ke Produk Rekomendasi
            $table->foreignId('recommended_product_id')->constrained('products')->cascadeOnDelete();
            
            // Metrik Algoritma Apriori
            $table->decimal('support', 8, 4)->default(0);
            $table->decimal('confidence', 8, 4)->default(0);
            $table->decimal('lift', 8, 4)->default(0);

            $table->timestamps();

            // Mencegah duplikasi data: Kombinasi Product A dan B hanya boleh ada 1 baris per website
            $table->unique(['website_id', 'product_id', 'recommended_product_id'], 'unique_recommendation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_recommendations');
    }
};