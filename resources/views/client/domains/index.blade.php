@extends('layouts.client')

@section('title', 'Pengaturan Domain')

@section('content')
<div class="container-fluid p-0" style="max-width: 800px;">
    <div class="mb-4">
        <h4 class="fw-bold mb-1">Pengaturan Alamat Web</h4>
        <p class="text-muted m-0">Kelola identitas tautan toko Anda di internet.</p>
    </div>

    {{-- PESAN NOTIFIKASI --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><strong>Berhasil!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Gagal!</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="bi bi-x-circle-fill me-2"></i><strong>Periksa kembali form Anda:</strong>
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @php
        $website->loadMissing('subscription.package');
        $canUseCustomDomain = $website->subscription?->package?->can_custom_domain === true;
        
        $mainDomain = parse_url(config('app.url'), PHP_URL_HOST);
        $port = app()->environment('local') ? ':8000' : ''; 
        
        if (!empty($website->custom_domain)) {
            $storeUrl = 'http://' . $website->custom_domain . $port;
        } else {
            $storeUrl = 'http://' . $website->subdomain . '.' . $mainDomain . $port;
        }
    @endphp

    {{-- KOTAK INFORMASI URL AKTIF --}}
    <div class="card border-0 shadow-sm mb-4 bg-primary bg-opacity-10">
        <div class="card-body p-4 text-center">
            <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 60px; height: 60px;">
                <i class="bi bi-link-45deg fs-2 text-primary"></i>
            </div>
            <h6 class="mb-1 text-muted">Toko Anda dapat diakses di:</h6>
            <a href="{{ $storeUrl }}" target="_blank" class="fs-4 fw-bold text-decoration-none">
                {{ str_replace(['http://', ':8000'], '', $storeUrl) }}
            </a>
            @if(empty($website->custom_domain))
                <div class="badge bg-secondary mt-2">Subdomain Bawaan</div>
            @else
                <div class="badge bg-success mt-2"><i class="bi bi-shield-check me-1"></i>Custom Domain Aktif</div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-server me-2"></i>Alamat Web Bawaan (Subdomain)
                </div>
                <div class="card-body">
                    <form action="{{ route('client.domains.update', $website->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <label class="form-label small text-muted">Anda bisa mengganti nama alamat web Anda kapan saja. Perubahan ini akan segera berlaku.</label>
                        <div class="input-group mb-3">
                            <input type="text" name="subdomain" class="form-control text-end" value="{{ old('subdomain', $website->subdomain) }}" required>
                            <span class="input-group-text bg-light text-muted fw-bold">.{{ $mainDomain }}</span>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Simpan Subdomain</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-globe me-2"></i>Domain Pribadi (Custom Domain)
                </div>
                <div class="card-body">
                    
                    @if($canUseCustomDomain)
                        {{-- PANDUAN DNS --}}
                        <div class="alert alert-warning border-warning shadow-sm mt-4 mb-4">
                        <h6 class="fw-bold text-dark"><i class="bi bi-globe me-2"></i>Panduan Setup Custom Domain</h6>
                        <p class="small text-dark mb-2">Agar domain Anda (misal: <strong>namatoko.com</strong>) bisa diakses dan menampilkan toko ini, Anda <strong>wajib</strong> melakukan pengaturan pada panel penyedia domain Anda (seperti Niagahoster, Rumahweb, dll).</p>
                        
                        <div class="bg-white p-3 rounded border mb-3">
                            <ol class="small mb-0 text-dark" style="line-height: 1.8;">
                                <li>Login ke panel penyedia domain Anda.</li>
                                <li>Cari menu <strong>DNS Management</strong> atau <strong>Zone Editor</strong>.</li>
                                <li>Buat record baru dengan tipe <strong>A Record</strong>.</li>
                                <li>Isi kolom <em>Name/Host</em> dengan <strong>@</strong> (atau kosongkan).</li>
                                <li>Isi kolom <em>IPv4 / Value / Target</em> dengan IP Server kami: <span class="badge bg-dark fs-6 user-select-all ms-1">157.66.34.137</span></li>
                                <li>Simpan dan tunggu masa propagasi (biasanya memakan waktu 1 hingga 24 jam).</li>
                            </ol>
                        </div>
                        </div>

                        {{-- FORM SIMPAN CUSTOM DOMAIN --}}
                        <form action="{{ route('client.domains.update', $website->id) }}" method="POST" class="mb-4">
                            @csrf
                            @method('PUT')
                            {{-- 🚨 TRIK RAHASIA: Kirim subdomain lama secara tersembunyi agar validasi Controller tidak marah --}}
                            <input type="hidden" name="subdomain" value="{{ $website->subdomain }}">

                            <label class="form-label fw-bold">Masukkan Domain Anda</label>
                            <div class="input-group">
                                <input type="text" name="custom_domain" class="form-control" value="{{ old('custom_domain', $website->custom_domain) }}" placeholder="contoh: tokosaya.com">
                                <button type="submit" class="btn btn-dark">Simpan Domain</button>
                            </div>
                            <div class="form-text small">Kosongkan lalu klik simpan jika Anda ingin menghapus custom domain.</div>
                        </form>

                        <hr class="border-secondary opacity-25">

                        {{-- FORM PENGECEKAN DNS --}}
                        <h6 class="fw-bold small mb-2">Cek Status DNS:</h6>
                        <form action="{{ route('client.domains.check', $website->id) }}" method="POST" class="d-flex align-items-center gap-2">
                            @csrf
                            <div class="input-group input-group-sm w-auto">
                                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                <input type="text" name="domain_to_check" class="form-control" placeholder="contoh: tokosaya.com" required>
                                <button type="submit" class="btn btn-outline-dark">Cek Status</button>
                            </div>
                        </form>
                        <div class="form-text small mt-1 text-dark opacity-75">*Pengecekan ini mungkin akan menampilkan status "Belum Mengarah" jika Anda baru saja menyimpan DNS (masih dalam masa propagasi).</div>

                    @else
                        {{-- TAMPILAN TERKUNCI PAKET VALUE --}}
                        <div class="alert alert-secondary text-center mb-0 border border-secondary border-opacity-25">
                            <i class="bi bi-lock-fill fs-1 text-warning mb-2 d-block"></i>
                            <h6 class="fw-bold">Fitur Terkunci</h6>
                            <p class="small text-muted mb-3">Paket langganan Anda tidak mendukung penggunaan domain pribadi. Silakan tingkatkan paket Anda.</p>
                            <a href="{{ route('client.billing.index', $website->id) }}" class="btn btn-warning btn-sm fw-bold shadow-sm">
                                <i class="bi bi-star-fill me-1"></i> Upgrade Paket
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection