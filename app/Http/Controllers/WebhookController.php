<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderHistory;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Services\AccurateService;
use App\Models\ProductVariant;
use App\Models\AccurateIntegration;

class WebhookController extends Controller
{
    public function handlePivotWebhook(Request $request)
    {
        $payload = $request->all();
        $event = $payload['event'] ?? ''; 

        \Illuminate\Support\Facades\Log::info('📥 WEBHOOK PIVOT MASUK:', $payload);

        // =========================================================================
        // 🚨 1. EKSTRAK ID TRANSAKSI DENGAN BENAR (TERMASUK UNTUK WITHDRAWAL)
        // =========================================================================
        $orderNumber = $request->input('clientReferenceId') 
                    ?? $request->input('data.clientReferenceId') 
                    ?? $request->input('data.withdrawal.referenceId') // 🚨 FIX: Ambil ID khusus dari objek Withdrawal
                    ?? $request->input('referenceId');

        // Deteksi Ping / Empty ID
        if (empty($orderNumber) || str_contains(strtoupper($orderNumber), 'TEST')) {
            \Illuminate\Support\Facades\Log::info("Merespons Ping/Tes Koneksi dari Dashboard Pivot.");
            return response()->json(['status' => 'success', 'message' => 'Webhook URL Verified'], 200);
        }

        // =========================================================================
        // 🚨 2. JALUR KHUSUS: TRANSAKSI PENCAIRAN DANA (WITHDRAWAL)
        // =========================================================================
        if (str_starts_with($event, 'WITHDRAW.')) {
            // Validasi Signature
            $apiKeyFromPivot = $request->header('x-api-key'); 
            if ($apiKeyFromPivot !== env('PIVOT_CALLBACK_KEY')) {
                \Illuminate\Support\Facades\Log::error("Webhook Withdrawal Ditolak: Invalid Signature");
                return response()->json(['status' => 'error', 'message' => 'Invalid Signature'], 403);
            }

            $status = $payload['data']['status'] ?? null; // SUCCESS / FAILED

            // Cari data penarikan di tabel withdrawals
            $withdrawal = \App\Models\Withdrawal::where('reference_id', $orderNumber)->first();

            if ($withdrawal) {
                // A. JIKA STATUS BERUBAH MENJADI SUKSES DARI PENDING
                if ($status === 'SUCCESS' && $withdrawal->status === 'pending') {
                    $withdrawal->update(['status' => 'approved']);
                    
                    \App\Models\WalletMutation::create([
                        'website_id' => $withdrawal->website_id,
                        'amount' => $withdrawal->amount,
                        'type' => 'debit',
                        'description' => "Pencairan dana manual ke rekening {$withdrawal->bank_name} sukses."
                    ]);
                    \Illuminate\Support\Facades\Log::info("✅ Withdrawal {$orderNumber} SUKSES via Webhook.");
                } 
                
                // B. JIKA STATUS DITOLAK BANK (FAILED)
                elseif ($status === 'FAILED' && $withdrawal->status !== 'rejected') {
                    // Cek apakah sebelumnya statusnya sudah terlanjur "approved" (Saldo sudah dipotong)
                    $wasApproved = ($withdrawal->status === 'approved');

                    // Ubah jadi Ditolak
                    $withdrawal->update([
                        'status' => 'rejected',
                        'admin_note' => 'Penarikan digagalkan oleh pihak Bank (Rekening tidak valid/Downstream Error).'
                    ]);

                    // 🚨 FITUR REFUND OTOMATIS: Jika saldo telanjur dipotong, kembalikan uangnya!
                    if ($wasApproved) {
                        \App\Models\WalletMutation::create([
                            'website_id' => $withdrawal->website_id,
                            'amount' => $withdrawal->amount,
                            'type' => 'credit', // Credit = Uang masuk kembali
                            'description' => "Refund pengembalian dana karena pencairan ditolak oleh Bank."
                        ]);
                        \Illuminate\Support\Facades\Log::info("🔄 Mutasi Refund (Pengembalian Saldo) dibuat untuk {$orderNumber}.");
                    } else {
                        \Illuminate\Support\Facades\Log::info("❌ Withdrawal {$orderNumber} FAILED ditolak oleh Bank.");
                    }
                }
            }
            
            return response()->json(['status' => 'success', 'message' => 'Withdrawal Webhook Processed'], 200);
        }

        // =========================================================================
        // 🚨 3. JALUR NORMAL: TRANSAKSI PEMBAYARAN PESANAN PEMBELI (PAYMENT)
        // =========================================================================
        $order = \App\Models\Order::with(['items.product', 'items.variant', 'website.accurateIntegration'])
                      ->where('order_number', $orderNumber)
                      ->first();
        
        if (!$order) {
            \Illuminate\Support\Facades\Log::error("Webhook Gagal: Pesanan {$orderNumber} tidak ditemukan di DB.");
            return response()->json(['status' => 'error', 'message' => "Order not found: {$orderNumber}"], 200);
        }

        $website = $order->website;

        // Panggil PivotService dengan konteks Toko (Website)
        $paymentService = new \App\Services\Payment\PivotService($website);
        $result = $paymentService->handleWebhook($request);

        if (!$result['is_valid']) {
            \Illuminate\Support\Facades\Log::error("Webhook Ditolak: Invalid Signature untuk order {$orderNumber}");
            return response()->json(['status' => 'error', 'message' => 'Invalid Signature'], 403);
        }
        // ==========================================
        // SKENARIO 1: PEMBAYARAN SUKSES
        // ==========================================
        if ($result['status'] === 'paid') {
            if ($order->status !== 'processing') {
                
                $order->update([
                    'status'         => 'processing', 
                    'payment_status' => 'paid',
                    'payment_method' => 'pivot',
                    'bank_name'      => $result['payment_method'],
                    'payment_proof'  => 'otomatis_gateway_verified', 
                ]);
                
                \App\Models\OrderHistory::create([
                    'order_id' => $order->id,
                    'status'   => 'processing',
                    'note'     => "Pembayaran lunas via Pivot ({$result['payment_method']}) otomatis diverifikasi."
                ]);

                // Integrasi Accurate
                try {
                    if ($website->accurateIntegration && $website->accurateIntegration->access_token) {
                        $accurateService = new \App\Services\AccurateService($website);
                        $invoiceCreated = $accurateService->syncSalesInvoice($order);
                        
                        if ($invoiceCreated) {
                            $accurateService->syncPaymentReceipt($order);
                            Log::info("Webhook Success: Faktur & Pembayaran Accurate berhasil dibuat untuk {$orderNumber}");
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Accurate Webhook Trigger Error untuk {$orderNumber}: " . $e->getMessage());
                }
            }
        }
        // ==========================================
        // SKENARIO 2: PEMBAYARAN GAGAL / EXPIRED
        // ==========================================
        elseif ($result['status'] === 'failed') {
            if ($order->status !== 'cancelled') {
                
                $order->update(['status' => 'cancelled']);
                
                \App\Models\OrderHistory::create([
                    'order_id' => $order->id,
                    'status'   => 'cancelled',
                    'note'     => "Pembayaran gagal/expired oleh sistem. Stok dikembalikan otomatis."
                ]);
                
                // Logika Pengembalian Stok
                foreach ($order->items as $item) {
                    if ($item->variant_id && $item->variant) {
                        $item->variant->increment('stock', $item->qty);
                        if ($item->product) {
                            $item->product->increment('stock', $item->qty);
                        }
                    } elseif ($item->product) {
                        $item->product->increment('stock', $item->qty);
                    }
                }
                
                Log::info("Webhook Cancel: Stok untuk pesanan {$orderNumber} berhasil dikembalikan ke etalase.");
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Webhook Processed'], 200);
    }
public function handleAccurateWebhook(Request $request)
    {
        // 1. Tangkap semua data
        $payloads = $request->all();
        \Illuminate\Support\Facades\Log::info('🤖 WEBHOOK ACCURATE MASUK', $payloads);

        foreach ($payloads as $payload) {
            $databaseId = $payload['databaseId'] ?? null;
            if (!$databaseId) continue;

            // 🚨 FIX: Ambil SEMUA website yang terkoneksi ke Database ID ini
            $integrations = \App\Models\AccurateIntegration::where('accurate_database_id', $databaseId)->get();
            
            if ($integrations->isEmpty()) {
                \Illuminate\Support\Facades\Log::warning("Webhook ditolak: Database ID {$databaseId} tidak ditemukan.");
                continue; 
            }

            // 3. Proses jika tipe event-nya adalah Perubahan Barang (ITEM)
            if (($payload['type'] ?? '') === 'ITEM' && isset($payload['data'])) {
                foreach ($payload['data'] as $itemData) {
                    $sku = $itemData['itemNo'] ?? null;
                    $action = $itemData['action'] ?? '';

                    if ($sku === 'ONGKIR-Deus' || $sku === 'ONGKIR') {
                        continue; 
                    }

                    if ($sku && $action === 'WRITE') {
                        
                        // 🚨 Looping untuk mengeksekusi semua toko yang berhak
                        foreach ($integrations as $integration) {
                            $website = $integration->website;
                            if (!$website) continue;
                            
                            // Lempar tugas ke Background Job agar WebhookController langsung selesai.
                            \App\Jobs\SyncAccurateItemJob::dispatch($website->id, $sku);
                        }
                    }
                }
            }
        }

        // 🚨 KUNCI UTAMA: Membalas Accurate dalam hitungan milidetik
        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Fungsi Bantuan: Menarik Harga & Stok 1 Barang Saja
     */
    private function updateSingleItemFromAccurate($sku, AccurateService $accurateService, $websiteId)
    {
        try {
            // Buka sesi Accurate (Meminjam fungsi dari AccurateService Anda)
            // Catatan: Pastikan openDatabaseSession() di AccurateService diubah menjadi 'public' 
            // agar bisa dipanggil dari sini, atau pindahkan logika API panggil detail ke dalam AccurateService.
            
            // Asumsi kita buat fungsi getSingleItemDetail() di AccurateService
            $itemDetail = $accurateService->getSingleItemDetail($sku);

            if ($itemDetail) {
                $price = $itemDetail['unitPrice'] ?? 0;
                $stock = $itemDetail['availableToSell'] ?? $itemDetail['balance'] ?? $itemDetail['quantity'] ?? 0;
                // Update ke Tabel Products (Induk)
                Product::where('website_id', $websiteId)
                       ->where('sku', $sku)
                       ->update(['price' => $price, 'stock' => $stock]);

                // Update ke Tabel Varian (Jika dia anak varian)
                ProductVariant::whereHas('product', function($q) use ($websiteId) {
                    $q->where('website_id', $websiteId);
                })->where('sku', $sku)->update(['price' => $price, 'stock' => $stock]);

                Log::info("✅ SINKRONISASI OTOMATIS SUKSES: SKU {$sku} di-update menjadi Rp {$price} | Stok: {$stock}");
            }
        } catch (\Exception $e) {
            Log::error("❌ GAGAL SINKRONISASI SKU {$sku}: " . $e->getMessage());
        }
    }
}