@extends('layouts.client')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="m-0">Keuangan & Saldo</h2>
    </div>

    <!-- Menampilkan Pesan Notifikasi -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <!-- 💰 KARTU SALDO & FORM TARIK DANA -->
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="text-muted mb-2">Saldo Aktif (Siap Tarik)</h5>
                    <h1 class="text-success fw-bold mb-4">
                        {{-- 🚨 BACA DARI VARIABEL PIVOT API ($activeBalance) --}}
                        Rp {{ number_format($activeBalance, 0, ',', '.') }}
                    </h1>

                    <hr>
                    <h6 class="mb-3 fw-bold">Tarik Dana ke Rekening</h6>
                    
                    {{-- 🚨 CEK STATUS KYB (BUKAN LAGI TABEL WEBSITE) --}}
                    @if(empty($kyb) || $kyb->status !== 'approved')
                        <div class="alert alert-warning text-sm">
                            <i class="bi bi-exclamation-triangle me-1"></i> Silakan lengkapi Verifikasi Bisnis (KYB) Anda terlebih dahulu untuk mengaktifkan pencairan dana.
                            <br><a href="{{ route('client.kyb.settings') }}" class="btn btn-sm btn-dark mt-2">Cek Status KYB</a>
                        </div>
                    @else
                        <div class="bg-light p-3 rounded mb-3 text-sm">
                            {{-- 🚨 BACA DATA REKENING DARI TABEL KYB --}}
                            <strong>Bank Tujuan:</strong> {{ $kyb->bank_channel_code }}<br>
                            <strong>No. Rekening:</strong> {{ $kyb->bank_account_number }}<br>
                            <strong>Atas Nama:</strong> {{ $kyb->bank_account_name }}
                        </div>

                        {{-- PERINGATAN AUTO-WITHDRAWAL --}}
                        @if($kyb->auto_withdrawal === 'ON')
                            <div class="alert alert-info text-sm mb-3">
                                <i class="bi bi-info-circle me-1"></i> Mode <strong>Auto-Withdrawal</strong> aktif. Saldo Anda akan otomatis ditransfer ke rekening di atas setiap hari kerja tanpa perlu ditarik manual.
                            </div>
                        @else
                            <form action="{{ route('client.wallet.withdraw', $website) }}" method="POST">
                                @csrf
                                <div class="form-group mb-3">
                                    <label for="amount" class="small fw-bold">Nominal Penarikan (Min. Rp 50.000)</label>
                                    <div class="input-group mt-1">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" name="amount" id="amount" min="50000" max="{{ $activeBalance }}" required placeholder="Contoh: 150000">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 fw-bold" {{ $activeBalance < 50000 ? 'disabled' : '' }}>
                                    <i class="bi bi-wallet2 me-1"></i> Ajukan Pencairan
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- 📑 TABEL RIWAYAT TRANSAKSI & PENCAIRAN -->
        <div class="col-md-7 mb-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h6 class="m-0 fw-bold">Riwayat Pencairan (Withdrawal)</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Nominal</th>
                                    <th>Status</th>
                                    <th>Bank Tujuan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($withdrawals as $wd)
                                    <tr>
                                        <td>{{ $wd->created_at->format('d M Y, H:i') }}</td>
                                        <td class="fw-bold">Rp {{ number_format($wd->amount, 0, ',', '.') }}</td>
                                        <td>
                                            @if($wd->status == 'pending')
                                                <span class="badge bg-warning text-dark">Menunggu</span>
                                            @elseif($wd->status == 'approved')
                                                <span class="badge bg-success">Berhasil</span>
                                            @else
                                                <span class="badge bg-danger">Ditolak</span>
                                            @endif
                                        </td>
                                        <td><small>{{ $wd->bank_name }} ({{ $wd->bank_account_number }})</small></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">Belum ada riwayat pencairan dana.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($withdrawals->hasPages())
                    <div class="card-footer bg-white border-top-0">
                        {{ $withdrawals->links() }}
                    </div>
                @endif
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h6 class="m-0 fw-bold">Buku Tabungan (Mutasi Saldo)</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Deskripsi</th>
                                    <th>Tipe</th>
                                    <th>Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mutations as $mutasi)
                                    <tr>
                                        <td>{{ $mutasi->created_at->format('d M Y, H:i') }}</td>
                                        <td>{{ $mutasi->description }}</td>
                                        <td>
                                            @if($mutasi->type == 'credit')
                                                <span class="badge bg-success">Masuk</span>
                                            @else
                                                <span class="badge bg-danger">Keluar</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold {{ $mutasi->type == 'credit' ? 'text-success' : 'text-danger' }}">
                                            {{ $mutasi->type == 'credit' ? '+' : '-' }} Rp {{ number_format($mutasi->amount, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">Belum ada mutasi saldo.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($mutations->hasPages())
                    <div class="card-footer bg-white border-top-0">
                        {{ $mutations->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection