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
    // --- 1. MENCATAT LOGIN ---
    protected function authenticated(Request $request, $user)
    {
        // Fitur lama: Simpan IP untuk UI History (tetap biarkan)
        $user->loginHistories()->create([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        $historiesToKeep = $user->loginHistories()->take(3)->pluck('id');
        $user->loginHistories()->whereNotIn('id', $historiesToKeep)->delete();

        // 🚨 Fitur Baru: Catat ke Audit Log Superadmin
        \App\Models\UserActivity::log('login', 'User berhasil login ke dalam sistem.');

        return redirect()->intended($this->redirectPath());
    }

    // --- 2. MENCATAT LOGOUT (Override Fungsi Bawaan) ---
    public function logout(Request $request)
    {
        // 🚨 Catat Log SEBELUM user dikeluarkan oleh sistem
        if (\Illuminate\Support\Facades\Auth::check()) {
            \App\Models\UserActivity::log('logout', 'User keluar (logout) dari sistem.');
        }

        // Jalankan perintah logout bawaan Laravel
        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $request->wantsJson()
            ? new \Illuminate\Http\JsonResponse([], 204)
            : redirect('/');
    }
}