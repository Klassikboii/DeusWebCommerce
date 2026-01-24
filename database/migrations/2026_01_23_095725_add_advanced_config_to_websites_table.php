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
        // 1. Branding
        $table->string('logo')->nullable()->after('site_name'); // Path gambar logo
        $table->string('favicon')->nullable()->after('logo');   // Path icon tab browser
        $table->string('hero_image')->nullable()->after('hero_btn_text'); // Ganti background banner

        // 2. Typography
        $table->string('font_family')->default('Inter')->after('secondary_color'); // Jenis Font
        $table->integer('base_font_size')->default(16)->after('font_family'); // Ukuran dasar (px)

        // 3. Layout Product
        $table->string('product_image_ratio')->default('1/1')->after('base_font_size'); // 1/1, 4/3, 16/9
        $table->string('product_card_style')->default('shadow')->after('product_image_ratio'); // shadow, border, minimal
    });
}

public function down(): void
{
    Schema::table('websites', function (Blueprint $table) {
        $table->dropColumn([
            'logo', 'favicon', 'hero_image', 
            'font_family', 'base_font_size', 
            'product_image_ratio', 'product_card_style'
        ]);
    });
}
};
