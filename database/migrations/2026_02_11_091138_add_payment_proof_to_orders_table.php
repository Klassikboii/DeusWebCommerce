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
        $table->string('payment_proof')->nullable()->after('status');
        $table->string('bank_name')->nullable()->after('payment_proof');
        // Ubah status enum jika perlu, atau kita pakai status 'pending' -> 'paid' -> 'cancelled'
    });
}

public function down()
{
    Schema::table('orders', function (Blueprint $table) {
        $table->dropColumn(['payment_proof', 'bank_name']);
    });
}
};
