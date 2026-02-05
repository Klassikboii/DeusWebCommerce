<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Fitur Killer: Login sebagai User lain tanpa password
     */
    public function impersonate($id)
    {
        $user = User::findOrFail($id);

        // SECURITY: Cegah login sebagai sesama Super Admin
        if ($user->role === 'admin') {
            return redirect()->back()->with('error', 'Anda tidak bisa melakukan impersonate terhadap sesama Super Admin.');
        }

        // Login sebagai user tersebut
        Auth::login($user);

        // Redirect ke dashboard client (Halaman pilih website)
        return redirect()->route('client.websites')->with('success', "Halo Bos! Anda sedang login sebagai user: {$user->name}");
    }

    /**
     * Hapus User (Beserta Website & Datanya)
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // SECURITY: Cegah menghapus diri sendiri atau sesama Admin
        if ($user->role === 'admin') {
            return redirect()->back()->with('error', 'DILARANG MENGHAPUS AKUN SUPER ADMIN!');
        }

        // Hapus User
        // Pastikan di Migration database Anda sudah set 'onDelete cascade' pada foreign key
        // agar website dan order milik user ini ikut terhapus otomatis.
        $user->delete();

        return redirect()->back()->with('success', 'User dan seluruh data websitenya berhasil dihapus.');
    }
}