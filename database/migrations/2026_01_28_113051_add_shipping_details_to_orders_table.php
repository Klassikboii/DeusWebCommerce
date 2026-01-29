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
        // Menambah kolom kurir dan resi
        $table->string('courier_name')->nullable()->after('total_amount'); // Misal: JNE, J&T
        $table->string('tracking_number')->nullable()->after('courier_name'); // No Resi
        
        // Pastikan kolom status mendukung enum/string yang kita butuhkan
        // (Jika Anda sudah punya kolom status, pastikan tipenya string/enum yang cukup panjang)
    });
}

public function down()
{
    Schema::table('orders', function (Blueprint $table) {
        $table->dropColumn(['courier_name', 'tracking_number']);
    });
}
};
