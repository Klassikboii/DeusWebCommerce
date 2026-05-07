<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Website;
use App\Models\Voucher;
use Illuminate\Validation\Rule;

class VoucherController extends Controller
{
    public function index(Website $website)
    {
        // Ambil semua voucher milik toko ini, urutkan dari yang terbaru
        $vouchers = $website->vouchers()->latest()->get();
        return view('client.vouchers.index', compact('website', 'vouchers'));
    }

    public function create(Website $website)
    {
        return view('client.vouchers.create', compact('website'));
    }

    public function store(Request $request, Website $website)
    {
        $request->validate([
            'code' => [
                'required', 'string', 'max:50', 'alpha_dash',
                // Pastikan kode voucher unik per toko (bukan per seluruh database)
                Rule::unique('vouchers')->where(fn ($query) => $query->where('website_id', $website->id))
            ],
            'discount_type' => 'required|in:nominal,percent',
            'discount_value' => 'required|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'min_purchase' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'target_rfm_segment' => 'nullable|string',
        ]);

        $website->vouchers()->create([
            'code' => strtoupper($request->code), // Otomatis jadikan huruf besar
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'max_discount_amount' => $request->discount_type === 'percent' ? $request->max_discount_amount : null,
            'min_purchase' => $request->min_purchase ?? 0,
            'max_uses' => $request->max_uses,
            'valid_from' => $request->valid_from,
            'valid_until' => $request->valid_until,
            'target_rfm_segment' => $request->target_rfm_segment,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('client.vouchers.index', $website->id)
            ->with('success', 'Voucher baru berhasil ditambahkan!');
    }
    // 1. FUNGSI UPDATE (EDIT)
       public function update(Request $request, $websiteId, \App\Models\Voucher $voucher)
    {
       // 🚨 SOLUSI: Langsung tembak ke database menggunakan $websiteId dari URL
        $website = \App\Models\Website::find($websiteId);

        // Jaring pengaman tambahan
        if (!$website) {
            abort(404, 'Data Toko tidak ditemukan.');
        }

        // Keamanan tambahan: Pastikan voucher ini benar-benar milik toko klien
        if ($voucher->website_id !== $website->id) {
            abort(403, 'Unauthorized action.');
        }

        // Validasi input
        $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                // Pastikan kode unik di toko ini, tapi abaikan kode milik voucher ini sendiri
                \Illuminate\Validation\Rule::unique('vouchers')->where(function ($query) use ($website) {
                    return $query->where('website_id', $website->id)->whereNull('deleted_at');
                })->ignore($voucher->id),
            ],
            'discount_type' => 'required|in:nominal,percent',
            'discount_value' => 'required|numeric|min:1',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'quota' => 'nullable|integer|min:1',
            'target_rfm_segment' => 'nullable|string',
            'valid_until' => 'nullable|date',
            'is_active' => 'required|boolean',
        ]);

        // Update data
        $voucher->update($request->all());

        return redirect()->back()->with('success', 'Voucher berhasil diperbarui!');
    }

    // 2. FUNGSI HAPUS (SOFT DELETE)
        public function destroy($websiteId, \App\Models\Voucher $voucher)
    {
        

        // 🚨 SOLUSI: Langsung tembak ke database menggunakan $websiteId dari URL
        $website = \App\Models\Website::find($websiteId);

        // Jaring pengaman tambahan
        if (!$website) {
            abort(404, 'Data Toko tidak ditemukan.');
        }

        // Keamanan tambahan: Pastikan voucher ini benar-benar milik toko klien
        if ($voucher->website_id !== $website->id) {
            abort(403, 'Unauthorized action.');
        }

        // Karena kita menggunakan SoftDeletes, perintah delete() di bawah ini 
        // TIDAK AKAN menghapus baris di database, melainkan hanya mengisi tanggal di kolom 'deleted_at'.
        // Voucher ini otomatis akan hilang dari daftar index() dan tidak bisa di-apply lagi!
        $voucher->delete();

        return redirect()->back()->with('success', 'Voucher berhasil dihapus!');
    }
    // FUNGSI UNTUK MEMATIKAN/MENGHIDUPKAN VOUCHER DENGAN 1 KLIK
    public function toggleStatus($websiteId, \App\Models\Voucher $voucher)
    {
        // Ubah status menjadi kebalikannya (Jika true jadi false, jika false jadi true)
        $voucher->update([
            'is_active' => !$voucher->is_active
        ]);

        $statusText = $voucher->is_active ? 'diaktifkan' : 'dinonaktifkan';
        
        return redirect()->back()->with('success', "Voucher berhasil {$statusText}!");
    }
}