@extends('layouts.' . ($website->active_template ?? 'modern'))

@section('title', 'Pembayaran - ' . $order->order_number)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            
            {{-- HEADER SUKSES --}}
            <div class="text-center mb-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                <h4 class="fw-bold mt-2">Pesanan Diterima!</h4>
                <p class="text-muted">Terima kasih, mohon selesaikan pembayaran.</p>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="mb-0 fw-bold text-center">Rincian Pembayaran</h5>
                </div>
                <div class="card-body p-4 pt-0">
                <div class="card-body p-4 pt-0">
                    {{-- TOTAL TAGIHAN (Sudah Lunas Bersih dari Controller) --}}
                    <div class="text-center mb-4 bg-light rounded p-3">
                        <p class="text-muted mb-1 small text-uppercase fw-bold">Total Tagihan</p>
                        <h2 class="fw-bold mb-0"  style="color: var(--primary-color);">
                            {{-- LANGSUNG PANGGIL total_amount KARENA SUDAH MENGANDUNG ONGKIR & DISKON --}}
                            Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                        </h2>
                        
                        <div class="mt-3">
                            <span class="badge bg-secondary fs-6 py-2 px-3 shadow-sm" style="cursor: pointer;" onclick="copyOrderNumber()" title="Klik untuk menyalin">
                                <span id="orderNumberText">{{ $order->order_number }}</span>
                                <i class="bi bi-files ms-2 text-warning"></i>
                            </span>
                            <div class="small text-muted mt-1" style="font-size: 0.75rem;">Simpan nomor ini untuk melacak pesanan Anda</div>
                        </div>
                    </div>

                    {{-- RINCIAN KECIL --}}
                    <div class="px-2 mb-3 border-bottom pb-2">
                        @php
                            // Kita hitung mundur Subtotal Produk Asli: Total Akhir - Ongkir + Diskon
                            $subtotalAsli = $order->total_amount - $order->shipping_cost + $order->discount_amount;
                        @endphp
                        
                        <div class="d-flex justify-content-between text-muted small mb-2">
                            <span>Subtotal Produk:</span>
                            <span>Rp {{ number_format($subtotalAsli, 0, ',', '.') }}</span>
                        </div>
                        
                        {{-- Tampilkan Baris Diskon Jika Ada --}}
                        @if($order->discount_amount > 0)
                            <div class="d-flex justify-content-between text-success small mb-2 fw-bold">
                                <span>Diskon Voucher:</span>
                                <span>-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between text-muted small">
                            <span>Ongkir ({{ $order->courier_name ?? 'Kurir Toko' }}):</span>
                            <span>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- INFO REKENING --}}
                    <div class="alert alert-info border-0 d-flex align-items-start gap-3">
                        <i class="bi bi-bank fs-4"  style="color: var(--primary-color);"></i>
                        <div>
                            <p class="mb-1 small text-muted">Silakan transfer ke:</p>
                            @if($website->bank_name && $website->bank_account_number)
                                <h6 class="fw-bold mb-0 text-uppercase">{{ $website->bank_name }}</h6>
                                <div class="fs-5 fw-bold mb-1 font-monospace">{{ $website->bank_account_number }}</div>
                                <div class="small">a.n {{ $website->bank_account_name }}</div>
                            @else
                                <strong class="text-danger small">Belum ada info rekening. Hubungi Admin.</strong>
                            @endif
                        </div>
                    </div>

                    {{-- 
                        === LOGIKA TAMPILAN UTAMA === 
                        Jika SUDAH upload bukti DAN status BUKAN pending (artinya sudah diproses/selesai)
                        -> Tampilkan Pesan Sukses
                    --}}
                    {{-- 
                            === LOGIKA TAMPILAN UTAMA (BARU) === 
                            Sembunyikan form jika:
                            1. Status pembayaran sudah 'paid' (Lunas via Midtrans atau Admin)
                            2. ATAU Status order sedang menunggu konfirmasi (Manual upload)
                            3. ATAU Status order sudah diproses/dikirim (Aman)
                        --}}
                        @if($order->payment_status == 'paid' || in_array($order->status, ['awaiting_confirmation', 'processing', 'shipped', 'completed']))
                            
                            <div class="alert alert-success text-center mt-4">
                                <i class="bi bi-check-circle-fill fs-1 d-block mb-2"></i>
                                
                                {{-- Pesan disesuaikan --}}
                                @if($order->status == 'awaiting_confirmation')
                                    <h6 class="fw-bold">Bukti Sedang Dicek</h6>
                                    <p class="small mb-0">Terima kasih, admin kami sedang memverifikasi transfer Anda.</p>
                                @else
                                    <h6 class="fw-bold">Pembayaran Berhasil!</h6>
                                    <p class="small mb-0">Pesanan Anda sudah lunas dan sedang diproses.</p>
                                @endif
                                
                                @if($order->bank_name)
                                    <span class="badge bg-light text-dark border mt-2">
                                        Metode: {{ $order->bank_name }}
                                    </span>
                                @endif
                            </div>
                            
                            <div class="d-grid mt-3">
                                <a href="{{ route('store.home') }}" class="btn btn-outline-primary">Kembali ke Toko</a>
                            </div>

                        @else
                            {{-- Form upload manual dan tombol bayar Midtrans ada di sini... --}}
                        
                        {{-- OPSI 1: TOMBOL WHATSAPP (Prioritas Interaksi) --}}
                        @php
                            $phone = $website->whatsapp_number;
                            if (Str::startsWith($phone, '0')) { $phone = '62' . substr($phone, 1); }
                            
                            $message = "Halo kak, saya sudah transfer untuk pesanan *" . $order->order_number . "*\n";
                            $message .= "Total: Rp " . number_format($order->grand_total, 0, ',', '.') . "\n";
                            $message .= "Mohon dicek ya. Terima kasih.";
                            
                            $waUrl = "https://wa.me/" . $phone . "?text=" . urlencode($message);
                        @endphp

                        <div class="d-grid gap-2 mb-4">
                            @if($website->whatsapp_number)
                                <a href="{{ $waUrl }}" target="_blank" class="btn btn-success fw-bold py-2">
                                    <i class="bi bi-whatsapp me-2"></i> Konfirmasi via WhatsApp
                                </a>
                            @endif
                        </div>

                       {{-- ================================================= --}}
                        {{-- BLOK PEMBAYARAN OTOMATIS PIVOT (JIKA ADA URL) --}}
                        {{-- ================================================= --}}
                        @if(isset($paymentUrl) && $paymentUrl)
                            <div class="card border-primary shadow-sm mb-4 border">
                                <div class="card-body text-center p-4">
                                    <h5 class="fw-bold text-primary mb-3">Bayar Lebih Cepat & Otomatis!</h5>
                                    <p class="text-muted mb-4">Selesaikan pembayaran Anda menggunakan Virtual Account, e-Wallet, atau QRIS. Status pesanan akan otomatis lunas tanpa perlu upload bukti.</p>
                                    
                                    {{-- 🚨 TOMBOL PIVOT: Langsung mengarah ke URL Pivot --}}
                                    <a href="{{ $paymentUrl }}" target="_self" class="btn btn-primary btn-lg px-5 rounded-pill shadow">
                                        <i class="bi bi-shield-lock me-2"></i> Lanjutkan Pembayaran
                                    </a>
                                </div>
                            </div>
                            
                            <div class="text-center text-muted mb-4 fw-bold">--- ATAU MANUAL ---</div>
                        @endif
                        {{-- ================================================= --}}

                        {{-- OPSI 2: FORM UPLOAD MANUAL (Database) --}}
                        
                        {{-- Peringatan Re-Upload --}}
                        @if($order->payment_proof && $order->status == 'pending')
                            <div class="alert alert-warning text-center small py-2 mb-3">
                                <i class="bi bi-info-circle me-1"></i> Bukti sebelumnya sedang dicek. Anda boleh upload ulang jika perlu revisi.
                            </div>
                        @endif

                        <form action="{{ route('store.payment.confirm', [ 'order_number' => $order->order_number]) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Bank Pengirim (Opsional)</label>
                                <input type="text" name="bank_name" class="form-control form-control-sm" 
                                       placeholder="Contoh: BCA / Dana / Gopay" 
                                       value="{{ old('bank_name', $order->bank_name) }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Foto Bukti Transfer <span class="text-danger">*</span></label>
                                <input type="file" id="payment_proof_input" name="payment_proof" class="form-control form-control-sm" accept=".jpg, .jpeg, .png" required>
                                <div class="form-text small">Format: JPG/PNG. Max 2MB.</div>
                                
                                <div id="file_error_message" class="text-danger small mt-1" style="display: none;"></div>
                            </div>

                            <button type="submit" class="btn w-100 fw-bold btn-outline-primary">
                                <i class="bi bi-cloud-upload me-2"></i> 
                                {{ $order->payment_proof ? 'Kirim Ulang Bukti' : 'Upload Bukti Manual' }}
                            </button>
                        </form>

                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('payment_proof_input');
    const errorMessage = document.getElementById('file_error_message');

    // Mencegah error jika element tidak ditemukan (misal: order sudah dibayar, form disembunyikan)
    if(!fileInput) return;

    fileInput.addEventListener('change', function () {
        errorMessage.style.display = 'none';
        errorMessage.innerText = '';

        const file = this.files[0];

        if (file) {
            const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!validTypes.includes(file.type)) {
                errorMessage.innerText = 'Format file tidak didukung! Harap gunakan JPG, JPEG, atau PNG.';
                errorMessage.style.display = 'block';
                this.value = ''; 
                return;
            }

            const maxSize = 2 * 1024 * 1024;
            if (file.size > maxSize) {
                errorMessage.innerText = 'Ukuran file terlalu besar! Maksimal 2MB. (Ukuran file Anda: ' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
                errorMessage.style.display = 'block';
                this.value = ''; 
                return;
            }
        }
    });
});
</script>
<script>
    function copyOrderNumber() {
        var copyText = document.getElementById("orderNumberText").innerText;
        
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(copyText).then(function() {
                alert("Nomor Pesanan (" + copyText + ") berhasil disalin!");
            });
        } else {
            let textArea = document.createElement("textarea");
            textArea.value = copyText;
            textArea.style.position = "fixed";
            textArea.style.left = "-999999px";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                alert("Nomor Pesanan (" + copyText + ") berhasil disalin!");
            } catch (err) {
                alert("Gagal menyalin. Silakan block dan copy manual teksnya.");
            }
            
            textArea.remove();
        }
    }
</script>
@endsection