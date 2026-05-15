<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchant_kyb_details', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke Website Klien (1 Website = 1 Sub-Account)
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();

            // ---------------------------------------------------------
            // 1. STATUS & TRACKING (Sistem Internal)
            // ---------------------------------------------------------
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('pivot_sub_account_id')->nullable(); // Menyimpan UUID Sub-Account Pivot saat sudah di-approve
            
            // ---------------------------------------------------------
            // 2. MERCHANT DETAIL
            // ---------------------------------------------------------
            $table->string('name', 255); // name (M)
            $table->string('short_name', 25); // shortName (M) - Maks 25 Karakter
            $table->string('description', 255)->nullable(); // description (O)
            $table->string('website'); // website (M)
            $table->string('logo', 255)->nullable(); // logo (M di API, tapi bisa kita handle default)
            $table->string('merchant_email', 255); // merchantEmail (M)
            $table->string('merchant_phone', 255); // merchantPhone (M)
            
            // Lokasi Bisnis
            $table->string('business_country', 255)->default('ID'); // businessCountry (M)
            $table->string('country_of_entity', 255)->default('ID'); // countryOfEntity (M)
            $table->string('province')->nullable(); // Kebutuhan UI Form
            $table->string('city')->nullable(); // Kebutuhan UI Form
            $table->unsignedBigInteger('district_id'); // districtId (M) - Harus Angka ID
            $table->string('address', 254); // address (M) - Maks 254 Karakter
            $table->string('post_code', 20); // postCode (M)
            
            // Kategori Bisnis
            $table->string('business_type', 20); // businessType (M) - Contoh: COMPANY, INDIVIDUAL
            $table->string('business_structure', 20); // businessStructure (M) - Contoh: PERSEROAN TERBATAS
            $table->string('parent_industry', 255); // parentIndustry (M)
            $table->string('child_industry', 255); // childIndustry (M)
            $table->string('mcc', 255); // mcc (M) - Merchant Category Code
            $table->string('digital_status', 255); // digitalStatus (M)

            // ---------------------------------------------------------
            // 3. PIC DETAIL
            // ---------------------------------------------------------
            $table->string('pic_name', 32); // picName (M) - Maks 32 Karakter
            $table->string('pic_email', 255); // picEmail (M)
            $table->string('pic_phone', 255); // picPhone (M)
            $table->string('pic_job_title', 20)->nullable(); // picJobTitle (O)

            // ---------------------------------------------------------
            // 4. WITHDRAWAL DETAIL (Rekening Bank)
            // ---------------------------------------------------------
            $table->string('bank_channel_code', 60); // channelCode (M) - Contoh: BCA, JENIUS
            $table->string('bank_account_number', 60); // accountNumber (M)
            $table->string('bank_account_name')->nullable(); // Beneficiary Name dari Validasi
            $table->string('auto_withdrawal', 5)->default('OFF'); // autoWithdrawal (O) - ON/OFF

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchant_kyb_details');
    }
};