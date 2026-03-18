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
                    @php
                        $cart = $order->total_amount;
                        $ongkos = $order->shipping_cost;
                        $grandtotal = $cart + $ongkos;
                    @endphp
                    {{-- TOTAL TAGIHAN --}}
                    <div class="text-center mb-4 bg-light rounded p-3">
                        <p class="text-muted mb-1 small text-uppercase fw-bold">Total Tagihan</p>
                        <h2 class="fw-bold mb-0"  style="color: var(--primary-color);">
                            Rp {{ number_format($grandtotal, 0, ',', '.') }}
                        </h2>
                        <span class="badge bg-secondary mt-2">{{ $order->order_number }}</span>
                    </div>

                    {{-- RINCIAN KECIL --}}
                    <div class="d-flex justify-content-between px-2 mb-2 text-muted small border-bottom pb-2">
                        <span>Subtotal Produk:</span>
                        <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between px-2 mb-3 text-muted small">
                        <span>Ongkir ({{ $order->courier_name ?? 'Kurir Toko' }}):</span>
                        <span>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
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
                    @if($order->payment_proof && $order->status != 'pending' && $order->status != 'unpaid')
                        <div class="alert alert-success text-center mt-4">
                            <i class="bi bi-check-circle-fill fs-1 d-block mb-2"></i>
                            <h6 class="fw-bold">Pembayaran Dikonfirmasi</h6>
                            <p class="small mb-0">Status pesanan Anda saat ini: <strong>{{ ucfirst($order->status) }}</strong></p>
                        </div>
                        <div class="d-grid mt-3">
                            <a href="{{ route('store.home', $website->active_domain) }}" class="btn btn-outline-primary">Kembali ke Toko</a>
                        </div>

                    {{-- 
                        === JIKA BELUM SELESAI === 
                        Tampilkan Opsi: WhatsApp ATAU Upload Bukti
                    --}}
                    @else
                        
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
                            {{-- BLOK PEMBAYARAN OTOMATIS MIDTRANS (JIKA ADA TOKEN) --}}
                            {{-- ================================================= --}}
                            @if(isset($snapToken) && $snapToken)
                                <div class="card border-primary shadow-sm mb-4 border" >
                                    <div class="card-body text-center p-4">
                                        <h5 class="fw-bold text-primary mb-3">Bayar Lebih Cepat & Otomatis!</h5>
                                        <p class="text-muted mb-4">Gunakan metode pembayaran otomatis (Virtual Account, e-Wallet, QRIS, dll) agar pesanan Anda langsung diproses tanpa perlu upload bukti transfer.</p>
                                        
                                        <button id="pay-button" class="btn btn-primary btn-lg px-5 rounded-pill shadow">
                                            <i class="bi bi-shield-lock me-2"></i> Bayar Sekarang
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="text-center text-muted mb-4 fw-bold">--- ATAU ---</div>
                            @endif
                            {{-- ================================================= --}}

                        {{-- OPSI 2: FORM UPLOAD (Database) --}}
                        
                        {{-- Peringatan Re-Upload --}}
                        @if($order->payment_proof && $order->status == 'pending')
                            <div class="alert alert-warning text-center small py-2 mb-3">
                                <i class="bi bi-info-circle me-1"></i> Bukti sebelumnya sedang dicek. Anda boleh upload ulang jika perlu revisi.
                            </div>
                        @endif

                        <form action="{{ route('store.payment.confirm', ['subdomain' => $website->active_domain, 'order_number' => $order->order_number]) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Bank Pengirim (Opsional)</label>
                                <input type="text" name="bank_name" class="form-control form-control-sm" 
                                       placeholder="Contoh: BCA / Dana / Gopay" 
                                       value="{{ old('bank_name', $order->bank_name) }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Foto Bukti Transfer <span class="text-danger">*</span></label>
                                <input type="file" name="payment_proof" class="form-control form-control-sm" accept="image/*" required>
                                <div class="form-text small">Format: JPG/PNG. Max 2MB.</div>
                            </div>

                            <button type="submit" class="btn w-100 fw-bold btn-primary">
                                <i class="bi bi-cloud-upload me-2"></i> 
                                {{ $order->payment_proof ? 'Kirim Ulang Bukti' : 'Upload Bukti Pembayaran' }}
                            </button>
                        </form>

                    @endif

                </div>
            </div>

        </div>
    </div>
</div>
@if(isset($snapToken) && $snapToken)
    @php
        // Cek apakah klien ini pakai Production atau Sandbox
        $snapUrl = $website->midtrans_is_production 
            ? 'https://app.midtrans.com/snap/snap.js' 
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    @endphp

    <script src="{{ $snapUrl }}" data-client-key="{{ $website->midtrans_client_key }}"></script>

    <script>
        document.getElementById('pay-button').onclick = function () {
            // Panggil popup Snap menggunakan Token yang dikirim dari Controller
            window.snap.pay('{{ $snapToken }}', {
                onSuccess: function (result) {
                    // Pembayaran sukses! (Misal: pakai QRIS atau Gopay)
                    alert("Pembayaran berhasil! Terima kasih.");
                    window.location.reload(); 
                },
                onPending: function (result) {
                    // Menunggu pembayaran (Misal: user memilih bayar di Indomaret/VA)
                    alert("Menunggu pembayaran Anda. Silakan selesaikan instruksi pembayaran yang diberikan.");
                    window.location.reload();
                },
                onError: function (result) {
                    // Pembayaran gagal
                    alert("Mohon maaf, pembayaran gagal. Silakan coba lagi.");
                },
                onClose: function () {
                    // User menutup popup tanpa menyelesaikan pembayaran
                    alert('Anda menutup layar pembayaran sebelum menyelesaikannya.');
                }
            });
        };
    </script>
@endif
@endsection