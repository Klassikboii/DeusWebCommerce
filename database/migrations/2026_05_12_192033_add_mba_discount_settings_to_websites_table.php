<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            // Kita pakai decimal agar klien bisa input 2.5%
            $table->decimal('mba_perfect_discount', 5, 2)->default(0)->after('dashboard_preferences');
            $table->decimal('mba_cross_discount', 5, 2)->default(0)->after('mba_perfect_discount');
        });
    }

    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropColumn(['mba_perfect_discount', 'mba_cross_discount']);
        });
    }
};