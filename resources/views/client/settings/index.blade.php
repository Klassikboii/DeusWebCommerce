@extends('layouts.client')
@php
    $accurateDatabases = [];
    $isAccurateConnected = $website->accurateIntegration && $website->accurateIntegration->access_token;
    $selectedDbId = $website->accurateIntegration->accurate_database_id ?? null;

    if ($isAccurateConnected && !$selectedDbId) {
        $accurateService = new \App\Services\AccurateService($website);
        $accurateDatabases = $accurateService->getDatabaseList();
    }
@endphp
@section('title', 'Pengaturan Toko')

@section('content')
<div class="container-fluid p-0" style="max-width: 800px;">
    
    <div class="mb-4">
        <h4 class="fw-bold mb-1">Pengaturan Toko</h4>
        <p class="text-muted m-0">Kelola identitas dan informasi kontak toko Anda.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('client.settings.update', $website->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 fw-bold">Identitas Umum</div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label">Nama Toko</label>
                    <input type="text" name="site_name" class="form-control" value="{{ old('site_name', $website->site_name) }}" required>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Subdomain</label>
                        <input type="text" class="form-control bg-light" value="{{ $website->subdomain }}.webcommerce.id" readonly>
                        <small class="text-muted">Subdomain tidak dapat diubah.</small>
                    </div>
                    {{-- Letakkan di dalam form pengaturan Anda --}}
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3">
                                <h6 class="mb-0 fw-bold">Status Operasional Toko</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="form-check form-switch fs-5">
                                    <input class="form-check-input cursor-pointer" type="checkbox" role="switch" 
                                        id="storeStatusSwitch" name="is_open" value="1" 
                                        {{ $website->is_open ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold ms-2" for="storeStatusSwitch">
                                        {{ $website->is_open ? 'Toko Buka (Menerima Pesanan)' : 'Toko Tutup Sementara' }}
                                    </label>
                                </div>
                                <p class="text-muted small mt-2 mb-0">
                                    Jika dimatikan, pengunjung masih bisa melihat produk tapi tidak bisa melakukan Checkout (Keranjang akan dinonaktifkan).
                                </p>
                            </div>
                        </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 fw-bold">Kontak & Alamat</div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nomor WhatsApp Toko</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">+62</span>
                            <input type="number" name="whatsapp_number" class="form-control" value="{{ old('whatsapp_number', $website->whatsapp_number) }}" placeholder="8123456789">
                        </div>
                        <small class="text-muted">Akan muncul di tombol 'Hubungi Kami'.</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email Resmi</label>
                        <input type="email" name="email_contact" class="form-control" value="{{ old('email_contact', $website->email_contact) }}" placeholder="toko@email.com">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Alamat Lengkap</label>
                    <textarea name="address" class="form-control" rows="3" placeholder="Jl. Contoh No. 123, Kota...">{{ old('address', $website->address) }}</textarea>
                    <small class="text-muted">Akan ditampilkan di bagian Footer website.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Kota Asal Pengiriman (Lokasi Toko)</label>
                    <select name="city_id" class="form-select select2" required>
                        <option value="">-- Pilih Kota Asal Toko --</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" {{ $website->city_id == $city->id ? 'selected' : '' }}>
                                {{ $city->type }} {{ $city->name }} - Prov. {{ $city->province->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Kota ini akan digunakan sebagai titik awal perhitungan ongkos kirim.</small>
                </div>

                {{-- ... Di dalam Form Settings, setelah input Address ... --}}

                    <hr class="my-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-credit-card me-2"></i>Informasi Rekening Bank</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nama Bank</label>
                            <input type="text" name="bank_name" class="form-control" 
                                placeholder="Contoh: BCA / Mandiri" 
                                value="{{ old('bank_name', $website->bank_name) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nomor Rekening</label>
                            <input type="number" name="bank_account_number" class="form-control" 
                                placeholder="Contoh: 1234567890" 
                                value="{{ old('bank_account_number', $website->bank_account_number) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Atas Nama (Pemilik Rekening)</label>
                            <input type="text" name="bank_account_holder" class="form-control" 
                                placeholder="Contoh: John Doe" 
                                value="{{ old('bank_account_holder', $website->bank_account_holder) }}">
                        </div>
                    </div>
                    {{-- ... Di dalam Form Settings, di bawah blok Informasi Rekening Bank ... --}}

        

        {{-- ... Lanjut ke tombol Save Pengaturan yang sudah ada ... --}}

{{-- ... Lanjut ke tombol Save ... --}}
            </div>
        </div>

        <div class="d-flex justify-content-end mb-5">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-save me-1"></i> Simpan Pengaturan
            </button>
        </div>
    </form>

    <hr class="my-5">
        
        <div class="card border-0 shadow-sm mb-4" style="background-color: #f8fbff; border-left: 4px solid #0052cc !important;">
            <div class="card-header py-3 bg-transparent border-0">
                <h5 class="fw-bold mb-0" style="color: #0052cc;">
                    <i class="bi bi-box-seam me-2"></i>Integrasi Accurate Online
                </h5>
            </div>
            <div class="card-body p-4 pt-0">
                <p class="text-muted mb-3">
                    Hubungkan toko Anda dengan Accurate Online untuk mempermudah sinkronisasi produk (Barang & Jasa). Saat Anda mengunggah produk baru di toko ini, data akan otomatis terkirim ke sistem pembukuan Accurate Anda.
                </p>

                @php
                    // Cek apakah website ini sudah terhubung (punya token)
                    $isAccurateConnected = $website->accurateIntegration && $website->accurateIntegration->access_token;
                @endphp

                @if($isAccurateConnected)
                    
                    @if(!$selectedDbId)
                        {{-- JIKA SUDAH LOGIN TAPI BELUM PILIH DATABASE --}}
                        <div class="alert alert-warning mb-0">
                            <h6 class="fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Satu Langkah Lagi!</h6>
                            <p class="small mb-2">Pilih database Accurate mana yang akan dihubungkan dengan toko ini:</p>
                            
                            <form action="{{ route('client.accurate.save_db', $website->id) }}" method="POST" class="d-flex gap-2">
                                @csrf
                                <select name="accurate_database_id" class="form-select form-select-sm" required>
                                    <option value="">-- Pilih Database Accurate --</option>
                                    @foreach($accurateDatabases as $db)
                                        <option value="{{ $db['id'] }}">{{ $db['alias'] }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary text-nowrap">Simpan Database</button>
                            </form>
                        </div>
                    @else
                        {{-- JIKA DATABASE SUDAH DIPILIH --}}
                        <div class="alert alert-success d-flex align-items-center mb-0">
                            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                            <div>
                                <strong>Status: Terhubung Aktif</strong><br>
                                <span class="small">Toko Anda terhubung dengan Database ID: {{ $selectedDbId }}</span>
                            </div>
                            <a href="{{ route('client.accurate.redirect', $website->id) }}" class="btn btn-sm btn-outline-success ms-auto">
                                <i class="bi bi-arrow-repeat me-1"></i> Ganti Akun
                            </a>
                        </div>
                    @endif

                @else
                    <a href="{{ route('client.accurate.redirect', $website->id) }}" class="btn btn-primary px-4">
                        <i class="bi bi-link-45deg me-1"></i> Hubungkan ke Accurate Sekarang
                    </a>
                @endif
            </div>
        </div>
</div>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // Aktifkan Select2
        $('.select2').select2({
            placeholder: "-- Ketik / Pilih Lokasi --",
            width: '100%' // Agar lebarnya rapi mengikuti form
        });

        // Paksa fungsi ongkir berjalan otomatis ketika kota dipilih lewat Select2
        $('#destination_city').on('select2:select', function (e) {
            getShippingRates(); 
        });
    });
</script>
@endsection