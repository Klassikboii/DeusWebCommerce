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
       Schema::table('merchant_kyb_details', function (Blueprint $table) {
    $table->string('merchant_id')->nullable();
    $table->string('merchant_secret')->nullable();
    $table->string('callback_url')->nullable();
    // sub_account_id sudah ada di migration sebelumnya
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kyb', function (Blueprint $table) {
            //
        });
    }
};
