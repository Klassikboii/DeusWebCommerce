<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only add deleted_at if it doesn't exist
        if (!Schema::hasColumn('vouchers', 'deleted_at')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        // Only drop deleted_at if it exists
        if (Schema::hasColumn('vouchers', 'deleted_at')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};