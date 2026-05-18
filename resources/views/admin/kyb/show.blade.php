@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold">Detail Verifikasi: {{ $kyb->name }}</h4>
        <span class="badge {{ $kyb->status == 'pending' ? 'bg-warning' : 'bg-success' }}">
            Status: {{ strtoupper($kyb->status) }}
        </span>
    </div>

    <div class="row">
       <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Data Lengkap Merchant (Untuk Form Pivot)</div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <tbody>
                            <tr><td colspan="2" class="bg-light fw-bold text-center py-2">Merchant Detail</td></tr>
                            <tr>
                                <td width="35%" class="ps-3">Nama Merchant</td>
                                <td>{{ $kyb->name }} <button class="btn btn-sm btn-light py-0 px-1 border" onclick="copy('{{ $kyb->name }}')"><i class="bi bi-clipboard"></i></button></td>
                            </tr>
                            <tr>
                                <td class="ps-3">Short Name</td>
                                <td>{{ $kyb->short_name }} <button class="btn btn-sm btn-light py-0 px-1 border" onclick="copy('{{ $kyb->short_name }}')"><i class="bi bi-clipboard"></i></button></td>
                            </tr>
                            {{-- 🚨 TAMPILKAN DESKRIPSI USAHA --}}
                            <tr>
                                <td class="ps-3">Deskripsi Usaha</td>
                                <td>{{ $kyb->description }} <button class="btn btn-sm btn-light py-0 px-1 border" onclick="copy('{{ $kyb->description }}')"><i class="bi bi-clipboard"></i></button></td>
                            </tr>
                            <tr>
                                <td class="ps-3">Tipe & Struktur Bisnis</td>
                                <td>{{ $kyb->business_type }} - <strong>{{ $kyb->business_structure }}</strong></td>
                            </tr>
                            <tr>
                                <td class="ps-3">Industri (MCC)</td>
                                <td><strong>{{ $kyb->mcc }}</strong> ({{ $kyb->parent_industry }} - {{ $kyb->child_industry }})</td>
                            </tr>
                            <tr>
                                <td class="ps-3">Email & Telp Bisnis</td>
                                <td>{{ $kyb->merchant_email }} | {{ $kyb->merchant_phone }}</td>
                            </tr>
                            <tr>
                                <td class="ps-3">Website</td>
                                <td><strong>{{ $kyb->website }}</strong> <button class="btn btn-sm btn-light py-0 px-1 border" onclick="copy('{{ $kyb->website }}')"><i class="bi bi-clipboard"></i></button></td>
                            </tr>

                            <tr><td colspan="2" class="bg-light fw-bold text-center py-2">Lokasi Operasional</td></tr>
                            <tr>
                                <td class="ps-3">Provinsi & Kota</td>
                                <td>{{ $kyb->province }}, {{ $kyb->city }}</td>
                            </tr>
                            <tr>
                                <td class="ps-3">Kecamatan (District ID)</td>
                                <td><strong>{{ $kyb->district_id }}</strong> <button class="btn btn-sm btn-light py-0 px-1 border" onclick="copy('{{ $kyb->district_id }}')"><i class="bi bi-clipboard"></i></button></td>
                            </tr>
                            <tr>
                                <td class="ps-3">Alamat Lengkap</td>
                                <td>{{ $kyb->address }} <button class="btn btn-sm btn-light py-0 px-1 border" onclick="copy('{{ $kyb->address }}')"><i class="bi bi-clipboard"></i></button></td>
                            </tr>
                            <tr>
                                <td class="ps-3">Kode Pos</td>
                                <td>{{ $kyb->post_code }} <button class="btn btn-sm btn-light py-0 px-1 border" onclick="copy('{{ $kyb->post_code }}')"><i class="bi bi-clipboard"></i></button></td>
                            </tr>

                            <tr><td colspan="2" class="bg-light fw-bold text-center py-2">PIC Detail</td></tr>
                            <tr>
                                <td class="ps-3">Nama PIC</td>
                                <td>{{ $kyb->pic_name }} <button class="btn btn-sm btn-light py-0 px-1 border" onclick="copy('{{ $kyb->pic_name }}')"><i class="bi bi-clipboard"></i></button></td>
                            </tr>
                            <tr>
                                <td class="ps-3">Jabatan</td>
                                <td>{{ $kyb->pic_job_title ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="ps-3">Email PIC</td>
                                <td>{{ $kyb->pic_email }} <button class="btn btn-sm btn-light py-0 px-1 border" onclick="copy('{{ $kyb->pic_email }}')"><i class="bi bi-clipboard"></i></button></td>
                            </tr>
                            <tr>
                                <td class="ps-3">Telp PIC</td>
                                <td>{{ $kyb->pic_phone }} <button class="btn btn-sm btn-light py-0 px-1 border" onclick="copy('{{ $kyb->pic_phone }}')"><i class="bi bi-clipboard"></i></button></td>
                            </tr>

                            <tr><td colspan="2" class="bg-light fw-bold text-center py-2">Withdrawal Detail</td></tr>
                            <tr>
                                <td class="ps-3">Bank Tujuan</td>
                                <td><strong>{{ $kyb->bank_channel_code }}</strong></td>
                            </tr>
                            <tr>
                                <td class="ps-3">Nomor Rekening</td>
                                <td><strong>{{ $kyb->bank_account_number }}</strong> <button class="btn btn-sm btn-light py-0 px-1 border" onclick="copy('{{ $kyb->bank_account_number }}')"><i class="bi bi-clipboard"></i></button></td>
                            </tr>
                            {{-- 🚨 TAMPILKAN NAMA PEMILIK REKENING (BENEFICIARY NAME) --}}
                            <tr>
                                <td class="ps-3">Nama Pemilik Rekening</td>
                                <td><strong>{{ $kyb->bank_account_name }}</strong> <button class="btn btn-sm btn-light py-0 px-1 border" onclick="copy('{{ $kyb->bank_account_name }}')"><i class="bi bi-clipboard"></i></button></td>
                            </tr>
                            <tr>
                                <td class="ps-3">Auto Withdrawal</td>
                                <td><span class="badge {{ $kyb->auto_withdrawal == 'ON' ? 'bg-success' : 'bg-secondary' }}">{{ $kyb->auto_withdrawal }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm border-start border-primary border-4">
                <div class="card-header bg-primary text-white fw-bold">Kredensial Produksi Pivot</div>
                <div class="card-body">
                    <form action="{{ route('admin.kyb.approve', $kyb->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Sub-Account ID (Dari Pivot)</label>
                            <input type="text" name="pivot_sub_account_id" class="form-control" value="{{ $kyb->pivot_sub_account_id }}" required placeholder="Contoh: SA-XXXX-XXXX">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Merchant ID</label>
                            <input type="text" name="merchant_id" class="form-control" value="{{ $kyb->merchant_id }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Merchant Secret</label>
                            <input type="password" name="merchant_secret" class="form-control" value="{{ $kyb->merchant_secret }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Callback URL (Webhook)</label>
                            <input type="text" name="callback_url" class="form-control" value="{{ $kyb->callback_url ?? 'https://' . ($kyb->website->domain ?? $kyb->website . '.ashop.asia') . '/pivot/webhook' }}">
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-success fw-bold">APPROVE & AKTIFKAN PEMBAYARAN</button>
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmReject()">Tolak Pengajuan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function copy(text) {
        navigator.clipboard.writeText(text);
        alert('Teks berhasil disalin!');
    }
</script>
@endsection