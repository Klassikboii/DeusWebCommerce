<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    // 🚨 TAMBAHKAN PARAMETER Website $website
    public function index(Website $website) 
    {
        // FIX: Tampilkan SEMUA tiket milik user ini tanpa memandang dari toko mana ia dibuat.
        // Agar jika klien pindah ke dashboard toko B, ia tetap bisa melihat riwayat tiket toko A.
        $tickets = Ticket::where('user_id', Auth::id())->latest()->paginate(10);
        
        return view('client.tickets.index', compact('tickets', 'website'));
    }

    // 🚨 TAMBAHKAN PARAMETER Website $website
    public function store(Request $request, Website $website)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string'
        ]);

        Ticket::create([
            'ticket_number' => 'TKT-' . strtoupper(Str::random(6)),
            'user_id' => Auth::id(),
            // 🚨 FIX UTAMA: Otomatis kunci ke website ID yang sedang dibuka di sidebar!
            'website_id' => $website->id, 
            'subject' => $request->subject,
            'description' => $request->description,
            'status' => 'pending'
        ]);

        return redirect()->back()->with('success', 'Tiket bantuan berhasil dikirim! Tim kami akan segera meninjaunya.');
    }
}