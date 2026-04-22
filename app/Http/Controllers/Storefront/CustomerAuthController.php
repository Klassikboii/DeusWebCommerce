<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomerAuthController extends Controller
{
    // 1. TAMPILKAN FORM LOGIN & DAFTAR
    public function showAuthForm(Request $request)
    {
        $website = $request->get('website');
        return view('storefront.auth', compact('website'));
    }

    // 2. PROSES PENDAFTARAN & KLAIM OTOMATIS (SKEMA A)
    public function register(Request $request)
    {
        $website = $request->get('website');

        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'whatsapp' => 'required|numeric', // Kita buat wajib agar bisa nyambung ke pesanan lama
            'password' => 'required|min:6|confirmed', // Butuh field password_confirmation di HTML
        ]);

        // Cek apakah email sudah dipakai di toko ini
        $existingCustomer = Customer::where('website_id', $website->id)
                                    ->where('email', $request->email)
                                    ->first();

        if ($existingCustomer) {
            return back()->with('error', 'Email ini sudah terdaftar di toko kami. Silakan gunakan menu Login.');
        }

        // A. Buat Customer Baru
        $customer = Customer::create([
            'website_id' => $website->id,
            'name'       => $request->name,
            'email'      => $request->email,
            'whatsapp'   => $request->whatsapp,
            'password'   => Hash::make($request->password),
        ]);

        // ====================================================================
        // 🚨 SIHIR SKEMA A BEKERJA DI SINI 🚨
        // Mencari pesanan lama di toko ini dengan nomor WA yang sama persis
        // ====================================================================
        Order::where('website_id', $website->id)
             ->where('customer_whatsapp', $request->whatsapp)
             ->whereNull('customer_id') // Hanya pesanan yang belum punya pemilik
             ->update(['customer_id' => $customer->id]);

        // B. Langsung Loginkan Pelanggan (Guard Khusus Customer)
        Auth::guard('customer')->login($customer);

        // Arahkan ke halaman riwayat pesanan (nanti kita buat view-nya)
        return redirect()->route('store.account')->with('success', 'Pendaftaran berhasil! Riwayat pesanan lama Anda telah otomatis ditambahkan ke akun ini.');
    }

    // 3. PROSES LOGIN
    // 3. PROSES LOGIN (DENGAN MODE DETEKTIF)
    public function login(Request $request)
    {
        $website = $request->get('website');

        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $credentials['website_id'] = $website->id;

        // 🚨 DETEKTIF 1: Cek apakah email ini ADA di toko ini
        $customer = \App\Models\Customer::where('email', $credentials['email'])
                        ->where('website_id', $credentials['website_id'])
                        ->first();

        if (!$customer) {
            return back()->with('error', "DEBUG BANTUAN: Email '{$credentials['email']}' tidak terdaftar di toko ini (Website ID: {$website->id}). Silakan cek tabel customers di database.");
        }

        // 🚨 DETEKTIF 2: Cek apakah passwordnya cocok dengan Hash
        if (!\Illuminate\Support\Facades\Hash::check($credentials['password'], $customer->password)) {
            return back()->with('error', "DEBUG BANTUAN: Email ketemu, tapi Password yang Anda ketik salah/tidak cocok dengan enkripsi database.");
        }

        // Jika lolos kedua tes di atas, coba login resmi
        if (Auth::guard('customer')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('store.account')->with('success', 'Selamat datang kembali!');
        }

        return back()->with('error', 'DEBUG BANTUAN: Semua data cocok, tapi sistem Guard Laravel gagal membuat Session. Cek file config/auth.php!');
    }

    // 4. PROSES LOGOUT
    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('store.home')->with('success', 'Anda telah berhasil keluar.');
    }
}