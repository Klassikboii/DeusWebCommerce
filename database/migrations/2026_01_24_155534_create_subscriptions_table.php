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
    Schema::create('subscriptions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('website_id')->constrained()->onDelete('cascade');
        $table->foreignId('package_id')->constrained()->onDelete('cascade');
        
        $table->string('status')->default('active'); // active, expired, cancelled
        $table->dateTime('starts_at');
        $table->dateTime('ends_at')->nullable(); // Nullable jika lifetime
        
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('subscriptions');
}
};
