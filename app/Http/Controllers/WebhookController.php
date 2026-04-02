<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderHistory;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleMidtrans(Request $request)
    {
        $payload = $request->all();
        Log::info('Midtrans Webhook Masuk: ', $payload);

        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;

        if (!$orderId) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        // 🚨 UPDATE PENTING: Panggil juga relasi product dan variant agar tidak error saat restock!
        $order = Order::with(['items.product', 'items.variant'])->where('order_number', $orderId)->first();
        
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $website = $order->website;
        $serverKey = $website->midtrans_server_key;

        if (!$serverKey) {
            Log::error("Toko {$website->site_name} tidak memiliki Server Key.");
            return response()->json(['message' => 'Server key missing'], 500);
        }

        $calculatedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        
        if ($calculatedSignature !== $signatureKey) {
            Log::error("Peringatan: Signature Midtrans Tidak Cocok untuk Order {$orderId}!");
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // ==========================================
        // SKENARIO 1: PEMBAYARAN SUKSES
        // ==========================================
        // Tambahkan variabel pembaca payment_type di bagian atas fungsi (di bawah $transactionStatus)
        $paymentType = $payload['payment_type'] ?? null;

        // ==========================================
        // SKENARIO 1: PEMBAYARAN SUKSES
        // ==========================================
        if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
            if ($order->status !== 'processing') {
                
                // --- LOGIKA DETEKTIF METODE PEMBAYARAN ---
                $metodePembayaran = 'Midtrans Otomatis'; // Default
                if ($paymentType == 'bank_transfer') {
                    $vaNumber = $payload['va_numbers'][0]['bank'] ?? '';
                    $metodePembayaran = 'Virtual Account ' . strtoupper($vaNumber);
                } elseif ($paymentType == 'echannel') {
                    $metodePembayaran = 'Mandiri Bill Payment';
                } elseif ($paymentType == 'qris') {
                    $metodePembayaran = 'QRIS (Gopay/OVO/Dana/LinkAja)';
                } elseif ($paymentType == 'gopay') {
                    $metodePembayaran = 'GoPay';
                } elseif ($paymentType == 'shopeepay') {
                    $metodePembayaran = 'ShopeePay';
                }

                // 🚨 UPDATE SUPER LENGKAP KE DATABASE 🚨
                $order->update([
                    'status'         => 'processing',
                    'payment_status' => 'paid',            // Ubah jadi lunas!
                    'bank_name'      => $metodePembayaran, // Masukkan metode (QRIS/VA)
                    'payment_proof'  => 'otomatis_midtrans_verified', // Gembok penanda
                ]);
                
                OrderHistory::create([
                    'order_id' => $order->id,
                    'status' => 'processing',
                    'note' => "Pembayaran lunas ({$metodePembayaran}) diverifikasi otomatis oleh Midtrans."
                ]);

                try {
                    if ($website->accurateIntegration && $website->accurateIntegration->access_token) {
                        $accurateService = new \App\Services\AccurateService($website);
                        $invoiceCreated = $accurateService->syncSalesInvoice($order);
                        if ($invoiceCreated) {
                            $accurateService->syncPaymentReceipt($order);
                            Log::info("Webhook Success: Faktur & Pembayaran Accurate berhasil dibuat untuk {$orderId}");
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Accurate Webhook Trigger Error untuk {$orderId}: " . $e->getMessage());
                }
            }

        // ==========================================

        // ==========================================
        // 🚨 SKENARIO 2: PEMBAYARAN GAGAL / EXPIRED
        // ==========================================
        } elseif ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
            
            if ($order->status !== 'cancelled') {
                $order->update(['status' => 'cancelled']);
                
                OrderHistory::create([
                    'order_id' => $order->id,
                    'status' => 'cancelled',
                    'note' => "Pembayaran {$transactionStatus} oleh Midtrans. Stok dikembalikan otomatis."
                ]);
                
                // 🚨 LOGIKA PENGEMBALIAN STOK (RESTOCK)
                foreach ($order->items as $item) {
                    // Jika barang varian
                    if ($item->variant_id && $item->variant) {
                        $item->variant->increment('stock', $item->qty);
                        // Jika produk induknya juga pakai sistem stok total, kembalikan juga
                        if ($item->product) {
                            $item->product->increment('stock', $item->qty);
                        }
                    } 
                    // Jika barang biasa
                    elseif ($item->product) {
                        $item->product->increment('stock', $item->qty);
                    }
                }
                
                Log::info("Webhook Cancel: Stok untuk pesanan {$orderId} berhasil dikembalikan ke etalase.");
            }
        }

        return response()->json(['message' => 'OK'], 200);
    }
}