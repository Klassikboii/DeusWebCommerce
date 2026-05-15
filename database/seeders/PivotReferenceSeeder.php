<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PivotReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Mulai menyedot data referensi Pivot...');

        // 1. Sedot Data Industri
        $industryFile = database_path('references/industries.csv');
        if (File::exists($industryFile)) {
            $this->importIndustries($industryFile);
        } else {
            $this->command->warn('⚠️ File industries.csv tidak ditemukan di folder database/references/');
        }

        // 2. Sedot Data Kecamatan (Districts)
        $districtFile = database_path('references/districts.csv');
        if (File::exists($districtFile)) {
            $this->importDistricts($districtFile);
        } else {
            $this->command->warn('⚠️ File districts.csv tidak ditemukan di folder database/references/');
        }

        $this->command->info('✅ SEMUA DATA BERHASIL DI-IMPORT!');
    }

    private function importIndustries($file)
    {
        $this->command->info('📦 Memproses tabel Industri...');
        DB::table('pivot_industries')->truncate(); // Bersihkan data lama jika ada
        
        $data = [];
        $row = 0;
        
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($csvData = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row++;
                if ($row == 1) continue; // Lewati baris pertama (Header CSV)

                $data[] = [
                    'parent_industry' => $csvData[0],
                    'child_industry'  => $csvData[1],
                    'mcc'             => $csvData[2],
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];

                // Masukkan ke database setiap 500 baris agar RAM aman
                if (count($data) >= 500) {
                    DB::table('pivot_industries')->insert($data);
                    $data = []; // Kosongkan keranjang
                }
            }
            // Masukkan sisa data yang kurang dari 500
            if (count($data) > 0) {
                DB::table('pivot_industries')->insert($data);
            }
            fclose($handle);
        }
    }

    private function importDistricts($file)
    {
        $this->command->info('🌍 Memproses tabel Kecamatan (District)...');
        DB::table('pivot_districts')->truncate(); // Bersihkan data lama jika ada
        
        $data = [];
        $row = 0;
        
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($csvData = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row++;
                if ($row == 1) continue; // Lewati baris pertama (Header CSV)

                $data[] = [
                    'id'         => $csvData[0], // District ID
                    'city_id'    => $csvData[1], // City ID
                    'name'       => $csvData[2], // Nama Kecamatan
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Masukkan ke database setiap 500 baris agar RAM aman
                if (count($data) >= 500) {
                    DB::table('pivot_districts')->insert($data);
                    $data = []; // Kosongkan keranjang
                }
            }
            // Masukkan sisa data yang kurang dari 500
            if (count($data) > 0) {
                DB::table('pivot_districts')->insert($data);
            }
            fclose($handle);
        }
    }
}