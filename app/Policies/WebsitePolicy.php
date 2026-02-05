<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Website;
use Illuminate\Auth\Access\Response;

class WebsitePolicy
{
    // 1. INDEX: Apakah user boleh melihat daftar website?
    // Biasanya return true (karena controller akan memfilter via where('user_id', auth()->id()))
    // Tapi bisa juga kita cek logic lain.
    public function viewAny(User $user)
    {
        return true; 
    }

    // 2. SHOW/DASHBOARD: Apakah user boleh melihat detail website ini?
    public function view(User $user, Website $website)
    {
        return $user->id === $website->user_id;
    }

    // 3. STORE: Apakah user boleh membuat website baru?
    // Disini kita bisa pasang batasan. Misal: User Free cuma boleh punya 1 website.
    public function create(User $user)
    {
        // Contoh Logic Pembatasan:
        // $count = Website::where('user_id', $user->id)->count();
        // if ($user->is_free_tier && $count >= 1) return false;
        
        return true; // Default boleh
    }

    // 4. UPDATE: Apakah user boleh edit/tambah produk/ubah setting?
    public function update(User $user, Website $website)
    {
        return $user->id === $website->user_id;
    }

    // 5. DELETE: Apakah user boleh hapus website?
    public function delete(User $user, Website $website)
    {
        return $user->id === $website->user_id;
    }
}