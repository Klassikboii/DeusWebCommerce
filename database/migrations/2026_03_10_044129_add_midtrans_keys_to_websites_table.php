<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->string('midtrans_client_key')->nullable()->after('subdomain');
            $table->string('midtrans_server_key')->nullable()->after('midtrans_client_key');
            $table->boolean('midtrans_is_production')->default(false)->after('midtrans_server_key');
        });
    }

    public function down()
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropColumn([
                'midtrans_client_key',
                'midtrans_server_key',
                'midtrans_is_production'
            ]);
        });
    }
};