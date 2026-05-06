<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah Kolom Saldo di Tabel Websites
        Schema::table('websites', function (Blueprint $table) {
            $table->decimal('wallet_balance', 15, 2)->default(0)->after('is_open');
        });

        // 2. Tabel Mutasi Dompet (Buku Tabungan / Riwayat Transaksi)
        Schema::create('wallet_mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete(); // Jika uang masuk dari order
            $table->enum('type', ['credit', 'debit']); // Credit = Uang Masuk, Debit = Uang Keluar (Ditarik)
            $table->decimal('amount', 15, 2);
            $table->string('description'); // Contoh: "Penjualan Order INV-123" atau "Penarikan Dana"
            $table->timestamps();
        });

        // 3. Tabel Pencairan Dana (Withdrawals)
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            
            // Rekam detail bank saat menarik (agar aman jika besok bank diubah)
            $table->string('bank_name');
            $table->string('bank_account_number');
            $table->string('bank_account_name');
            
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('transfer_proof')->nullable(); // Foto bukti transfer dari Admin Deus
            $table->text('admin_note')->nullable(); // Catatan admin jika ditolak/disetujui
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
        Schema::dropIfExists('wallet_mutations');
        Schema::table('websites', function (Blueprint $table) {
            $table->dropColumn('wallet_balance');
        });
    }
};