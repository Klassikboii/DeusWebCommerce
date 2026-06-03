<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth; // <--- WAJIB ADA
use Illuminate\Http\Request;

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
        
        return route('login');
    }

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
    protected function authenticated(Request $request, $user)
    {
        // 1. Simpan history login saat ini
        $user->loginHistories()->create([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // 2. Batasi hanya menyimpan 3 data terakhir (Hapus sisanya)
        $historiesToKeep = $user->loginHistories()->take(3)->pluck('id');
        
        $user->loginHistories()->whereNotIn('id', $historiesToKeep)->delete();

        // 3. Lanjutkan redirect bawaan Laravel (ke halaman home/dashboard)
        return redirect()->intended($this->redirectPath());
    }
}