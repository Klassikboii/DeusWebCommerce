<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::create('shipping_rates', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('website_id');
        
        // KEMBALIKAN KOTA ASAL
        $table->string('origin_city')->nullable(); 
        $table->string('destination_city'); 
        
        $table->string('courier_name'); // Contoh: "JNE Reg" (Gabungan di CSV)
        $table->string('service_name')->nullable(); // Kita biarkan nullable (opsional)
        
        $table->decimal('rate_per_kg', 12, 0); 
        $table->integer('min_weight')->default(1); 
        
        $table->integer('min_day')->nullable();
        $table->integer('max_day')->nullable(); 
        
        $table->timestamps();

        $table->foreign('website_id')->references('id')->on('websites')->onDelete('cascade');
    });
}

    public function down()
    {
        Schema::dropIfExists('shipping_rates');
    }
};