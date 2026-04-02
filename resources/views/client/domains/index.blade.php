@extends('layouts.client')

@section('title', 'Pengaturan Domain')

@section('content')
<div class="container-fluid p-0" style="max-width: 800px;">
    <div class="mb-4">
        <h4 class="fw-bold mb-1">Custom Domain</h4>
        <p class="text-muted m-0">Gunakan nama domain Anda sendiri (misal: tokosaya.com).</p>
    </div>

    {{-- PESAN SUKSES --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong>Berhasil!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- PESAN ERROR / GAGAL --}}
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Gagal!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- PESAN ERROR VALIDASI (Misal ada form yang kosong / format salah) --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                    <i class="bi bi-x-circle-fill me-2"></i>
                    <strong>Periksa kembali form Anda:</strong>
                    <ul class="mb-0 mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        @php
                    $mainDomain = parse_url(config('app.url'), PHP_URL_HOST);
                    // Tambahkan port 8000 KHUSUS jika kita sedang di komputer lokal (Laragon)
                    $port = app()->environment('local') ? ':8000' : ''; 
                    
                    // Cek apakah klien ini punya Custom Domain? Jika ya, prioritaskan itu!
                    if (!empty($website->custom_domain)) {
                        $storeUrl = 'http://' . $website->custom_domain . $port;
                    } else {
                        $storeUrl = 'http://' . $website->subdomain . '.' . $mainDomain . $port;
                    }
                @endphp
    @if($website->domain_status == 'none' || empty($website->custom_domain))
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-globe fs-3 text-primary"></i>
                </div>
                <h5>Hubungkan Domain Anda</h5>
                <p class="text-muted small">
                    Saat ini toko Anda dapat diakses melalui: <br>
                    @if($website->custom_domain)
                    <a href="{{ $storeUrl }}" target="_blank" class="fw-bold text-decoration-none">
                        {{ $website->custom_domain }}
                    </a>
                    @else
                    <a href="{{ $storeUrl }}" target="_blank" class="fw-bold text-decoration-none">
                        <span class="fw-bold"> {{ $website->subdomain }}.deusserver.ashop.asia </span><br>
                    </a>
                     (Subdomain Gratis)
                    @endif
                    
                </p>
            </div>
                @php
                    $website->loadMissing('subscription.package');
                    $canUseCustomDomain = $website->subscription?->package?->can_custom_domain === true;
                    
                    // Ambil domain utama VPS kita agar otomatis dan dinamis
                    $mainDomain = parse_url(config('app.url'), PHP_URL_HOST); 
                @endphp
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-link me-2"></i>Alamat Web Bawaan (Subdomain)
            </div>
            <div class="card-body">
                <form action="{{ route('client.domains.update', $website->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <label class="form-label small text-muted">Anda bisa mengganti nama alamat web Anda kapan saja.</label>
                    <div class="input-group mb-3">
                        <input type="text" name="subdomain" class="form-control text-end" value="{{ old('subdomain', $website->subdomain) }}" required>
                        <span class="input-group-text bg-light text-muted">.{{ $mainDomain }}</span>
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
                    {{-- Tampilan untuk Klien Paket PRO/ELITE --}}
                    
                    {{-- (Masukkan kotak kuning Panduan DNS dan Pengecekan DNS di sini) --}}
                    
                    <form action="{{ route('client.domains.update', $website->id) }}" method="POST" class="mt-4">
                        @csrf
                        @method('PUT')
                        <label class="form-label small text-muted">Masukkan nama domain yang sudah Anda beli (contoh: tokosaya.com)</label>
                        <input type="text" name="custom_domain" class="form-control mb-3" value="{{ old('custom_domain', $website->custom_domain) }}" placeholder="namatoko.com">
                        
                        <button type="submit" class="btn btn-primary btn-sm">Simpan Custom Domain</button>
                    </form>
                    
                @else
                    {{-- Tampilan Terkunci untuk Klien Paket VALUE --}}
                    <div class="alert alert-secondary text-center mb-0 border border-secondary border-opacity-25">
                        <i class="bi bi-lock-fill fs-1 text-warning mb-2 d-block"></i>
                        <h6 class="fw-bold">Fitur Terkunci</h6>
                        <p class="small text-muted mb-3">Paket langganan Anda saat ini tidak mendukung penggunaan domain pribadi (.com, .id, dll). Silakan tingkatkan paket Anda untuk membuka fitur ini.</p>
                        <a href="{{ route('client.billing.index', $website->id) }}" class="btn btn-warning btn-sm fw-bold shadow-sm">
                            <i class="bi bi-star-fill me-1"></i> Upgrade Paket Sekarang
                        </a>
                    </div>
                @endif
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

                        <hr class="border-warning opacity-50">

                        <h6 class="fw-bold text-dark small mb-2">Cek Status Domain Anda:</h6>
                        <form action="{{ route('client.domains.check',  $website->id) }}" method="POST" class="d-flex align-items-center gap-2">
                            @csrf
                            <div class="input-group input-group-sm w-auto">
                                <span class="input-group-text bg-white"><i class="bi bi-link-45deg"></i></span>
                                <input type="text" name="domain_to_check" class="form-control" placeholder="contoh: tokosaya.com" required>
                                <button type="submit" class="btn btn-dark fw-bold">Cek Status DNS</button>
                            </div>
                        </form>
                        <div class="form-text small mt-1 text-dark opacity-75">*Pengecekan ini mungkin akan menampilkan status "Belum Mengarah" jika Anda baru saja menyimpan DNS (masih dalam masa propagasi).</div>
                    </div>
        </div>
    </div>

    @else
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <div class="small text-muted mb-1">Domain Terdaftar</div>
                    <h4 class="fw-bold m-0 text-primary">{{ $website->custom_domain }}</h4>
                </div>
                
                @if($website->domain_status == 'active')
                    <span class="badge bg-success px-3 py-2">Aktif</span>
                @else
                    <span class="badge bg-warning text-dark px-3 py-2">Menunggu Verifikasi</span>
                @endif
            </div>

            <div class="alert alert-info border-0 d-flex gap-3 align-items-start">
                <i class="bi bi-info-circle-fill fs-5 mt-1"></i>
                <div>
                    <strong>Instruksi Konfigurasi DNS</strong>
                    <p class="m-0 small mt-1">
                        Agar domain <code>{{ $website->custom_domain }}</code> mengarah ke toko Anda, silakan login ke penyedia domain Anda dan tambahkan <strong>CNAME Record</strong> berikut:
                    </p>
                </div>
            </div>

            <div class="bg-light p-3 rounded mb-4 border">
                <div class="row text-center text-muted small fw-bold text-uppercase mb-2">
                    <div class="col-3">Type</div>
                    <div class="col-4">Name / Host</div>
                    <div class="col-5">Value / Target</div>
                </div>
                <div class="row text-center align-items-center bg-white py-2 border rounded mx-0">
                    <div class="col-3 fw-bold">CNAME</div>
                    <div class="col-4">www</div>
                    <div class="col-5 text-break font-monospace small">pro.webcommerce.id</div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                <small class="text-muted">Salah memasukkan domain?</small>
                <form action="{{ route('client.domains.destroy', $website->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus domain ini? Website akan kembali ke subdomain lama.')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm">Hapus & Kembali ke Subdomain</button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection