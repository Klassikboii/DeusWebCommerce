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

        // 1. Aturan Dasar (Bisa disimpan meskipun Toko Tutup)
        $rules = [
            'site_name' => 'required|string|max:50',
            'whatsapp_number' => 'nullable|numeric',
            'email_contact' => 'nullable|email',
            'address' => 'nullable|string|max:500',
            'icon' => 'nullable|image|max:1024',
            'bank_name' => 'nullable|string|max:50',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_holder' => 'nullable|string|max:100',
            'city_id' => 'nullable|numeric',
            'active_couriers' => 'nullable|array',
        ];

        $customMessages = [
            'site_name.required' => 'Nama Toko wajib diisi.',
        ];

        // 🚨 2. THE GATEKEEPER: Aturan Ketat Jika Toko Dibuka
        if ($request->has('is_open')) {
            $rules['whatsapp_number'] = 'required|numeric';
            $rules['email_contact'] = 'required|email';
            $rules['address'] = 'required|string|max:500';
            $rules['city_id'] = 'required|numeric';
            
            // Wajibkan Kredensial Bank Manual
            $rules['bank_name'] = 'required|string|max:50';
            $rules['bank_account_number'] = 'required|string|max:50';
            $rules['bank_account_holder'] = 'required|string|max:100';

            // Pesan Error Khusus
            $customMessages['whatsapp_number.required'] = 'Nomor WhatsApp wajib diisi untuk membuka toko.';
            $customMessages['email_contact.required'] = 'Email Resmi wajib diisi untuk membuka toko.';
            $customMessages['address.required'] = 'Alamat Lengkap wajib diisi agar ongkir dapat dihitung.';
            $customMessages['city_id.required'] = 'Kota Asal Pengiriman wajib dipilih.';
            $customMessages['bank_name.required'] = 'Nama Bank wajib diisi agar pembeli bisa melakukan pembayaran.';
            $customMessages['bank_account_number.required'] = 'Nomor Rekening wajib diisi.';
            $customMessages['bank_account_holder.required'] = 'Nama Pemilik Rekening wajib diisi.';
        }

        // 3. Jalankan Validasi! (Jika gagal, otomatis kembali ke halaman sebelumnya membawa error)
        $request->validate($rules, $customMessages);

        // 4. Lanjut Menyimpan Data
        $data = [
            'site_name' => $request->site_name,
            'whatsapp_number' => $request->whatsapp_number,
            'email_contact' => $request->email_contact,
            'address' => $request->address,
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'bank_account_holder' => $request->bank_account_holder,
            'is_open' => $request->has('is_open'),
            'city_id' => $request->city_id,
            'active_couriers' => $request->active_couriers ?? ['jne'], 
        ];

        $website->update($data);

        \App\Models\UserActivity::log(
            'update_website_settings',
            "Memperbarui pengaturan toko: {$website->name}"
        );

        return redirect()->back()->with('success', 'Pengaturan toko berhasil diperbarui.');
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
    // 1. Fungsi menampilkan form
    public function paymentSettings()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        
        // 1. Ambil data KYB milik user
        $kyb = \App\Models\MerchantKybDetail::where('user_id', $user->id)->first();
        
        // 2. 🚨 Ambil semua website milik user ini
        $websites = $user->websites; 

        // 3. Konteks website untuk layout sidebar (jika diperlukan)
        $website = session('website_id') 
            ? \App\Models\Website::find(session('website_id')) 
            : $websites->first();

        // 4. 🚨 Lempar variabel $websites ke view
        return view('client.payment.settings', compact('kyb', 'website', 'websites'));
    }

    // Fungsi untuk memproses data form KYB
   // 2. Fungsi simpan formpublic function storeKyb(\Illuminate\Http\Request $request)
   public function storeKyb(\Illuminate\Http\Request $request) 
   {
        $user = \Illuminate\Support\Facades\Auth::user();

        // 🚨 1. Tambahkan 2 input baru ini ke dalam validasi
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:25',
            'website_id_reference' => 'required|exists:websites,id',
            'business_type' => 'required|in:INDIVIDUAL,COMPANY',
            'business_structure' => 'required',
            'country_of_entity' => 'required|string', // <-- BARU
            'digital_status' => 'required|string',    // <-- BARU
            'mcc' => 'required',
            'province' => 'required|string',
            'city' => 'required|string',
            'district_id' => 'required',
            'address' => 'required|max:254',
            'post_code' => 'required|string|max:20',
            'pic_name' => 'required|max:32',
            'pic_job_title' => 'nullable|string|max:20',
            'pic_email' => 'required|email',
            'pic_phone' => 'required',
            'bank_channel_code' => 'required',
            'bank_account_number' => 'required',
            'bank_account_name' => 'required',
            'auto_withdrawal' => 'nullable',
            'description' => 'required|string'
        ]);
        
        // 🚨 2. Perbaikan pencarian MCC seperti yang kita bahas sebelumnya
        $industry = \Illuminate\Support\Facades\DB::table('pivot_industries')->find($request->mcc);
        if (!$industry) {
            $industry = \Illuminate\Support\Facades\DB::table('pivot_industries')->where('mcc', $request->mcc)->first();
        }
        if (!$industry) {
            return redirect()->back()->withErrors(['mcc' => 'Kategori industri tidak ditemukan, silakan cari ulang.'])->withInput();
        }

        $selectedWeb = \App\Models\Website::find($request->website_id_reference);
        $websiteUrl = $selectedWeb->custom_domain ?? ($selectedWeb->subdomain ? $selectedWeb->subdomain . '.ashop.asia' : 'toko.ashop.asia');

        \App\Models\MerchantKybDetail::updateOrCreate(
            ['user_id' => $user->id], 
            array_merge($validated, [
                'status' => 'pending',
                'website' => $websiteUrl,
                'merchant_email' => $user->email,
                'merchant_phone' => $request->pic_phone,
                
                // Ekstrak identitas industri
                'mcc' => $industry->mcc,
                'parent_industry' => $industry->parent_industry,
                'child_industry' => $industry->child_industry,
                
                // 🚨 3. Dinamiskan input sesuai form klien
                'country_of_entity' => $request->country_of_entity,
                'digital_status' => $request->digital_status,
                
                // Pivot butuh 2 parameter: businessType (COMPANY/INDIVIDUAL) & businessStructure (FIRMA/PT/dll)
                'business_type' => $request->business_type,
                
                'auto_withdrawal' => $request->auto_withdrawal === 'ON' ? 'ON' : 'OFF',
            ])
        );
        // Catat log
    \App\Models\UserActivity::log(
        'update_kyb_details', 
        "Memperbarui detail KYB untuk toko: {$websiteUrl}"
    );

        return redirect()->back()->with('success', 'Data verifikasi berhasil dikirim. Kami akan meninjau pengajuan Anda segera.');
    }
    
}