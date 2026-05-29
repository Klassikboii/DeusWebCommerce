<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Website; // 🚨 PENTING: Panggil Model Website
use App\Models\WalletMutation;
use App\Models\Withdrawal;
use App\Services\Payment\PivotService;
use Log;

class WalletController extends Controller
{
    protected $pivotService;

    public function __construct(PivotService $pivotService)
    {
        $this->pivotService = $pivotService;
    }
    // 🚨 Tangkap $website dari URL: manage/{website}/wallet
    public function index()
    {
        $user = auth()->user();
        $kyb = $user->kybDetail;
        
        // Dapatkan konteks toko yang sedang aktif
        $website = session('website_id') 
            ? Website::find(session('website_id')) 
            : $user->websites->first();

        $activeBalance = 0;
        $isPivotActive = false;

        // Panggil saldo dari Pivot secara Real-Time HANYA JIKA KYB Approved
        if ($kyb && $kyb->status === 'approved' && $kyb->merchant_id) {
            $isPivotActive = true;
            $activeBalance = $this->pivotService->getSubAccountBalance($kyb->merchant_id);
        }

        // Ambil data riwayat transaksi (Gunakan paginate agar fungsi hasPages() di Blade tidak error)
        $withdrawals = Withdrawal::where('website_id', $website->id)->latest()->paginate(10);
        $mutations = WalletMutation::where('website_id', $website->id)->latest()->paginate(10);

        return view('client.wallet.index', compact('website', 'kyb', 'activeBalance', 'isPivotActive', 'withdrawals', 'mutations'));
    }

    // 🚨 Tangkap $website dari URL juga untuk aksi withdraw
public function withdraw(Request $request, Website $website)
    {
        // 1. Validasi input
        $request->validate([
            'amount' => 'required|numeric|min:50000',
        ]);

        $user = auth()->user();
        $kyb = $user->kybDetail;

        if (!$kyb || $kyb->status !== 'approved' || !$kyb->merchant_id) {
            return back()->with('error', 'Akun Anda belum terverifikasi untuk melakukan penarikan.');
        }

        // 2. Cek saldo live
        $activeBalance = $this->pivotService->getSubAccountBalance($kyb->merchant_id);

        if ($request->amount > $activeBalance) {
            return back()->with('error', 'Nominal penarikan melebihi saldo aktif Anda saat ini.');
        }

        // 3. Generate Reference ID Unik untuk Withdrawal ini
        // Format: WD-TahunBulanHari-Random (Contoh: WD-20260526-9A2B)
        $referenceId = 'WD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            // Simpan ke database dengan reference_id (Pastikan Anda sudah menambah kolom ini di tabel withdrawals)
            $withdrawal = \App\Models\Withdrawal::create([
                'website_id' => $website->id,
                'reference_id' => $referenceId, // 🚨 Kunci untuk Webhook nanti
                'amount' => $request->amount,
                'status' => 'pending', 
                'bank_name' => $kyb->bank_channel_code,
                'bank_account_number' => $kyb->bank_account_number,
                'bank_account_name' => $kyb->bank_account_name,
            ]);

            // =====================================================================
            // 🚨 PAYLOAD SESUAI DOKUMENTASI PIVOT
            // =====================================================================
            $payload = [
                'referenceId' => $referenceId,
                'withdrawType' => 'BANK_TRANSFER',
                // 'balanceType' => 'PAYMENT_BALANCE', // Opsional, hapus jika tidak diminta wajib
                'isFullAmount' => false,
                'amount' => [
                    'currency' => 'IDR',
                    'value' => (string) $request->amount // Wajib String!
                ],
                'description' => 'Tarik Dana Manual: ' . $kyb->short_name
            ];

            $accessToken = $this->pivotService->getAccessToken(); 
            // 🚨 Panggil Base URL dari .env
            $pivotBaseUrl = env('PIVOT_BASE_URL', 'https://api.pivot-payment.com');
            
            $pivotResponse = \Illuminate\Support\Facades\Http::withToken($accessToken)
                ->withHeaders([
                    'X-MERCHANT-ID' => env('PIVOT_CLIENT_KEY'),
                    'x-submerchant-id' => trim($kyb->merchant_id),
                    'Content-Type' => 'application/json',
                ])->post($pivotBaseUrl . '/v1/withdrawals', $payload); // 🚨 Gunakan variabel di sini

            if ($pivotResponse->successful()) {
                $responseBody = $pivotResponse->json();
                $pivotStatus = $responseBody['data']['status'] ?? 'PENDING';

                // Jika beruntung dan Bank langsung memproses detik itu juga (SUCCESS)
                if ($pivotStatus === 'SUCCESS') {
                    $withdrawal->update(['status' => 'approved']);

                    \App\Models\WalletMutation::create([
                        'website_id' => $website->id,
                        'amount' => $request->amount,
                        'type' => 'debit',
                        'description' => "Pencairan dana manual ke rekening {$kyb->bank_channel_code} sukses."
                    ]);
                    
                    $pesanStatus = 'Pencairan dana berhasil diproses langsung ke rekening Anda!';
                } 
                // Jika normal dan butuh waktu antrean bank (PENDING)
                else {
                    // Status lokal tetap 'pending', tunggu Webhook!
                    $pesanStatus = 'Pencairan sedang diproses oleh pihak Bank. Dana akan masuk ke rekening Anda segera.';
                }

                \Illuminate\Support\Facades\DB::commit();
                return redirect()->back()->with('success', $pesanStatus);
            }

            // Jika API menolak (HTTP 400/500)
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('API Withdrawal Gagal: ' . $pivotResponse->body());
            
            // Ambil pesan error asli dari Pivot untuk ditampilkan ke klien
            $errorMessage = $pivotResponse->json()['message'] ?? 'Gagal menghubungi server bank.';
            return redirect()->back()->with('error', 'Pencairan ditolak oleh sistem: ' . $errorMessage);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error Sistem Pencairan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}