<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('accurate_stock')->nullable()->after('stock')->comment('Bayangan stok terakhir di Accurate');
            $table->timestamp('last_sync_at')->nullable()->after('accurate_stock');
        });
        
        // Lakukan hal yang sama untuk varian jika toko mendukung varian
        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->integer('accurate_stock')->nullable()->after('stock');
            });
        }
    }

    public function down(): void {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['accurate_stock', 'last_sync_at']);
        });
        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->dropColumn(['accurate_stock']);
            });
        }
    }
};