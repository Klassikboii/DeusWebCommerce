<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth; // <--- Pastikan ada ini

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Logika Redirect setelah Login Sukses
     */
    public function redirectTo()
    {
        // 1. Ambil User yang sedang login
        $user = Auth::user();

        // 2. Cek Role & Arahkan
        if ($user->role === 'admin' || $user->role === 'superadmin') {
            return route('admin.dashboard'); // Admin ke Dashboard Pusat
        }
        
        // 3. User Biasa (Client) ke Halaman Pilih Website
        return route('client.websites'); 
    }

    // Pastikan construct tetap ada (bawaan)
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
}