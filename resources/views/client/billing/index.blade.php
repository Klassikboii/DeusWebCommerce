@extends('layouts.client')

@section('title', 'Langganan & Tagihan')

@section('content')
<div class="container-fluid p-0" style="max-width: 900px;">
    
    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-danger shadow-sm py-4 mb-4 text-center">
            <div class="fs-1 mb-2">⛔</div>
            <h4 class="fw-bold">AKSES DITUTUP</h4>
            <p class="mb-0 fs-5">{{ session('error') }}</p>
        </div>
    @endif

    @if($pendingTransaction)
        <div class="alert alert-warning border-0 shadow-sm mb-4">
            <h5 class="alert-heading fw-bold"><i class="bi bi-clock-history me-2"></i> Menunggu Verifikasi</h5>
            <p class="m-0">
                Anda telah melakukan upgrade ke paket <strong>{{ $pendingTransaction->package->name }}</strong>. 
                Admin sedang mengecek bukti transfer Anda. Fitur baru akan aktif setelah disetujui.
            </p>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h6 class="text-muted text-uppercase small fw-bold mb-3">Paket Aktif</h6>
                    
                    @if($currentSubscription)
                        <h3 class="fw-bold text-primary mb-1">{{ $currentSubscription->package->name }}</h3>
                        <div class="text-muted small mb-4">
                            Exp: {{ $currentSubscription->ends_at ? $currentSubscription->ends_at->format('d M Y') : 'Selamanya' }}
                        </div>

                        <ul class="list-group list-group-flush small mb-0">
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>Limit Produk</span>
                                <strong>{{ $currentSubscription->package->max_products }} Item</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>Custom Domain</span>
                                <span>{!! $currentSubscription->package->can_custom_domain ? '<i class="bi bi-check text-success"></i>' : '<i class="bi bi-x text-muted"></i>' !!}</span>
                            </li>
                        </ul>
                    @else
                       <div class="text-center py-4">
                            <i class="bi bi-exclamation-circle text-danger fs-1 mb-2"></i>
                            <h4 class="fw-bold">Paket Tidak Aktif</h4>
                            <p class="text-muted small">Masa langganan Anda telah habis atau belum dipilih. Fitur Premium dinonaktifkan.</p>
                            <div class="alert alert-light border small">
                                Saat ini Anda berjalan dalam mode <strong>Basic / Terbatas</strong>.
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold">Upgrade Paket</h6>
                </div>
                <div class="card-body p-4">
                    
                    <form action="{{ route('client.billing.store', $website->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Pilih Paket</label>
                            <select name="package_id" class="form-select @error('package_id') is-invalid @enderror" id="packageSelect" required>
                                <option value="" selected disabled>-- Pilih Paket --</option>
                                @foreach($packages as $pkg)
                                    <option value="{{ $pkg->id }}" data-price="{{ $pkg->price }}">
                                        {{ $pkg->name }} - Rp {{ number_format($pkg->price, 0, ',', '.') }}/bln
                                    </option>
                                @endforeach
                            </select>
                            @error('package_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="bg-light p-3 rounded mb-3 border">
                            <small class="d-block text-muted mb-1">Silakan transfer ke:</small>
                            <div class="fw-bold">BCA 123-456-7890 (PT WebCommerce)</div>
                            <div class="fw-bold" id="totalDisplay">Total: Rp -</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">Upload Bukti Transfer</label>
                            <input type="file" name="proof_image" class="form-control @error('proof_image') is-invalid @enderror" accept="image/*" required>
                            <div class="form-text">Maksimal 2MB (JPG/PNG).</div>
                            
                            @error('proof_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100" {{ $pendingTransaction ? 'disabled' : '' }}>
                            {{ $pendingTransaction ? 'Sedang Diproses...' : 'Konfirmasi Pembayaran' }}
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Script sederhana update harga saat pilih paket
    document.getElementById('packageSelect').addEventListener('change', function() {
        const price = this.options[this.selectedIndex].getAttribute('data-price');
        const formatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(price);
        document.getElementById('totalDisplay').innerText = 'Total: ' + formatted;
    });
</script>
@endsection