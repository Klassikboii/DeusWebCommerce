<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use App\Models\Province;
use App\Models\City;

class RajaOngkirSeeder extends Seeder
{
    public function run(): void
    {
        $apiKey = env('RAJAONGKIR_API_KEY');

        if (!$apiKey) {
            $this->command->error('RAJAONGKIR_API_KEY belum diisi di file .env!');
            return;
        }

        $this->command->info('Mulai menyedot data dari Komerce (RajaOngkir V2)...');

        // 1. Ambil Data Provinsi
        $responseProvince = Http::withHeaders(['key' => $apiKey])
            ->get('https://rajaongkir.komerce.id/api/v1/destination/province');

        if ($responseProvince->successful()) {
            $provinces = $responseProvince->json()['data'] ?? [];
            $this->command->info('Ditemukan ' . count($provinces) . ' Provinsi. Mulai mengunduh kota...');

            foreach ($provinces as $province) {
                // Simpan Provinsi
                Province::updateOrCreate(
                    ['id' => $province['id']],
                    ['name' => $province['name']]
                );

                // 2. Langsung Ambil Data Kota berdasarkan Province ID
                $responseCity = Http::withHeaders(['key' => $apiKey])
                    ->get("https://rajaongkir.komerce.id/api/v1/destination/city/{$province['id']}");
                
                if ($responseCity->successful()) {
                    $cities = $responseCity->json()['data'] ?? [];
                    
                    foreach ($cities as $city) {
                        // Komerce API menggabungkan kata "KOTA/KABUPATEN" ke dalam nama
                        $type = str_contains(strtoupper($city['name']), 'KABUPATEN') ? 'Kabupaten' : 'Kota';
                        
                        // Kita bersihkan kata "Kota" atau "Kabupaten" agar namanya rapi di database kita
                        $cleanName = trim(str_ireplace(['KOTA ', 'KABUPATEN '], '', $city['name']));

                        City::updateOrCreate(
                            ['id' => $city['id']],
                            [
                                'province_id' => $province['id'],
                                'type' => $type,
                                'name' => $cleanName,
                                'postal_code' => $city['zip_code'] ?? null
                            ]
                        );
                    }
                }
            }
            $this->command->info('✅ Proses sinkronisasi Provinsi dan Kota selesai!');
        } else {
            $this->command->error('Gagal menghubungi server Komerce. Pastikan API Key benar.');
            $this->command->error('Pesan: ' . $responseProvince->body());
        }
    }
}