<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            // Menyimpan preferensi susunan widget dashboard dalam bentuk JSON
            $table->json('dashboard_preferences')->nullable()->after('theme_config');
        });
    }

    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropColumn('dashboard_preferences');
        });
    }
};