@extends('layouts.' . ($website->active_template ?? 'modern'))

@section('title', 'Cek Pesanan - ' . $website->site_name)

@section('content')
<div class="container py-5" style="min-height: 60vh;">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-search fs-1 text-primary"></i>
                        <h4 class="fw-bold mt-3">Cek Status Pesanan</h4>
                        <p class="text-muted small">
                            Masukkan Nomor Order dan No HP yang Anda gunakan saat checkout untuk melanjutkan pembayaran atau cek status.
                        </p>
                    </div>

                    {{-- Alert Error --}}
                    @if(session('error'))
                        <div class="alert alert-danger text-center small mb-3">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('store.track.check', $website->subdomain) }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase">Nomor Order</label>
                            <input type="text" name="order_number" class="form-control" placeholder="Contoh: ORD-12345..." required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase"> No WhatsApp</label>
                            <input type="text" name="contact" class="form-control" placeholder="Masukan  No HP..." required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-2 fw-bold">
                                Cari Pesanan
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4 border-top pt-3">
                        <a href="{{ route('store.home', $website->subdomain) }}" class="text-decoration-none small text-muted">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection