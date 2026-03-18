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
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>Bebas Watermark</span>
                                <span>{!! $currentSubscription->package->remove_branding ? '<i class="bi bi-check text-success"></i>' : '<i class="bi bi-x text-muted"></i>' !!}</span>
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
                               <label class="form-label fw-bold">Pilih Paket</label>
                                <select name="package_id" id="packageSelect" class="form-select" required>
                                    <option value="">-- Pilih Paket --</option>
                                    @foreach($packages as $package)
                                        {{-- 🚨 TITIPKAN DATA KOLOM DATABASE KE DALAM ATRIBUT data-* --}}
                                        <option value="{{ $package->id }}" 
                                                data-desc="{{ $package->description }}"
                                                data-max-products="{{ $package->max_products }}"
                                                data-domain="{{ $package->can_custom_domain }}"
                                                data-branding="{{ $package->remove_branding }}">
                                            {{ $package->name }} - Rp {{ number_format($package->price, 0, ',', '.') }} / {{ $package->duration_days }} Hari
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- KOTAK PREVIEW BENEFIT --}}
                                <div id="featuresPreviewBox" class="card border-primary shadow-sm d-none mb-4 mt-3">
                                    <div class="card-body bg-light">
                                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                                            <i class="bi bi-stars text-warning me-2"></i>Detail Paket: <span id="previewTitle" class="text-dark"></span>
                                        </h6>
                                        
                                        {{-- Deskripsi Paket --}}
                                        <p id="previewDesc" class="small text-muted mb-3 font-italic"></p>
                                        
                                        {{-- List Checklist Dinamis --}}
                                        <ul id="featuresList" class="mb-0 list-unstyled">
                                            {{-- Akan diisi oleh JS --}}
                                        </ul>
                                    </div>
                                </div>
                        <div class="alert alert-info border-0 d-flex align-items-start gap-3 mt-4">
                            <i class="bi bi-bank fs-4 text-primary"></i>
                            <div>
                                <p class="mb-1 small text-muted">Silakan transfer pembayaran langganan ke rekening berikut:</p>
                                @if($admin && $admin->bank_name)
                                    <h6 class="fw-bold mb-0 text-uppercase">{{ $admin->bank_name }}</h6>
                                    <div class="fs-5 fw-bold mb-1 font-monospace">{{ $admin->bank_account_number }}</div>
                                    <div class="small">a.n {{ $admin->bank_account_name }}</div>
                                @else
                                    <strong class="text-danger small">Belum ada info rekening Super Admin. Harap hubungi penyedia layanan.</strong>
                                @endif
                            </div>
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
</script><script>
document.addEventListener('DOMContentLoaded', function() {
    const packageSelect = document.getElementById('packageSelect');
    const featuresPreviewBox = document.getElementById('featuresPreviewBox');
    const featuresList = document.getElementById('featuresList');
    const previewDesc = document.getElementById('previewDesc');
    const previewTitle = document.getElementById('previewTitle');

    packageSelect.addEventListener('change', function() {
        // Ambil opsi yang sedang dipilih
        const option = this.options[this.selectedIndex];
        
        // Kosongkan list sebelumnya
        featuresList.innerHTML = '';
        previewDesc.innerHTML = '';
        previewTitle.innerHTML = '';

        if (this.value) {
            // 1. Ambil data dari atribut data-*
            const packageName = option.text.split(' - ')[0]; // Ambil nama paket saja
            const desc = option.getAttribute('data-desc');
            const maxProducts = option.getAttribute('data-max-products');
            const canDomain = option.getAttribute('data-domain') === '1';
            const removeBranding = option.getAttribute('data-branding') === '1';

            // 2. Isi Judul & Deskripsi
            previewTitle.innerText = packageName;
            if (desc && desc !== 'null') {
                previewDesc.innerText = '"' + desc + '"';
            }

            // 3. RENDER CHECKLIST BERDASARKAN DATABASE
            
            // Limit Produk (Selalu ada)
            featuresList.innerHTML += `
                <li class="mb-2">
                    <i class="bi bi-check-circle-fill text-success me-2"></i> 
                    Maksimal <b>${maxProducts} Produk</b>
                </li>`;

            // Custom Domain (Jika 1 Check Hijau, Jika 0 Silang Abu-abu)
            if (canDomain) {
                featuresList.innerHTML += `
                    <li class="mb-2">
                        <i class="bi bi-check-circle-fill text-success me-2"></i> 
                        Mendukung <b>Custom Domain</b> (Toko Anda.com)
                    </li>`;
            } else {
                featuresList.innerHTML += `
                    <li class="mb-2 text-muted">
                        <i class="bi bi-x-circle text-danger opacity-75 me-2"></i> 
                        <del>Custom Domain</del> <small>(Hanya Subdomain)</small>
                    </li>`;
            }

            // Remove Branding (Jika 1 Check Hijau, Jika 0 Silang Abu-abu)
            if (removeBranding) {
                featuresList.innerHTML += `
                    <li class="mb-2">
                        <i class="bi bi-check-circle-fill text-success me-2"></i> 
                        <b>Bebas Watermark</b> (Hapus Branding ASHOP)
                    </li>`;
            } else {
                featuresList.innerHTML += `
                    <li class="mb-2 text-muted">
                        <i class="bi bi-x-circle text-danger opacity-75 me-2"></i> 
                        <del>Bebas Watermark</del> 
                    </li>`;
            }

            // Tampilkan kotaknya
            featuresPreviewBox.classList.remove('d-none');
        } else {
            // Sembunyikan jika user memilih "-- Pilih Paket --"
            featuresPreviewBox.classList.add('d-none');
        }
    });
});
</script>
@endsection