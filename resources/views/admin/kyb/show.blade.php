@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold">Detail Verifikasi: {{ $kyb->name }}</h4>
        <span class="badge {{ $kyb->status == 'pending' ? 'bg-warning text-dark' : ($kyb->status == 'approved' ? 'bg-success' : 'bg-danger') }}">
            Status: {{ strtoupper($kyb->status) }}
        </span>
    </div>

    @if (session('success'))
        <div class="alert alert-success fw-bold">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger fw-bold">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Data Lengkap Merchant (Untuk Form Pivot)</div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0 align-middle">
                        <tbody>
                            <tr><td colspan="2" class="bg-light fw-bold text-center py-2 text-primary">Merchant Detail</td></tr>
                            
                            <tr>
                                <td width="35%" class="ps-3 py-2 text-muted small">Nama Merchant <br>(Merchant Name)</td>
                                <td>
                                    <div class="d-flex justify-content-between align-items-center pe-2">
                                        <strong class="text-break">{{ $kyb->name }}</strong>
                                        <button class="btn btn-outline-secondary btn-sm ms-2 flex-shrink-0" onclick="copyText('{{ addslashes($kyb->name) }}')" title="Salin">
                                            <i class="bi bi-clipboard"></i> Salin
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            
                            <tr>
                                <td class="ps-3 py-2 text-muted small">Merchant Short Name</td>
                                <td>
                                    <div class="d-flex justify-content-between align-items-center pe-2">
                                        <strong>{{ $kyb->short_name }}</strong>
                                        <button class="btn btn-outline-secondary btn-sm ms-2 flex-shrink-0" onclick="copyText('{{ addslashes($kyb->short_name) }}')" title="Salin">
                                            <i class="bi bi-clipboard"></i> Salin
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr><td colspan="2" class="bg-light fw-bold text-center py-2 text-primary">Lokasi Operasional</td></tr>
                            
                            <tr>
                                <td class="ps-3 py-2 text-muted small">Negara Pendirian <br>(Country of Establishment)</td>
                                <td>
                                    <div class="d-flex justify-content-between align-items-center pe-2">
                                        <strong>{{ $kyb->country_of_entity }}</strong>
                                        <button class="btn btn-outline-secondary btn-sm ms-2 flex-shrink-0" onclick="copyText('{{ addslashes($kyb->country_of_entity) }}')" title="Salin">
                                            <i class="bi bi-clipboard"></i> Salin
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            
                            <tr>
                                <td class="ps-3 py-2 text-muted small">Provinsi (Province)</td>
                                <td>
                                    <div class="d-flex justify-content-between align-items-center pe-2">
                                        <strong>{{ $kyb->province }}</strong>
                                        <button class="btn btn-outline-secondary btn-sm ms-2 flex-shrink-0" onclick="copyText('{{ addslashes($kyb->province) }}')" title="Salin">
                                            <i class="bi bi-clipboard"></i> Salin
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="ps-3 py-2 text-muted small">Kota (City)</td>
                                <td>
                                    <div class="d-flex justify-content-between align-items-center pe-2">
                                        <strong>{{ $kyb->city }}</strong>
                                        <button class="btn btn-outline-secondary btn-sm ms-2 flex-shrink-0" onclick="copyText('{{ addslashes($kyb->city) }}')" title="Salin">
                                            <i class="bi bi-clipboard"></i> Salin
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="ps-3 py-2 text-muted small">Kecamatan (District ID)</td>
                                <td>
                                    <div class="d-flex justify-content-between align-items-center pe-2">
                                        <strong>{{ $kyb->district_id }} - {{ $kyb->district_name }}</strong>
                                        <button class="btn btn-outline-secondary btn-sm ms-2 flex-shrink-0" onclick="copyText('{{ addslashes($kyb->district_name) }}')" title="Salin">
                                            <i class="bi bi-clipboard"></i> Salin
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="ps-3 py-2 text-muted small">Alamat Lengkap <br> (Address Line)</td>
                                <td>
                                    <div class="d-flex justify-content-between align-items-center pe-2">
                                        <strong class="text-break">{{ $kyb->address }}</strong>
                                        <button class="btn btn-outline-secondary btn-sm ms-2 flex-shrink-0" onclick="copyText('{{ addslashes($kyb->address) }}')" title="Salin">
                                            <i class="bi bi-clipboard"></i> Salin
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="ps-3 py-2 text-muted small">Kode Pos <br> (Postal Code)</td>
                                <td>
                                    <div class="d-flex justify-content-between align-items-center pe-2">
                                        <strong>{{ $kyb->post_code }}</strong>
                                        <button class="btn btn-outline-secondary btn-sm ms-2 flex-shrink-0" onclick="copyText('{{ addslashes($kyb->post_code) }}')" title="Salin">
                                            <i class="bi bi-clipboard"></i> Salin
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr><td colspan="2" class="bg-light fw-bold text-center py-2 text-primary">Karakteristik Bisnis</td></tr>

                            <tr>
                                <td class="ps-3 py-2 text-muted small">Jenis Perdagangan<br> (Merchant Type)</td>
                                <td>
                                    <div class="d-flex justify-content-between align-items-center pe-2">
                                        @if($kyb->business_type == 'INDIVIDUAL')
                                            <span class="badge bg-info text-dark">Perorangan</span>
                                        @else
                                            <span class="badge bg-primary">Firma</span>
                                        @endif
                                        
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="ps-3 py-2 text-muted small">MCC (Industry)</td>
                                <td class="py-2">
                                <div class="d-flex justify-content-between align-items-center pe-2">
                                        <strong>{{ $kyb->mcc }} - {{ $kyb->mcc_name }}</strong>
                                        <button class="btn btn-outline-secondary btn-sm ms-2 flex-shrink-0" onclick="copyText('{{ addslashes($kyb->parent_industry) }} - {{ addslashes($kyb->child_industry) }}')" title="Salin">
                                            <i class="bi bi-clipboard"></i> Salin
                                </button>
                                    </div>
                                </td>
                                
                            </tr>

                            <tr>
                                <td class="ps-3 py-2 text-muted small">Jenis Industri (Industry Type)</td>
                                <td>
                                    <div class="d-flex justify-content-between align-items-center pe-2">
                                        <strong>{{ $kyb->digital_status }}</strong>
                                        
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="ps-3 py-2 text-muted small">Struktur Perdagangan (Merchant Structure)</td>
                                <td>
                                    <div class="d-flex justify-content-between align-items-center pe-2">
                                        <strong>{{ $kyb->business_structure }}</strong>
                                        
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="ps-3 py-2 text-muted small">Website</td>
                                <td>
                                    <div class="d-flex justify-content-between align-items-center pe-2">
                                        <strong class="text-primary text-break">https://{{ $kyb->website }}</strong>
                                        <button class="btn btn-outline-secondary btn-sm ms-2 flex-shrink-0" onclick="copyText('https://{{ addslashes($kyb->website) }}')" title="Salin">
                                            <i class="bi bi-clipboard"></i> Salin
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="ps-3 py-2 text-muted small">Deskripsi Usaha <br> (Description)</td>
                                <td>
                                    <div class="d-flex justify-content-between align-items-center pe-2">
                                        <strong class="text-break">{{ $kyb->description }}</strong>
                                        <button class="btn btn-outline-secondary btn-sm ms-2 flex-shrink-0" onclick="copyText('{{ addslashes($kyb->description) }}')" title="Salin">
                                            <i class="bi bi-clipboard"></i> Salin
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="ps-3 py-2 text-muted small">Email & Telp Bisnis</td>
                                <td class="py-2"><strong>{{ $kyb->merchant_email }} | {{ $kyb->merchant_phone }}</strong></td>
                            </tr>

                            <tr><td colspan="2" class="bg-light fw-bold text-center py-2 text-primary">Withdrawal Detail</td></tr>

                            <tr>
                                <td class="ps-3 py-2 text-muted small">Bank Tujuan <br> (Bank Account)</td>
                                <td>
                                    <div class="d-flex justify-content-between align-items-center pe-2">
                                        <strong>{{ $kyb->bank_channel_code }}</strong>
                                        <button class="btn btn-outline-secondary btn-sm ms-2 flex-shrink-0" onclick="copyText('{{ addslashes($kyb->bank_channel_code) }}')" title="Salin">
                                            <i class="bi bi-clipboard"></i> Salin
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="ps-3 py-2 text-muted small">Nomor Rekening <br>(Account Number)</td>
                                <td>
                                    <div class="d-flex justify-content-between align-items-center pe-2">
                                        <strong>{{ $kyb->bank_account_number }}</strong>
                                        <button class="btn btn-outline-secondary btn-sm ms-2 flex-shrink-0" onclick="copyText('{{ addslashes($kyb->bank_account_number) }}')" title="Salin">
                                            <i class="bi bi-clipboard"></i> Salin
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="ps-3 py-2 text-muted small">Nama Pemilik Rekening <br> (Beneficiary Name)</td>
                                <td>
                                    <div class="d-flex justify-content-between align-items-center pe-2">
                                        <strong>{{ $kyb->bank_account_name }}</strong>
                                        <button class="btn btn-outline-secondary btn-sm ms-2 flex-shrink-0" onclick="copyText('{{ addslashes($kyb->bank_account_name) }}')" title="Salin">
                                            <i class="bi bi-clipboard"></i> Salin
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="ps-3 py-2 text-muted small">Auto Withdrawal</td>
                                <td class="py-2"><span class="badge {{ $kyb->auto_withdrawal == 'ON' ? 'bg-info text-dark' : 'bg-secondary' }}">{{ $kyb->auto_withdrawal }}</span></td>
                            </tr>

                            <tr><td colspan="2" class="bg-light fw-bold text-center py-2 text-primary">PIC Detail</td></tr>
                            
                            <tr>
                                <td class="ps-3 py-2 text-muted small">Nama PIC (PIC Name)</td>
                                <td>
                                    <div class="d-flex justify-content-between align-items-center pe-2">
                                        <strong>{{ $kyb->pic_name }}</strong>
                                        <button class="btn btn-outline-secondary btn-sm ms-2 flex-shrink-0" onclick="copyText('{{ addslashes($kyb->pic_name) }}')" title="Salin">
                                            <i class="bi bi-clipboard"></i> Salin
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="ps-3 py-2 text-muted small">Email PIC (PIC Email)</td>
                                <td>
                                    <div class="d-flex justify-content-between align-items-center pe-2">
                                        <strong>{{ $kyb->pic_email }}</strong>
                                        <button class="btn btn-outline-secondary btn-sm ms-2 flex-shrink-0" onclick="copyText('{{ addslashes($kyb->pic_email) }}')" title="Salin">
                                            <i class="bi bi-clipboard"></i> Salin
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="ps-3 py-2 text-muted small">Jabatan</td>
                                <td class="py-2"><strong>{{ $kyb->pic_job_title ?? '-' }}</strong></td>
                            </tr>

                            <tr>
                                <td class="ps-3 py-2 text-muted small">Telp PIC</td>
                                <td>
                                    <div class="d-flex justify-content-between align-items-center pe-2">
                                        <strong>{{ $kyb->pic_phone }}</strong>
                                        <button class="btn btn-outline-secondary btn-sm ms-2 flex-shrink-0" onclick="copyText('{{ addslashes($kyb->pic_phone) }}')" title="Salin">
                                            <i class="bi bi-clipboard"></i> Salin
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm border-start border-primary border-4 sticky-top" style="top: 20px;">
                <div class="card-header bg-white fw-bold py-3">
                    <i class="bi bi-gear-fill text-primary me-2"></i> Kredensial Produksi Pivot
                </div>
                <div class="card-body">
                    
                    {{-- FORM 1: Aksi Utama (Approve / Simpan Manual) --}}
                    <form action="{{ route('admin.kyb.approve', $kyb->id) }}" method="POST">
                        @csrf
                        
                        @if($kyb->status === 'pending')
                            <div class="d-grid mb-4">
                                <button type="submit" name="action" value="auto" class="btn btn-primary fw-bold py-2 shadow-sm" formnovalidate>
                                    <i class="bi bi-lightning-charge-fill me-1"></i> APPROVE & BUAT AKUN VIA API
                                </button>
                            </div>

                            <div class="position-relative mb-4">
                                <hr class="text-muted">
                                <span class="position-absolute top-50 start-50 translate-middle bg-white px-2 text-muted small fw-bold">Atau Fallback Manual</span>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Merchant ID</label>
                            <input type="text" name="merchant_id" class="form-control bg-light" value="{{ $kyb->merchant_id }}" {{ $kyb->status === 'approved' ? 'readonly' : 'required' }}>
                        </div>
                        
                        @if($kyb->status !== 'approved')
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" name="action" value="manual" class="btn btn-success fw-bold">
                                    <i class="bi bi-save me-1"></i> SIMPAN KREDENSIAL MANUAL
                                </button>
                            </div>
                        @endif
                    </form>

                    {{-- FORM 2: Aksi Penolakan (Hanya muncul jika pending) --}}
                    @if($kyb->status === 'pending')
                        <hr class="my-4 text-muted">
                        <div class="d-grid">
                            <button type="button" class="btn btn-outline-danger fw-bold" onclick="confirmReject()">
                                <i class="bi bi-x-circle me-1"></i> Tolak Pengajuan
                            </button>
                        </div>
                        
                        {{-- Form Tersembunyi untuk Menolak --}}
                        <form id="rejectForm" action="{{ route('admin.kyb.reject', $kyb->id) }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. FUNGSI COPY TEXT 
    function copyText(text) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Teks berhasil disalin!');
            }).catch(err => {
                console.error('Gagal menyalin: ', err);
            });
        } else {
            let textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.top = "0";
            textArea.style.left = "0";
            textArea.style.position = "fixed";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
                alert('Teks berhasil disalin!');
            } catch (err) {
                alert('Gagal menyalin teks.');
            }
            document.body.removeChild(textArea);
        }
    }

    // 2. FUNGSI KONFIRMASI TOLAK
    function confirmReject() {
        if (confirm('Apakah Anda yakin ingin menolak pengajuan verifikasi ini? Status merchant akan berubah menjadi "Rejected" dan mereka harus mengajukan ulang.')) {
            document.getElementById('rejectForm').submit();
        }
    }
</script>
@endsection