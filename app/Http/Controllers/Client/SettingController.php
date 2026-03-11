<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index(Website $website)
    {
        $this->authorize('viewAny', $website);
        $cities = \App\Models\City::with('province')->orderBy('name', 'asc')->get();
        return view('client.settings.index', compact('website', 'cities'));
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

        $request->validate([
            'midtrans_client_key' => 'nullable|string|max:255',
            'midtrans_server_key' => 'nullable|string|max:255',
            // Checkbox HTML jika tidak dicentang tidak akan terkirim, kita tangani di bawah
        ]);

        $website->update([
            'midtrans_client_key' => $request->midtrans_client_key,
            'midtrans_server_key' => $request->midtrans_server_key,
            // Jika checkbox dicentang nilainya 1 (true), jika tidak ada maka 0 (false/sandbox)
            'midtrans_is_production' => $request->has('midtrans_is_production') ? 1 : 0, 
        ]);

        return redirect()->back()->with('success', 'Pengaturan Pembayaran Midtrans berhasil diperbarui!');
    }
}