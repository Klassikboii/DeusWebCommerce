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
        <p class="text-muted m-0">Kelola identitas, integrasi, dan informasi kontak toko Anda.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm border-0">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('client.settings.update', $website->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 fw-bold text-primary">
                <i class="bi bi-shop me-2"></i>Identitas Umum
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Toko</label>
                    <input type="text" name="site_name" class="form-control" value="{{ old('site_name', $website->site_name) }}" required>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Subdomain</label>
                        <input type="text" class="form-control bg-light" value="{{ $website->subdomain }}.webcommerce.id" readonly>
                        <small class="text-muted">Subdomain tidak dapat diubah.</small>
                    </div>
                </div>

                <hr class="my-4">
                
                <h6 class="fw-bold mb-3">Status Operasional Toko</h6>
                <div class="form-check form-switch fs-5 mb-1">
                    <input class="form-check-input cursor-pointer" type="checkbox" role="switch" 
                        id="storeStatusSwitch" name="is_open" value="1" 
                        {{ $website->is_open ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold ms-2 text-{{ $website->is_open ? 'success' : 'danger' }}" for="storeStatusSwitch">
                        {{ $website->is_open ? 'Toko Buka (Menerima Pesanan)' : 'Toko Tutup Sementara' }}
                    </label>
                </div>
                <p class="text-muted small mb-0">
                    Jika dimatikan, pengunjung masih bisa melihat produk tapi tidak bisa melakukan Checkout.
                </p>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 fw-bold text-primary">
                <i class="bi bi-geo-alt me-2"></i>Kontak & Alamat
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nomor WhatsApp Toko</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">+62</span>
                            <input type="number" name="whatsapp_number" class="form-control" value="{{ old('whatsapp_number', $website->whatsapp_number) }}" placeholder="8123456789">
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Email Resmi</label>
                        <input type="email" name="email_contact" class="form-control" value="{{ old('email_contact', $website->email_contact) }}" placeholder="toko@email.com">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Alamat Lengkap</label>
                    <textarea name="address" class="form-control" rows="3" placeholder="Jl. Contoh No. 123, Kota...">{{ old('address', $website->address) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Kota Asal Pengiriman</label>
                    <select name="city_id" class="form-select select2" required>
                        <option value="">-- Pilih Kota Asal Toko --</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" {{ $website->city_id == $city->id ? 'selected' : '' }}>
                                {{ $city->type }} {{ $city->name }} - Prov. {{ $city->province->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <hr class="my-4">
                
                <h6 class="fw-bold mb-3"><i class="bi bi-bank me-2"></i>Rekening Manual (Opsional)</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nama Bank</label>
                        <input type="text" name="bank_name" class="form-control" placeholder="BCA / Mandiri" value="{{ old('bank_name', $website->bank_name) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nomor Rekening</label>
                        <input type="number" name="bank_account_number" class="form-control" placeholder="1234567890" value="{{ old('bank_account_number', $website->bank_account_number) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Atas Nama</label>
                        <input type="text" name="bank_account_holder" class="form-control" placeholder="John Doe" value="{{ old('bank_account_holder', $website->bank_account_holder) }}">
                    </div>
                </div>
            </div>
            
            <div class="card-footer bg-light p-3 text-end">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-save me-1"></i> Simpan Profil Toko
                </button>
            </div>
        </div>
    </form>

    <hr class="my-5 text-muted">

    <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #0052cc !important;">
        <div class="card-header bg-white py-3 fw-bold" style="color: #0052cc;">
            <i class="bi bi-box-seam me-2"></i>Integrasi Accurate Online
            <button type="button" class="btn btn-sm btn-light border text-muted" data-bs-toggle="modal" data-bs-target="#modalpanduanAccurate" title="Cara Setup">
            <i class="bi bi-question-circle"></i>
        </button>
        </div>
        <div class="card-body p-4">
            <p class="text-muted mb-4">Hubungkan toko Anda dengan Accurate Online untuk mempermudah sinkronisasi produk (Barang & Jasa).</p>

            @if($isAccurateConnected)
                @if(!$selectedDbId)
                    <div class="alert border-warning bg-warning bg-opacity-10 mb-0">
                        <h6 class="fw-bold text-warning-emphasis"><i class="bi bi-exclamation-triangle-fill me-2"></i>Satu Langkah Lagi!</h6>
                        <p class="small mb-3">Pilih database Accurate mana yang akan dihubungkan:</p>
                        
                        <form action="{{ route('client.accurate.save_db', $website->id) }}" method="POST" class="d-flex gap-2">
                            @csrf
                            <select name="accurate_database_id" class="form-select form-select-sm w-50" required>
                                <option value="">-- Pilih Database Accurate --</option>
                                @foreach($accurateDatabases as $db)
                                    <option value="{{ $db['id'] }}">{{ $db['alias'] }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">Simpan Database</button>
                        </form>
                    </div>
                @else
                    <div class="alert border-success bg-success bg-opacity-10 d-flex align-items-center mb-0">
                        <i class="bi bi-check-circle-fill fs-4 text-success me-3"></i>
                        <div>
                            <strong class="text-success-emphasis">Status: Terhubung Aktif</strong><br>
                            <span class="small text-muted">Toko Anda terhubung dengan Database ID: {{ $selectedDbId }}</span>
                        </div>
                        <a href="{{ route('client.accurate.redirect', $website->id) }}" class="btn btn-sm btn-outline-success ms-auto">
                            <i class="bi bi-arrow-repeat me-1"></i> Ganti Akun
                        </a>
                    </div>
                @endif
            @else
                <a href="{{ route('client.accurate.redirect', $website->id) }}" class="btn btn-primary px-4">
                    <i class="bi bi-link-45deg me-1"></i> Hubungkan ke Accurate
                </a>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-5" style="border-left: 4px solid #17a2b8 !important;">
        <div class="card-header bg-white py-3 fw-bold text-info-emphasis">
            <i class="bi bi-credit-card me-2"></i>Payment Gateway (Midtrans)
            <button type="button" class="btn btn-sm btn-light border text-muted" data-bs-toggle="modal" data-bs-target="#modalPanduanMidtrans" title="Cara Setup">
            <i class="bi bi-question-circle"></i>
        </button>
        </div>
        <div class="card-body p-4">
            <div class="alert bg-light border text-muted small mb-4">
                <i class="bi bi-info-circle me-1"></i> Masukkan API Key dari akun Midtrans Anda agar pembayaran dari pelanggan langsung masuk ke saldo Anda.
            </div>

            <form action="{{ route('client.settings.payment.update', $website->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="form-label fw-bold">Environment (Lingkungan)</label>
                    <div class="form-check form-switch fs-5">
                        <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="midtrans_is_production" name="midtrans_is_production" value="1" {{ $website->midtrans_is_production ? 'checked' : '' }}>
                        <label class="form-check-label ms-2 fs-6 mt-1" for="midtrans_is_production">
                            Gunakan <strong>Production</strong> (Live / Uang Asli)
                        </label>
                    </div>
                    <div class="form-text mt-2">Biarkan mati (off) jika Anda masih dalam tahap testing (Sandbox).</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Client Key</label>
                    <input type="text" name="midtrans_client_key" class="form-control" value="{{ old('midtrans_client_key', $website->midtrans_client_key) }}" placeholder="Contoh: SB-Mid-client-xxxxxx">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Server Key</label>
                    <div class="input-group">
                        <input type="password" name="midtrans_server_key" id="server_key_input" class="form-control" value="{{ old('midtrans_server_key', $website->midtrans_server_key) }}" placeholder="Contoh: SB-Mid-server-xxxxxx">
                        <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('server_key_input').type = document.getElementById('server_key_input').type === 'password' ? 'text' : 'password'">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="form-text text-danger"><i class="bi bi-shield-lock"></i> Jaga kerahasiaan Server Key Anda!</div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-info text-white px-4">
                        <i class="bi bi-key me-1"></i> Simpan Kunci Midtrans
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "-- Ketik / Pilih Lokasi --",
            width: '100%' 
        });

        $('#destination_city').on('select2:select', function (e) {
            getShippingRates(); 
        });
    });
</script>
<div class="modal fade" id="modalPanduanMidtrans" tabindex="-1" aria-labelledby="modalMidtransLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title fw-bold" id="modalMidtransLabel"><i class="bi bi-credit-card me-2"></i>Panduan Setup Midtrans</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <ol class="mb-0 text-muted" style="line-height: 1.8;">
                    <li>Daftar atau Login ke akun <a href="https://dashboard.midtrans.com/" target="_blank" class="fw-bold text-primary text-decoration-none">Dashboard Midtrans</a> Anda.</li>
                    <li>Di menu sebelah kiri, masuk ke bagian <strong>Settings</strong> (Pengaturan) ➔ <strong>Access Keys</strong>.</li>
                    <li>Anda akan melihat <strong>Client Key</strong> dan <strong>Server Key</strong>. Salin (Copy) kedua kunci tersebut.</li>
                    <li>Kembali ke Dashboard ini, klik tombol "Hubungkan" dan <em>Paste</em> kunci tersebut ke form yang tersedia.</li>
                    <li>Uang dari pembeli akan langsung masuk ke akun Midtrans Anda secara otomatis!</li>
                </ol>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mengerti</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalPanduanAccurate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h6 class="modal-title fw-bold"><i class="bi bi-box-seam me-2"></i>Panduan Setup Accurate Online</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-warning small mb-3">
                    <strong>⚠️ PENTING SEBELUM MENGHUBUNGKAN:</strong><br>
                    Pastikan Anda sudah membuat 2 item dengan tipe <strong>"JASA" (Service)</strong> di Accurate Online Anda dengan Nomor/SKU berikut:<br>
                    1. SKU: <strong>ONGKIR</strong> (Untuk mencatat biaya pengiriman)<br>
                    2. SKU: <strong>DISKON</strong> (Jika Anda berencana menggunakan fitur kupon/promo)
                </div>
                <ol class="mb-0 text-muted small" style="line-height: 1.8;">
                    <li>Klik tombol <strong>"Hubungkan"</strong> di sebelah kotak ini.</li>
                    <li>Anda akan diarahkan ke halaman Login resmi Accurate Online.</li>
                    <li>Berikan izin pada aplikasi Webcommerce ini untuk mengakses data Anda.</li>
                    <li>Setelah kembali, <strong>Pilih Database Accurate</strong> yang ingin dihubungkan.</li>
                    <li>Selesai! Anda kini bisa menarik produk langsung dari Accurate.</li>
                </ol>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mengerti</button>
            </div>
        </div>
    </div>
</div>
@endsection