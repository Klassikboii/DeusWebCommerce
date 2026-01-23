<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('websites', function (Blueprint $table) {
        $table->id();
        // Relasi ke tabel 'users' (User yang Login)
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        
        // Relasi ke tabel 'website_templates' (Desain yang dipilih)
        $table->foreignId('template_id')->default(1); 
        
        // Identitas Website
        $table->string('site_name');
        $table->string('subdomain')->unique(); // subdomain.platform.com
        $table->string('custom_domain')->nullable(); // tokoanda.com
        
        // Konfigurasi Tampilan (Pilar A)
        $table->string('primary_color')->default('#3b82f6');
        $table->string('secondary_color')->default('#8b5cf6');
        
        // Konfigurasi Kontak
        $table->string('whatsapp_number')->nullable();
        $table->string('email_contact')->nullable();
        $table->text('address')->nullable();
        
        $table->enum('status', ['draft', 'published', 'suspended'])->default('draft');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('websites');
    }
};
