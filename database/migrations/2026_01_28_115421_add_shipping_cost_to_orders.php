<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('orders', function (Blueprint $table) {
        // Default 0 agar tidak error hitungan matematika
        $table->decimal('shipping_cost', 15, 2)->default(0)->after('total_amount');
        
        // Opsional: Kolom untuk mencatat "Layanan" (misal: JNE REG)
        $table->string('courier_service')->nullable()->after('courier_name'); 
    });
}

public function down()
{
    Schema::table('orders', function (Blueprint $table) {
        $table->dropColumn(['shipping_cost', 'courier_service']);
    });
}
};
