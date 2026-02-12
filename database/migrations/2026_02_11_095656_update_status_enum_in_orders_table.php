<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Menambahkan 'awaiting_confirmation' ke dalam daftar ENUM
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'awaiting_confirmation', 'processing', 'shipped', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down()
    {
        // Kembalikan ke status lama jika rollback (Hati-hati, data 'awaiting_confirmation' bisa error jika ada)
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'processing', 'shipped', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");
    }
};  