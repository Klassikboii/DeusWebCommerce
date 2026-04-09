<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            // Kita tambahkan kolom boolean untuk saklar fitur
            $table->boolean('has_ai_insights')->default(false)->after('remove_branding');
            $table->boolean('has_custom_dashboard')->default(false)->after('has_ai_insights');
            $table->boolean('has_shipping_markup')->default(false)->after('has_custom_dashboard');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['has_ai_insights', 'has_custom_dashboard', 'has_shipping_markup']);
        });
    }
};