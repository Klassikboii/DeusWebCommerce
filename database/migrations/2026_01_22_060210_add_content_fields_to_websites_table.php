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
            // Kita tambah kolom untuk teks Hero Banner
            $table->string('hero_title')->nullable()->after('secondary_color');
            $table->string('hero_subtitle')->nullable()->after('hero_title');
            $table->string('hero_btn_text')->nullable()->after('hero_subtitle');
            $table->string('hero_btn_url')->nullable()->after('hero_btn_text'); // Opsional: Link tombol
        });
    }

    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropColumn(['hero_title', 'hero_subtitle', 'hero_btn_text', 'hero_btn_url']);
        });
    }
};
