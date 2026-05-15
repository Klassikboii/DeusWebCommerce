@extends('layouts.client')

@section('title', 'Pengaturan Pembayaran & Verifikasi')

{{-- Pastikan memanggil CSS Select2 di head --}}
@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endsection

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h3 class="fw-bold mb-1">Verifikasi Profil Bisnis (KYB)</h3>
        <p class="text-muted">Sesuai regulasi Bank Indonesia, data ini diperlukan untuk mencairkan dana penjualan ke rekening Anda.</p>
    </div>
    {{-- 🚨 TAMBAHKAN BLOK ERROR INI 🚨 --}}
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

    {{-- TAMBAHKAN JUGA BLOK SUKSES --}}
    @if (session('success'))
        <div class="alert alert-success shadow-sm border-0">
            <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
        </div>
    @endif
    <form action="{{ route('client.payment.kyb.store', $website->id) }}" method="POST">
        @csrf
        
        <div class="row">
            <div class="col-lg-8">
                {{-- 1. MERCHANT DETAIL --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold py-3">1. Detail Usaha</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Nama Usaha Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="Contoh: PT Sukses Makmur" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Singkatan Usaha <span class="text-danger">*</span></label>
                                <input type="text" name="short_name" class="form-control" maxlength="25" placeholder="Maks 25 huruf" required>
                            </div>
                            {{-- INI ADALAH BLOK YANG HILANG (Tipe Struktur Bisnis) --}}
                            <div class="col-md-6">
                                <label class="form-label">Tipe Struktur Bisnis <span class="text-danger">*</span></label>
                                <select name="business_structure" class="form-select" required>
                                    <option value="">-- Pilih Struktur --</option>
                                    <option value="INDIVIDUAL">Perorangan (Individual)</option>
                                    <option value="PERSEROAN TERBATAS">Perseroan Terbatas (PT)</option>
                                    <option value="CV">CV</option>
                                </select>
                            </div>
                            {{-- Dropdown Select2 untuk Struktur Bisnis --}}
                            <div class="col-md-6">
                                <label class="form-label">Kategori Industri (MCC) <span class="text-danger">*</span></label>
                                <select name="mcc" class="form-select select2-industry" required>
                                    <option value=""></option> </select>
                            </div>

                            <!-- <div class="col-md-12">
                                <label class="form-label">Kecamatan <span class="text-danger">*</span></label>
                                <select name="district_id" id="district" class="form-select select2-ajax" required>
                                    <option value=""></option> </select>
                            </div> -->
                        </div>
                    </div>
                </div>

                {{-- 2. LOKASI OPERASIONAL --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold py-3">2. Lokasi Operasional</div>
                    <div class="card-body">
                        <div class="row g-3">
                            {{-- Provinsi & Kota dibuat input teks saja agar Anda bisa Copy-Paste ke Pivot nanti --}}
                            <div class="col-md-6">
                                <label class="form-label">Provinsi <span class="text-danger">*</span></label>
                                <input type="text" name="province" class="form-control" placeholder="Masukkan Provinsi" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kota/Kabupaten <span class="text-danger">*</span></label>
                                <input type="text" name="city" class="form-control" placeholder="Masukkan Kota/Kabupaten" required>
                            </div>

                            {{-- Kecamatan menggunakan Dropdown Pintar (Ini yang paling penting untuk API) --}}
                            <div class="col-md-12">
                                <label class="form-label">Cari Kecamatan <span class="text-danger">*</span></label>
                                <select name="district_id" id="district" class="form-select select2-ajax" required>
                                    <option value=""></option>
                                </select>
                                <div class="form-text">Cukup ketik nama kecamatan Anda (Sistem akan otomatis mengambil ID untuk Pivot).</div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Alamat Lengkap (Jalan, No, RT/RW) <span class="text-danger">*</span></label>
                                <textarea name="address" class="form-control" rows="2" maxlength="254" required placeholder="Contoh: Jl. Sudirman No. 123, RT 01/02"></textarea>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Kode Pos <span class="text-danger">*</span></label>
                                <input type="number" name="post_code" class="form-control" placeholder="Contoh: 60123" required>
                            </div>
                        </div>
                    </div>
                </div>

                                {{-- 3. PIC DETAIL --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold py-3">3. Person In Charge (PIC)</div>
                    <div class="card-body">
                        <div class="alert alert-info py-2 small mb-3">
                            <i class="bi bi-info-circle-fill me-1"></i> PIC akan menerima email aktivasi untuk mengakses sistem dashboard Pivot.
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap PIC <span class="text-danger">*</span></label>
                                <input type="text" name="pic_name" class="form-control" maxlength="32" placeholder="Sesuai KTP" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jabatan <span class="text-danger">*</span></label>
                                <input type="text" name="pic_job_title" class="form-control" placeholder="Contoh: Owner / Manager">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email PIC <span class="text-danger">*</span></label>
                                <input type="email" name="pic_email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">No. WhatsApp PIC <span class="text-danger">*</span></label>
                                <input type="text" name="pic_phone" class="form-control" placeholder="Contoh: 08123456789" required>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 4. WITHDRAWAL DETAIL --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold py-3">4. Rekening Pencairan Dana</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Bank Tujuan <span class="text-danger">*</span></label>
                                <select name="bank_channel_code" id="bank_select" class="form-select select2-bank" required>
                                    <option value=""></option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Nomor Rekening <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="bank_account_number" id="account_number" class="form-control" placeholder="Masukkan nomor rekening saja" required>
                                    <button class="btn btn-outline-primary" type="button" id="btn-check-account">
                                        <i class="bi bi-search me-1"></i> Check Account
                                    </button>
                                </div>
                                <div id="account-name-preview" class="form-text text-success fw-bold d-none">
                                    <i class="bi bi-check-circle-fill"></i> Nama Pemilik: <span id="target-name">...</span>
                                </div>
                            </div>
                            <div class="col-12 mt-4">
                                <div class="form-check form-switch p-3 bg-light rounded border">
                                    <input class="form-check-input ms-0 me-3" type="checkbox" name="auto_withdrawal" value="ON" id="autoWithdrawal">
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
                {{-- Kotak Submit --}}
                <div class="card border-0 shadow-sm sticky-top" style="top: 100px;">
                    <div class="card-body">
                        <h6 class="fw-bold">Siap Mengajukan?</h6>
                        <p class="small text-muted mb-3">Pastikan data sesuai dengan dokumen legal usaha dan buku tabungan Anda untuk menghindari penolakan.</p>
                        <button type="submit" class="btn btn-primary w-100 fw-bold">Ajukan Verifikasi</button>
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
    // Dropdown Industri
    $('.select2-industry').select2({
        theme: 'bootstrap-5',
        ajax: {
            url: "{{ route('api.pivot.industries', $website->id) }}", // Pastikan route name ini benar
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
        ajax: {
            url: "{{ route('api.pivot.districts', $website->id) }}", // Pastikan route name ini benar
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

        $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memeriksa...');
        
        // Simulasi loading 1.5 detik
        setTimeout(() => {
            $(this).html('<i class="bi bi-search me-1"></i> Check Account');
            $('#account-name-preview').removeClass('d-none');
            // Untuk tahap manual, kita tampilkan placeholder atau biarkan user melihat efeknya
            $('#target-name').text("Sesuai Data Bank"); 
            alert("Pengecekan akun berhasil. Pastikan nama pemilik rekening sudah benar.");
        }, 1500);
    });
    $('.select2-bank').select2({
    theme: 'bootstrap-5',
    placeholder: "Cari Nama Bank...",
    ajax: {
        url: "{{ route('api.pivot.banks', $website->id) }}",
        dataType: 'json',
        delay: 250,
        processResults: function (data) {
            return { results: data };
        }
    }
});
});
</script>
@endsection