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
   public function handlePaymentWebhook(Request $request)
    {
        $payload = $request->all();
        \Illuminate\Support\Facades\Log::info('Payment Webhook Masuk: ', $payload);

        // 1. Cari Order ID (Bisa dari Midtrans 'order_id' atau Pivot 'reference_no')
        $orderId = $request->order_id ?? $request->reference_no;

        if (!$orderId) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        // Mempertahankan "🚨 UPDATE PENTING" dari kode Anda
        $order = Order::with(['items.product', 'items.variant'])->where('order_number', $orderId)->first();
        
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $website = $order->website;

        // 2. Panggil Factory & Service
        $paymentGateway = \App\Services\Payment\PaymentFactory::make($website);
        $result = $paymentGateway->handleWebhook($request);

        // Keamanan: Cek apakah validasi signature di dalam Service lolos
        if (!$result['is_valid']) {
            \Illuminate\Support\Facades\Log::error("Webhook Ditolak: " . ($result['message'] ?? 'Signature Error'));
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // ==========================================
        // SKENARIO 1: PEMBAYARAN SUKSES
        // ==========================================
        if ($result['status'] === 'paid') {
            if ($order->status !== 'processing') {
                
                $order->update([
                    'status'         => 'processing',
                    'payment_status' => 'paid',            
                    'bank_name'      => $result['payment_method'], // Hasil terjemahan Service
                    'payment_proof'  => 'otomatis_gateway_verified', 
                ]);
                
                OrderHistory::create([
                    'order_id' => $order->id,
                    'status' => 'processing',
                    'note' => "Pembayaran lunas ({$result['payment_method']}) diverifikasi otomatis oleh sistem."
                ]);

                // Integrasi Accurate Anda yang dipertahankan utuh
                try {
                    if ($website->accurateIntegration && $website->accurateIntegration->access_token) {
                        $accurateService = new \App\Services\AccurateService($website);
                        $invoiceCreated = $accurateService->syncSalesInvoice($order);
                        if ($invoiceCreated) {
                            $accurateService->syncPaymentReceipt($order);
                            \Illuminate\Support\Facades\Log::info("Webhook Success: Faktur & Pembayaran Accurate berhasil dibuat untuk {$orderId}");
                        }
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Accurate Webhook Trigger Error untuk {$orderId}: " . $e->getMessage());
                }
            }
        }
        // ==========================================
        // SKENARIO 2: PEMBAYARAN GAGAL / EXPIRED
        // ==========================================
        elseif ($result['status'] === 'failed') {
            if ($order->status !== 'cancelled') {
                
                $order->update(['status' => 'cancelled']);
                
                OrderHistory::create([
                    'order_id' => $order->id,
                    'status' => 'cancelled',
                    'note' => "Pembayaran gagal/expired oleh sistem. Stok dikembalikan otomatis."
                ]);
                
                // Logika Restock Ganda Anda yang dipertahankan utuh
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
                
                \Illuminate\Support\Facades\Log::info("Webhook Cancel: Stok untuk pesanan {$orderId} berhasil dikembalikan ke etalase.");
            }
        }

        return response()->json(['message' => 'OK'], 200);
    }
    // App\Http\Controllers\WebhookController.php


public function handleAccurateWebhook(Request $request)
    {
        // 1. Tangkap semua data (Accurate mengirimnya dalam bentuk Array of Objects)
        $payloads = $request->all();
        Log::info('🤖 WEBHOOK ACCURATE DIPROSES:', $payloads);

        foreach ($payloads as $payload) {
            
            // 2. Cari Website/Toko siapa yang terhubung dengan Database ID ini
            $databaseId = $payload['databaseId'] ?? null;
            if (!$databaseId) continue;

            $integration = AccurateIntegration::where('accurate_database_id', $databaseId)->first();
            
            // Jika database ID tidak dikenali di sistem kita, abaikan.
            if (!$integration) {
                Log::warning("Webhook ditolak: Database ID {$databaseId} tidak ditemukan di sistem.");
                continue; 
            }

            $website = $integration->website;
            $accurateService = new AccurateService($website);

            // 3. Proses jika tipe event-nya adalah Perubahan Barang (ITEM)
            if (($payload['type'] ?? '') === 'ITEM' && isset($payload['data'])) {
                
                foreach ($payload['data'] as $itemData) {
                    $sku = $itemData['itemNo'] ?? null;
                    $action = $itemData['action'] ?? ''; // Biasanya "WRITE" (Tambah/Update) atau "DELETE"

                    // 🚨 TAMBAHKAN BLOKIRAN INI
                    // Jika SKU-nya adalah ongkir, langsung lewati (jangan simpan ke DB)
                    if ($sku === 'ONGKIR-Deus' || $sku === 'ONGKIR') {
                        continue; 
                    }

                    if ($sku && $action === 'WRITE') {
                        // 4. JALANKAN OPERASI UPDATE (Tarik detail tunggal dari Accurate)
                        $this->updateSingleItemFromAccurate($sku, $accurateService, $website->id);
                    }
                }
            }
        }

        // Wajib balas 200 OK dengan cepat
        return response()->json(['status' => 'success']);
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