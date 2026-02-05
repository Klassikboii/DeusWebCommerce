<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth; // <--- WAJIB ADA

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Logika Redirect setelah Login Sukses
     */
    public function redirectTo()
    {
        $user = Auth::user();

        // 1. Jika Role ADMIN -> Ke Dashboard Pusat
        if ($user->role === 'admin') {
            return route('admin.dashboard');
        }

        // 2. Jika Role CLIENT -> Ke Halaman Pilih Website
        if ($user->role === 'client') {
            return route('client.websites');
        }

        // Default (jika ada role lain)
        return '/';
    }

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
}