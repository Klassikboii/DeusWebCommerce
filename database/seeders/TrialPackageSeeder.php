<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;

class TrialPackageSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan tidak duplikat
        if (!Package::where('name', 'Free Trial 14 Hari')->exists()) {
            Package::create([
                'name' => 'Free Trial 14 Hari',
                'price' => 0,
                'duration_days' => 14, // Durasi otomatis expired dalam 14 hari
                'max_products' => 100, // Beri mereka rasa enak (limit tinggi)
                'can_custom_domain' => false,
                'remove_branding' => false,
            ]);
        }
    }
}