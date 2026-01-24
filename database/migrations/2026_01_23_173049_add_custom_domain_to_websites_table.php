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
        // Kolom untuk domain pilihan user (misal: tokosaya.com)
        // Sudah ada 'custom_domain' di migrasi awal? Jika belum, tambahkan. 
        // Jika di tabel Anda sudah ada (saya lihat di screenshot phpmyadmin ada kolom custom_domain NULL), 
        // maka kita hanya butuh kolom status.
        
        // Cek dulu: Jika custom_domain belum ada, uncomment baris bawah ini:
        // $table->string('custom_domain')->nullable()->after('subdomain');
        
        $table->enum('domain_status', ['none', 'pending', 'active'])->default('none')->after('subdomain');
    });
}

public function down(): void
{
    Schema::table('websites', function (Blueprint $table) {
        $table->dropColumn(['domain_status']); 
        // $table->dropColumn(['custom_domain']); // Jika tadi ditambah
    });
}
};
