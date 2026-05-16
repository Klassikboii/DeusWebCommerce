<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function index(Website $website)
    {
        $this->authorize('viewAny', $website);
        $cities = \App\Models\City::with('province')->orderBy('name', 'asc')->get();

        // 🚨 DAFTAR SEMUA KURIR YANG DIDUKUNG KOMERCE API
        $supportedCouriers = [
            'jne'      => 'JNE Express',
            'sicepat'  => 'SiCepat',
            'jnt'      => 'J&T Express',
            'pos'      => 'POS Indonesia',
            'anteraja' => 'AnterAja',
            'ninja'    => 'Ninja Xpress',
            'tiki'     => 'TIKI',
            'lion'     => 'Lion Parcel',
            'ide'      => 'ID Express',
            'sap'      => 'SAP Express'
        ];
        return view('client.settings.index', compact('website', 'cities', 'supportedCouriers'));

        
    }

    public function update(Request $request, Website $website)
    {
        $this->authorize('update', $website);

        $request->validate([
            'site_name' => 'required|string|max:50',
            'whatsapp_number' => 'nullable|numeric',
            'email_contact' => 'nullable|email',
            'address' => 'nullable|string|max:500',
            'icon' => 'nullable|image|max:1024', // Validasi Icon Toko

            'bank_name' => 'nullable|string|max:50',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_holder' => 'nullable|string|max:100',
            'city_id' => 'nullable|numeric',
            'active_couriers' => 'nullable|array',
        ]);

        $data = [
            'site_name' => $request->site_name,
            'whatsapp_number' => $request->whatsapp_number,
            'email_contact' => $request->email_contact,
            'address' => $request->address,
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'bank_account_holder' => $request->bank_account_holder,
            'is_open' => $request->has('is_open'),
            'city_id' =>$request->city_id,
            'active_couriers' => $request->active_couriers ?? ['jne'], // Beri default JNE jika klien hapus semua centang
        ];

        // Fitur Tambahan: Upload Icon/Logo Toko (Opsional jika ingin dipakai nanti)
        // Pastikan Anda sudah menambah kolom 'icon' di database jika ingin pakai ini.
        // Untuk sekarang kita simpan data teks dulu.

        $website->update($data);

        return redirect()->back()->with('success', 'Identitas toko berhasil diperbarui.');
    }
    public function updatePayment(Request $request, \App\Models\Website $website)
    {
        // Pastikan hanya pemilik website yang bisa mengubahnya
        $this->authorize('update', $website); 

        // Validasi Kredensial Pivot
        $request->validate([
            'pivot_client_key' => 'nullable|string|max:255',
            'pivot_server_key' => 'nullable|string|max:255',
            // pivot_is_production adalah boolean (checkbox)
        ]);

        $website->update([
            'pivot_client_key' => $request->pivot_client_key,
            'pivot_server_key' => $request->pivot_server_key,
            // Jika checkbox dicentang nilainya 1 (true), jika tidak ada maka 0 (false/sandbox)
            'pivot_is_production' => $request->has('pivot_is_production') ? 1 : 0, 
        ]);

        return redirect()->back()->with('success', 'Pengaturan Pembayaran Pivot berhasil diperbarui!');
    }
    // Fungsi untuk menampilkan form KYB
    public function paymentSettings(\App\Models\Website $website)
    {
        // Pastikan Anda membuat file view-nya di resources/views/client/payment/settings.blade.php
        return view('client.payment.settings', compact('website'));
    }

    // Fungsi untuk memproses data form KYB
    public function storeKyb(\Illuminate\Http\Request $request, \App\Models\Website $website)
    {
        // 1. Validasi harus mencakup SEMUA field yang ada di form
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:25',
            'business_structure' => 'required',
            'mcc' => 'required',
            'province' => 'required|string', // Tangkap Provinsi
            'city' => 'required|string', // Tangkap Kota
            'district_id' => 'required',
            'address' => 'required|max:254',
            'post_code' => 'required|string|max:20', // Tangkap Kode Pos
            'pic_name' => 'required|max:32',
            'pic_job_title' => 'nullable|string|max:20', // Tangkap Jabatan
            'pic_email' => 'required|email',
            'pic_phone' => 'required',
            'bank_channel_code' => 'required',
            'bank_account_number' => 'required',
            'auto_withdrawal' => 'nullable' // Tangkap Switch Auto-Withdrawal
        ]);

        // 2. Cari nama Parent & Child Industry dari database berdasarkan MCC yang dikirim Klien
        $industry = \Illuminate\Support\Facades\DB::table('pivot_industries')
                        ->where('mcc', $request->mcc)
                        ->first();

        // 3. Simpan seluruh data ke Database
        \App\Models\MerchantKybDetail::updateOrCreate(
            ['user_id' => \Illuminate\Support\Facades\Auth::id()], // Disimpan atas nama Klien yang sedang login
            array_merge($validated, [
                'status' => 'pending',
                // Mencegah error ".ashop.asia" jika domain/slug kosong
                'website' => $website->domain ?? ($website->slug ? $website->slug . '.ashop.asia' : 'toko.ashop.asia'),
                // Asumsi email/no.telp merchant sama dengan PIC (bisa disesuaikan)
                'merchant_email' => \Illuminate\Support\Facades\Auth::user()->email,
                'merchant_phone' => $request->pic_phone,
                
                // Masukkan nama industri yang asli dari database
                'parent_industry' => $industry ? $industry->parent_industry : 'Lainnya',
                'child_industry' => $industry ? $industry->child_industry : 'Lainnya',
                
                'digital_status' => 'Digital',
                'business_type' => $request->business_structure === 'INDIVIDUAL' ? 'INDIVIDUAL' : 'COMPANY',
                'auto_withdrawal' => $request->has('auto_withdrawal') ? 'ON' : 'OFF',
            ])
        );

        return redirect()->back()->with('success', 'Data verifikasi berhasil dikirim. Kami akan meninjau pengajuan Anda dalam 1-3 hari kerja.');
    }
    
}