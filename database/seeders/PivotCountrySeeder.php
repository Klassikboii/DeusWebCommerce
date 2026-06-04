<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PivotCountrySeeder extends Seeder
{
    public function run(): void
    {
        $csvFile = base_path('database/references/countries.csv');
        
        if (!file_exists($csvFile)) {
            $this->command->error("File countries.csv tidak ditemukan di database/references/");
            return;
        }

        $file = fopen($csvFile, 'r');
        $isHeader = true;

        while (($data = fgetcsv($file, 1000, ',')) !== false) {
            if ($isHeader) {
                $isHeader = false;
                continue; 
            }

            // Cek apakah data kolom 0 dan 1 ada
            if (!empty($data[0]) && !empty($data[1])) {
                $code = trim($data[0]);
                $name = trim($data[1]);

                // 🚨 SARINGAN: Pastikan Code ISO tidak lebih dari 5 huruf
                // Jika lebih dari 5 huruf (seperti kasus binary Excel), lewati baris ini!
                if (strlen($code) <= 5) {
                    DB::table('pivot_countries')->updateOrInsert(
                        ['code' => strtoupper($code)], // Jadikan huruf besar semua agar seragam (ID, US)
                        ['name' => $name]
                    );
                }
            }
        }
        fclose($file);
        
        $this->command->info('Data Negara ISO 3166-1 berhasil di-seed!');
    }
}