@extends('layouts.app') {{-- Sesuaikan dengan layout dashboard klien Anda --}}

@section('title', 'Pengaturan Pembayaran & Verifikasi')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold mb-1">Verifikasi Profil Bisnis (KYB)</h3>
            <p class="text-muted">Sesuai regulasi, data ini diperlukan untuk mencairkan dana ke rekening Anda.</p>
        </div>
        <a href="{{ route('client.websites') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- ALERT MESSAGES --}}
    @if ($errors->any())
        <div class="alert alert-danger shadow-sm border-0">
            <strong>Gagal Menyimpan!</strong> Silakan perbaiki kesalahan berikut:
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success shadow-sm border-0">
            <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
        </div>
    @endif

    {{-- LOGIKA STATUS KYB --}}
    @php
        // Ambil data KYB jika sudah pernah diisi
        $kyb = auth()->user()->kybDetail ?? null;
        
        $isPending = ($kyb->status ?? '') === 'pending';
        $isApproved = ($kyb->status ?? '') === 'approved';
        $isRejected = ($kyb->status ?? '') === 'rejected';
    @endphp

    @if($isPending)
        <div class="alert alert-warning border-warning shadow-sm mb-4">
            <h6 class="fw-bold mb-1"><i class="bi bi-hourglass-split"></i> Verifikasi Sedang Diproses</h6>
            <p class="mb-0 small">Data Anda sedang ditinjau oleh tim kami. Anda tidak dapat mengubah informasi ini selama proses verifikasi berlangsung.</p>
        </div>
    @elseif($isApproved)
        <div class="alert alert-success border-success shadow-sm mb-4">
            <h6 class="fw-bold mb-1"><i class="bi bi-shield-check"></i> Bisnis Terverifikasi</h6>
            <p class="mb-0 small">Pembayaran otomatis Pivot sudah aktif. Perubahan data rekening mungkin memerlukan verifikasi ulang.</p>
        </div>
    @elseif($isRejected)
        <div class="alert alert-danger border-danger shadow-sm mb-4">
            <h6 class="fw-bold mb-1"><i class="bi bi-x-circle"></i> Verifikasi Ditolak</h6>
            <p class="mb-0 small">Mohon periksa kembali data legalitas dan rekening Anda sesuai dengan dokumen resmi, lalu ajukan ulang.</p>
        </div>
    @endif

    <form action="{{ route('client.kyb.store') }}" method="POST">
        @csrf
        
        <div class="row">
            <div class="col-lg-8">
                {{-- 1. DETAIL USAHA --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold py-3"><i class="bi bi-shop me-2 text-primary"></i>1. Detail Usaha</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label small fw-bold text-muted">Nama Usaha Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="Sesuai Legal" value="{{ old('name', $kyb->name ?? '') }}" {{ $isPending ? 'readonly' : 'required' }}>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Singkatan Usaha <span class="text-danger">*</span></label>
                                <input type="text" name="short_name" class="form-control" maxlength="25" placeholder="Maks 25 huruf" value="{{ old('short_name', $kyb->short_name ?? '') }}" {{ $isPending ? 'readonly' : 'required' }}>
                            </div>
                            
                            {{-- DROPDOWN PILIHAN WEBSITE UTAMA --}}
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-muted">Pilih Website/Toko Utama Sebagai Referensi <span class="text-danger">*</span></label>
                                <select name="website_id_reference" class="form-select" {{ $isPending ? 'disabled' : 'required' }}>
                                    <option value="">-- Pilih Toko Utama Anda --</option>
                                    @if(isset($websites) && $websites->count() > 0)
                                        @foreach($websites as $web)
                                            <option value="{{ $web->id }}" {{ (old('website_id_reference', $kyb->website_id_reference ?? '') == $web->id) ? 'selected' : '' }}>
                                                {{ $web->custom_domain ?? $web->subdomain . '.ashop.asia' }} ({{ $web->site_name }})
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @isset($kyb->website)
                                    <label class="form-label small fw-bold text-muted">
                                        Toko Anda Sekarang:
                                        <span class="text-primary">({{ $kyb->website }})</span>
                                    </label>
                                @endisset
                                @if(isset($websites) && $websites->count() == 0)
                                    <div class="text-danger mt-2 small fw-bold">
                                        <i class="bi bi-exclamation-triangle-fill"></i> Anda belum memiliki toko. Silakan buat toko terlebih dahulu.
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Tipe Struktur Bisnis <span class="text-danger">*</span></label>
                                <select name="business_structure" class="form-select" {{ $isPending ? 'disabled' : 'required' }}>
                                    <option value="">-- Pilih Struktur --</option>
                                    <option value="PERSEROAN TERBATAS" {{ (old('business_structure', $kyb->business_structure ?? '') == 'PERSEROAN TERBATAS') ? 'selected' : '' }}>Perseroan Terbatas (PT)</option>
                                    <option value="CV" {{ (old('business_structure', $kyb->business_structure ?? '') == 'CV') ? 'selected' : '' }}>CV</option>
                                    <option value="PERUSAHAAN NEGARA" {{ (old('business_structure', $kyb->business_structure ?? '') == 'PERUSAHAAN NEGARA') ? 'selected' : '' }}>Perusahaan Negara</option>
                                    <option value="PERUSAHAAN DAERAH" {{ (old('business_structure', $kyb->business_structure ?? '') == 'PERUSAHAAN DAERAH') ? 'selected' : '' }}>Perusahaan Daerah</option>
                                    <option value="YAYASAN" {{ (old('business_structure', $kyb->business_structure ?? '') == 'YAYASAN') ? 'selected' : '' }}>Yayasan</option>
                                    <option value="KOPERASI" {{ (old('business_structure', $kyb->business_structure ?? '') == 'KOPERASI') ? 'selected' : '' }}>Koperasi</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Kategori Industri (MCC) <span class="text-danger">*</span></label>
                                <select name="mcc" class="form-select select2-industry" {{ $isPending ? 'disabled' : 'required' }}>
                                    @if($kyb && $kyb->mcc)
                                        {{-- 🚨 PERBAIKAN DI SINI: Panggil Accessor mcc_name --}}
                                        <option value="{{ $kyb->mcc }}" selected>{{ $kyb->mcc_name }}</option>
                                    @else
                                        <option value=""></option>
                                    @endif
                                </select>
                            </div>
                            {{-- TIGA INPUT BARU SESUAI REQUEST --}}
                            <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Negara Terdaftar <span class="text-danger">*</span></label>
                                    <select name="country_of_entity" class="form-select select2-country" {{ $isPending ? 'disabled' : 'required' }}>
                                        @if($kyb && $kyb->country_of_entity)
                                            <option value="{{ $kyb->country_of_entity }}" selected>{{ $kyb->country_name }}</option>
                                        @else
                                            {{-- Default ke Indonesia jika baru pertama kali buka --}}
                                            <option value="ID" selected>Indonesia (ID)</option>
                                        @endif
                                    </select>
                                </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Merchant Type <span class="text-danger">*</span></label>
                                <select name="business_type" class="form-select" {{ $isPending ? 'disabled' : 'required' }}>
                                    <option value="">-- Pilih Tipe --</option>
                                    <option value="INDIVIDUAL" {{ (old('business_type', $kyb->business_type ?? '') == 'INDIVIDUAL') ? 'selected' : '' }}>Perorangan</option>
                                    <option value="COMPANY" {{ (old('business_type', $kyb->business_type ?? '') == 'COMPANY') ? 'selected' : '' }}>Firma</option>
                                
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Tipe Industri <span class="text-danger">*</span></label>
                                <select name="digital_status" class="form-select" {{ $isPending ? 'disabled' : 'required' }}>
                                    <option value="Digital" {{ (old('digital_status', $kyb->digital_status ?? '') == 'Digital') ? 'selected' : '' }}>Digital</option>
                                    <option value="Non-Digital" {{ (old('digital_status', $kyb->digital_status ?? '') == 'Non-Digital') ? 'selected' : '' }}>Non-Digital</option>
                                </select>
                            </div>
                            {{-- AKHIR TIGA INPUT BARU --}}

                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-muted">Deskripsi Singkat Usaha <span class="text-danger">*</span></label>
                                <input type="text" name="description" class="form-control" placeholder="Contoh: Toko retail yang menjual komponen elektronik" value="{{ old('description', $kyb->description ?? '') }}" {{ $isPending ? 'readonly' : 'required' }}>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. LOKASI OPERASIONAL --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold py-3"><i class="bi bi-geo-alt me-2 text-primary"></i>2. Lokasi Operasional</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Provinsi <span class="text-danger">*</span></label>
                                <input type="text" name="province" class="form-control" placeholder="Masukkan Provinsi" value="{{ old('province', $kyb->province ?? '') }}" {{ $isPending ? 'readonly' : 'required' }}>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Kota/Kabupaten <span class="text-danger">*</span></label>
                                <input type="text" name="city" class="form-control" placeholder="Masukkan Kota/Kabupaten" value="{{ old('city', $kyb->city ?? '') }}" {{ $isPending ? 'readonly' : 'required' }}>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-muted">Cari Kecamatan <span class="text-danger">*</span></label>
                                <select name="district_id" id="district" class="form-select select2-ajax" {{ $isPending ? 'disabled' : 'required' }}>
                                    @if($kyb && $kyb->district_id)
                                        {{-- 🚨 PERBAIKAN DI SINI: Panggil Accessor district_name --}}
                                        <option value="{{ $kyb->district_id }}" selected>{{ $kyb->district_name }}</option>
                                    @else
                                        <option value=""></option>
                                    @endif
                                </select>
                                <div class="form-text">Ketik nama kecamatan (Otomatis mencari ID untuk Pivot).</div>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Alamat Lengkap (Jalan, No, RT/RW) <span class="text-danger">*</span></label>
                                <textarea name="address" class="form-control" rows="2" maxlength="254" placeholder="Contoh: Jl. Sudirman No. 123" {{ $isPending ? 'readonly' : 'required' }}>{{ old('address', $kyb->address ?? '') }}</textarea>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Kode Pos <span class="text-danger">*</span></label>
                                <input type="number" name="post_code" class="form-control" placeholder="Contoh: 60123" value="{{ old('post_code', $kyb->post_code ?? '') }}" {{ $isPending ? 'readonly' : 'required' }}>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. PIC DETAIL --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold py-3"><i class="bi bi-person-badge me-2 text-primary"></i>3. Person In Charge (PIC)</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Nama Lengkap PIC <span class="text-danger">*</span></label>
                                <input type="text" name="pic_name" class="form-control" maxlength="32" placeholder="Sesuai KTP" value="{{ old('pic_name', $kyb->pic_name ?? '') }}" {{ $isPending ? 'readonly' : 'required' }}>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Jabatan <span class="text-danger">*</span></label>
                                <input type="text" name="pic_job_title" class="form-control" placeholder="Contoh: Owner / Manager" value="{{ old('pic_job_title', $kyb->pic_job_title ?? '') }}" {{ $isPending ? 'readonly' : 'required' }}>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Email PIC <span class="text-danger">*</span></label>
                                <input type="email" name="pic_email" class="form-control" value="{{ old('pic_email', $kyb->pic_email ?? '') }}" {{ $isPending ? 'readonly' : 'required' }}>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">No. WhatsApp PIC <span class="text-danger">*</span></label>
                                <input type="text" name="pic_phone" class="form-control" placeholder="08123456789" value="{{ old('pic_phone', $kyb->pic_phone ?? '') }}" {{ $isPending ? 'readonly' : 'required' }}>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 4. WITHDRAWAL DETAIL --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold py-3"><i class="bi bi-bank me-2 text-primary"></i>4. Rekening Pencairan Dana</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-muted">Bank Tujuan <span class="text-danger">*</span></label>
                                <select name="bank_channel_code" id="bank_select" class="form-select select2-bank" {{ $isPending ? 'disabled' : 'required' }}>
                                    @if($kyb && $kyb->bank_channel_code)
                                        <option value="{{ $kyb->bank_channel_code }}" selected>{{ $kyb->bank_channel_code }}</option>
                                    @else
                                        <option value=""></option>
                                    @endif
                                </select>
                            </div>
                            
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-muted">Nomor Rekening <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="bank_account_number" id="account_number" class="form-control" placeholder="Masukkan nomor rekening" value="{{ old('bank_account_number', $kyb->bank_account_number ?? '') }}" {{ $isPending ? 'readonly' : 'required' }}>
                                    <button class="btn btn-outline-primary" type="button" id="btn-check-account" {{ $isPending ? 'disabled' : '' }}>
                                        <i class="bi bi-search me-1"></i> Check Account
                                    </button>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-muted">Nama Pemilik Rekening (Beneficiary Name) <span class="text-danger">*</span></label>
                                <input type="text" name="bank_account_name" id="account_name" class="form-control" placeholder="Sesuai buku tabungan" value="{{ old('bank_account_name', $kyb->bank_account_name ?? '') }}" {{ $isPending ? 'readonly' : 'required' }}>
                                <div id="account-name-preview" class="form-text text-success fw-bold d-none">
                                    <i class="bi bi-check-circle-fill"></i> Hasil Cek Sistem: <span id="target-name">...</span>
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <div class="form-check form-switch p-3 bg-light rounded border">
                                    {{-- Hidden input agar nilai auto_withdrawal tetap terkirim OFF jika switch tidak dicentang --}}
                                    {{-- Hidden input mengirimkan state terakhir --}}
                                        <input type="hidden" name="auto_withdrawal" value="OFF">

                                        @php
                                            $isAutoOn = old('auto_withdrawal', $kyb->auto_withdrawal ?? 'OFF') === 'ON';
                                        @endphp

                                        {{-- 
                                        Jika disabled, ubah name menjadi atribut kosong (misal: data-name) agar browser tidak mengirimkannya,
                                        sehingga backend hanya akan membaca nilai dari hidden input di atas!
                                        --}}
                                        <input class="form-check-input ms-0 me-3" type="checkbox" 
                                            {{ $isPending ? 'data-name' : 'name' }}="auto_withdrawal" 
                                            value="ON" 
                                            id="autoWithdrawal" 
                                            {{ $isAutoOn ? 'checked' : '' }} 
                                            {{ $isPending ? 'disabled' : '' }}>
                                    <label class="form-check-label fw-bold" for="autoWithdrawal">
                                        Auto-Withdrawal (Pencairan Otomatis)
                                    </label>
                                    <p class="text-muted small mb-0 ms-5">Dana akan otomatis ditransfer ke rekening Anda setiap hari jika saldo mencukupi.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                {{-- KOTAK SUBMIT --}}
                <div class="card border-0 shadow-sm sticky-top" style="top: 100px;">
                    <div class="card-body">
                        <h6 class="fw-bold">Ringkasan Verifikasi</h6>
                        <p class="small text-muted mb-3">Pastikan data sesuai dengan dokumen legal usaha dan buku tabungan Anda untuk menghindari penolakan.</p>
                        
                        @if(!$isPending)
                            <button type="submit" class="btn btn-primary w-100 fw-bold">
                                <i class="bi bi-send-check me-1"></i> {{ $kyb ? 'Simpan & Ajukan Ulang' : 'Ajukan Verifikasi' }}
                            </button>
                        @else
                            <button type="button" class="btn btn-secondary w-100 fw-bold" disabled>
                                Sedang Diproses
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
   $(document).ready(function() {
       // Cek apakah form dalam mode disabled/pending
       let isFormDisabled = {{ $isPending ? 'true' : 'false' }};

       // Dropdown Industri (MCC)
       $('.select2-industry').select2({
           theme: 'bootstrap-5',
           placeholder: "Cari Kategori Industri...",
           disabled: isFormDisabled,
           ajax: {
               url: "{{ route('api.pivot.industries') }}", 
               dataType: 'json',
               delay: 250,
               processResults: function (data) {
                   return { results: data };
               }
           }
       });

       // Dropdown Kecamatan
       $('#district').select2({
           theme: 'bootstrap-5',
           placeholder: "Cari Kecamatan...",
           disabled: isFormDisabled,
           ajax: {
               url: "{{ route('api.pivot.districts') }}", 
               dataType: 'json',
               delay: 250,
               processResults: function (data) {
                   return { results: data };
               }
           }
       });

       // Dropdown Bank
       $('.select2-bank').select2({
           theme: 'bootstrap-5',
           placeholder: "Cari Nama Bank...",
           disabled: isFormDisabled,
           ajax: {
               url: "{{ route('api.pivot.banks') }}",
               dataType: 'json',
               delay: 250,
               processResults: function (data) {
                   return { results: data };
               }
           }
       });
       // Dropdown Negara ISO
       $('.select2-country').select2({
           theme: 'bootstrap-5',
           placeholder: "Cari Negara (Ketik Nama / Kode ISO)...",
           disabled: isFormDisabled,
           ajax: {
               url: "{{ route('api.pivot.countries') }}", 
               dataType: 'json',
               delay: 250,
               processResults: function (data) {
                   return { results: data };
               }
           }
       });

       // Simulasi Cek Rekening
       $('#btn-check-account').click(function() {
           let accNumber = $('#account_number').val();
           let bank = $('#bank_select').val();

           if(!accNumber || !bank) {
               alert("Silakan pilih Bank dan masukkan Nomor Rekening terlebih dahulu.");
               return;
           }

           let btn = $(this);
           btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memeriksa...');
           
           // Simulasi delay jaringan API 1.5 detik
           setTimeout(() => {
               btn.html('<i class="bi bi-search me-1"></i> Check Account');
               $('#account-name-preview').removeClass('d-none');
               
               // Dalam implementasi nyata, ini diganti dengan nama kembalian dari API Cek Rekening
               let dummyName = "JUNAIDI SUTANTO"; 
               $('#target-name').text(dummyName); 
               
               // Otomatis isi ke input text
               $('#account_name').val(dummyName);
           }, 1500);
       });
   });
</script>
@endsection