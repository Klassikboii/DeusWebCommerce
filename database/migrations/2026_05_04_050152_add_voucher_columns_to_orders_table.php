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
        // Tambahkan setelah kolom total_amount atau shipping_cost
        $table->unsignedBigInteger('voucher_id')->nullable()->after('shipping_cost');
        $table->decimal('discount_amount', 15, 2)->default(0)->after('voucher_id');
        
        // Opsional: Buat relasi foreign key
        $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('set null');
    });
}

public function down()
{
    Schema::table('orders', function (Blueprint $table) {
        $table->dropForeign(['voucher_id']);
        $table->dropColumn(['voucher_id', 'discount_amount']);
    });
}
};
