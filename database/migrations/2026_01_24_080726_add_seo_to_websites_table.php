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
        $table->string('meta_title')->nullable()->after('domain_status'); // Judul di Tab Browser / Google
        $table->string('meta_description')->nullable()->after('meta_title'); // Deskripsi di bawah judul Google
        $table->string('meta_keywords')->nullable()->after('meta_description'); // (Opsional) Kata kunci
    });
}

public function down(): void
{
    Schema::table('websites', function (Blueprint $table) {
        $table->dropColumn(['meta_title', 'meta_description', 'meta_keywords']);
    });
}
};
