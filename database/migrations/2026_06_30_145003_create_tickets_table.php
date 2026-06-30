<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique(); // Format unik, misal: TKT-X8Y9Z
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('website_id')->nullable()->constrained()->onDelete('cascade'); // Nullable jika masalah akun/umum
            $table->string('subject');
            $table->text('description');
            $table->enum('status', ['pending', 'in_progress', 'resolved'])->default('pending');
            $table->text('admin_reply')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tickets');
    }
};