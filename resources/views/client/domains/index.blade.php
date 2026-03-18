@extends('layouts.client')

@section('content')
<div class="container-fluid py-4">
    <h4 class="fw-bold mb-4">Pengaturan Domain</h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('client.domains.update', $website->id) }}" method="POST">
                        @csrf
                        {{-- Sesuai route web.php Anda, metode updatenya adalah POST --}}
                        
                        {{-- SUBDOMAIN SISTEM (Read Only) --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">Subdomain Bawaan (Gratis)</label>
                            <div class="input-group">
                                <input type="text" class="form-control bg-light" value="{{ $website->subdomain }}" readonly>
                                <span class="input-group-text bg-light">.{{ parse_url(config('app.url'), PHP_URL_HOST) }}</span>
                            </div>
                            <small class="text-muted mt-1 d-block">Ini adalah alamat default toko Anda yang selalu aktif.</small>
                        </div>

                        <hr class="my-4">

                        {{-- CUSTOM DOMAIN (Input) --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-primary">Gunakan Domain Sendiri (Custom Domain)</label>
                            <p class="text-muted small mb-3">
                                Ingin toko Anda terlihat lebih profesional dengan domain sendiri (misal: <strong>tokosaya.com</strong>)? Masukkan nama domain Anda di bawah ini.
                            </p>
                            
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-globe"></i></span>
                                <input type="text" class="form-control" name="custom_domain" id="custom_domain" value="{{ old('custom_domain', $website->custom_domain) }}" placeholder="contoh: joseelectronics.com">
                            </div>
                            @error('custom_domain')
                                <small class="text-danger mt-1 d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- INSTRUKSI DNS UNTUK KLIEN --}}
                        <div class="alert alert-info border-info bg-info bg-opacity-10 mb-4">
                            <h6 class="fw-bold"><i class="bi bi-info-circle-fill me-2"></i>Langkah Penting Setelah Menyimpan:</h6>
                            <ul class="mb-0 small">
                                <li>Masuk ke penyedia domain Anda (Niagahoster, Idwebhost, dll).</li>
                                <li>Buka pengaturan <strong>DNS Management</strong>.</li>
                                <li>Buat/Ubah <strong>A Record</strong> yang mengarah ke IP Server: <strong class="user-select-all text-primary">123.456.789.000</strong> <em>(Ganti dengan IP Enhance Anda)</em>.</li>
                                <li>Hubungi tim support kami agar domain Anda segera diaktifkan di sisi server.</li>
                            </ul>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-2"></i>Simpan Konfigurasi Domain</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection