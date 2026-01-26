<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Cek apakah user sudah login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // 2. Cek apakah jabatannya 'admin'
        if (auth()->user()->role !== 'admin') {
            // Jika bukan admin, tendang ke dashboard client biasa
            return redirect('/dashboard')->with('error', 'Anda tidak memiliki akses ke halaman Super Admin.');
        }

        // 3. Jika lolos, silakan lewat
        return $next($request);
    }
}