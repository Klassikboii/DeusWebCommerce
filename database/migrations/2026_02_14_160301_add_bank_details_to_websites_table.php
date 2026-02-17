<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->string('bank_name')->nullable()->after('address'); // BCA, Mandiri
            $table->string('bank_account_number')->nullable()->after('bank_name'); // 1234567890
            $table->string('bank_account_holder')->nullable()->after('bank_account_number'); // PT Deus Commerce
        });
    }

    public function down()
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'bank_account_number', 'bank_account_holder']);
        });
    }
};