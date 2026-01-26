<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Package;

class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        // 1. UBAH USER PERTAMA JADI SUPER ADMIN
        // Asumsi user ID 1 adalah akun Anda yang sedang dipakai login sekarang
        $admin = User::find(1);
        if($admin) {
            $admin->update(['role' => 'admin']);
            $this->command->info('User ID 1 berhasil menjadi Admin!');
        }

        // 2. BUAT PAKET 'FREE' (GRATIS SELAMANYA / TRIAL)
        Package::create([
            'name' => 'Starter (Free)',
            'price' => 0,
            'duration_days' => 30,
            'max_products' => 5, // Dikit aja biar mereka upgrade
            'can_custom_domain' => false,
            'remove_branding' => false,
        ]);

        // 3. BUAT PAKET 'PRO'
        Package::create([
            'name' => 'Pro Business',
            'price' => 99000,
            'duration_days' => 30,
            'max_products' => 100, // Lebih lega
            'can_custom_domain' => true, // Fitur premium
            'remove_branding' => true,
        ]);
        
        $this->command->info('Paket langganan berhasil dibuat!');
    }
}