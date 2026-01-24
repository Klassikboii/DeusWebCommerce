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
    Schema::table('websites', function (Blueprint $table) {
        // Kita simpan array menu dalam bentuk JSON
        $table->json('navigation_menu')->nullable()->after('domain_status');
    });
}

public function down(): void
{
    Schema::table('websites', function (Blueprint $table) {
        $table->dropColumn('navigation_menu');
    });
}
};
