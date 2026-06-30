<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index()
    {
        // Ambil semua tiket beserta data user dan websitenya
        $tickets = Ticket::with(['user', 'website'])->latest()->paginate(15);
        
        return view('admin.tickets.index', compact('tickets'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,resolved',
            'admin_reply' => 'nullable|string'
        ]);

        $ticket->update([
            'status' => $request->status,
            'admin_reply' => $request->admin_reply
        ]);

        return redirect()->back()->with('success', 'Tiket berhasil di-update dan balasan terkirim ke klien.');
    }
    // 🚨 Tambahkan Request $request di dalam parameternya
    public function impersonate(\Illuminate\Http\Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);

        // SECURITY: Cegah login sebagai sesama Super Admin
        if ($user->role === 'admin') {
            return redirect()->back()->with('error', 'Anda tidak bisa melakukan impersonate terhadap sesama Super Admin.');
        }

        // Login sebagai user tersebut
        \Illuminate\Support\Facades\Auth::login($user);

        // 🚨 LOGIKA BARU: Jika datang dari Pusat Bantuan, langsung arahkan ke toko yang rusak!
        if ($request->filled('redirect_to')) {
            return redirect($request->redirect_to)->with('success', "Halo Bos! Anda sedang menangani tiket dari: {$user->name}");
        }

        // Redirect default ke dashboard client (Halaman pilih website)
        return redirect()->route('client.websites')->with('success', "Halo Bos! Anda sedang login sebagai user: {$user->name}");
    }
}