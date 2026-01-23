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
        // Defaultnya kita set 'modern' (template yang sudah kita buat)
        $table->string('active_template')->default('modern')->after('subdomain');
    });
}

public function down(): void
{
    Schema::table('websites', function (Blueprint $table) {
        $table->dropColumn('active_template');
    });
}
};
