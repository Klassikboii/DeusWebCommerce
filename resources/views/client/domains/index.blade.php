@extends('layouts.client')

@section('title', 'Pengaturan Domain')

@section('content')
<div class="container-fluid p-0" style="max-width: 800px;">
    <div class="mb-4">
        <h4 class="fw-bold mb-1">Custom Domain</h4>
        <p class="text-muted m-0">Gunakan nama domain Anda sendiri (misal: tokosaya.com).</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

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
                    <a href="{{ route('store.home', $website->subdomain) }}" target="_blank" class="fw-bold text-decoration-none">
                        {{ $website->subdomain }}.webcommerce.id
                    </a>
                </p>
            </div>

            <form action="{{ route('client.domains.update', $website->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-bold small">Masukkan Nama Domain</label>
                    
                    <input type="text" 
                        name="custom_domain" 
                        class="form-control @error('custom_domain') is-invalid @enderror" 
                        placeholder="Contoh: www.tokoelektronik.com" 
                        value="{{ $website->custom_domain }}"" 
                        required>
                    
                    @error('custom_domain')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="form-text">Pastikan Anda sudah membeli domain ini di penyedia domain.</div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Simpan Domain</button>
            </form>
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