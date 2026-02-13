@extends('layouts.modern')

@section('title', 'Konfirmasi Pembayaran')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Konfirmasi Pembayaran</h5>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <p class="text-muted mb-1">Total Tagihan (Termasuk Ongkir)</p>
                        {{-- PASTIKAN INI MENJUMLAHKAN SHIPPING_COST --}}
                        <h2 class="text-primary fw-bold">
                            Rp {{ number_format($order->total_amount + $order->shipping_cost, 0, ',', '.') }}
                        </h2>
                        <span class="badge bg-secondary">{{ $order->order_number }}</span>
                    </div>

                    {{-- Tampilkan Rincian Kecil --}}
                    <div class="d-flex justify-content-between px-5 mb-3 text-muted small">
                        <span>Subtotal Produk:</span>
                        <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between px-5 mb-3 text-muted small">
                        <span>Ongkos Kirim ({{ $order->courier_name }}):</span>
                        <span>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                    </div>
                    <hr>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-1"></i> Silakan transfer ke rekening berikut:
                        <br><strong>BCA 1234567890 a.n PT Deus Commerce</strong>
                    </div>

                    {{-- 
                        === PERBAIKAN LOGIKA === 
                        Hanya sembunyikan form jika:
                        1. Bukti Ada 
                        DAN 
                        2. Status BUKAN 'pending' (Artinya: Sedang dicek admin / Selesai)
                        
                        Jika status 'pending' (meski ada bukti lama), form tetap muncul agar bisa direvisi.
                    --}}
                    @if($order->payment_proof && $order->status != 'pending')
                    
                        <div class="alert alert-success text-center">
                            <i class="bi bi-check-circle-fill fs-1 d-block mb-2"></i>
                            Bukti pembayaran sudah dikirim.<br>
                            Status: <span class="badge bg-success">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                        </div>
                        <div class="d-grid">
                            <a href="{{ route('store.home', $website->subdomain) }}" class="btn btn-outline-primary">Kembali ke Toko</a>
                        </div>

                    @else
                    
                        {{-- Jika ini adalah Re-Upload (Status Pending tapi ada bukti lama), beri pesan peringatan --}}
                        @if($order->payment_proof && $order->status == 'pending')
                            <div class="alert alert-warning text-center small">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> 
                                <strong>Perhatian:</strong> Bukti pembayaran sebelumnya mungkin ditolak atau belum valid.<br>Silakan upload ulang bukti yang benar.
                            </div>
                        @endif

                        <form action="{{ route('store.payment.confirm', ['subdomain' => $website->subdomain, 'order_number' => $order->order_number]) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="mb-3">
                                <label class="form-label">Transfer dari Bank</label>
                                <input type="text" name="bank_name" class="form-control" 
                                       placeholder="Contoh: BCA / Mandiri / GoPay" 
                                       value="{{ old('bank_name', $order->bank_name) }}" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Upload Bukti Transfer</label>
                                <input type="file" name="payment_proof" class="form-control" accept="image/*" required>
                                <div class="form-text">Format: JPG, PNG. Max 2MB.</div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2">
                                <i class="bi bi-upload me-2"></i>
                                {{ $order->payment_proof ? 'Kirim Ulang Bukti' : 'Kirim Bukti Pembayaran' }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection