@extends('layouts.admin') 

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold text-primary">Super Admin Panel</h2>
            <p class="text-muted">Pantau pertumbuhan platform dan status operasional harian.</p>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- 1. BARIS METRIK UTAMA (4 KOLOM) --}}
    {{-- ========================================== --}}
    <div class="row g-4">
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body">
                    <h6 class="opacity-75 mb-2"><i class="bi bi-people me-2"></i>Total Klien</h6>
                    <h2 class="fw-bold mb-0">{{ number_format($totalUsers) }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-success text-white h-100">
                <div class="card-body">
                    <h6 class="opacity-75 mb-2"><i class="bi bi-globe me-2"></i>Website Aktif</h6>
                    <h2 class="fw-bold mb-0">{{ number_format($totalWebsites) }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm text-white h-100" style="background-color: #6f42c1;">
                <div class="card-body">
                    <h6 class="opacity-75 mb-2" title="Total Perputaran Uang Toko Klien Bulan Ini"><i class="bi bi-graph-up-arrow me-2"></i>GMV (Bulan Ini)</h6>
                    <h3 class="fw-bold mb-0 text-truncate">Rp {{ number_format($totalGMV, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-warning text-dark h-100">
                <div class="card-body">
                    <h6 class="opacity-75 mb-2"><i class="bi bi-wallet2 me-2"></i>Pendapatan SaaS</h6>
                    <h3 class="fw-bold mb-0 text-truncate">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- 2. BARIS NOTIFIKASI (ALERT PENDING ACTIONS) --}}
    {{-- ========================================== --}}
    <div class="row mt-4">
        <div class="col-12">
            @if($pendingTransactions > 0)
            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-3" role="alert">
                <i class="bi bi-receipt fs-4 me-3"></i>
                <div>
                    <strong>Perhatian!</strong> Ada <span class="badge bg-dark">{{ $pendingTransactions }}</span> pembayaran langganan paket menunggu verifikasi.
                    <a href="{{ route('admin.transactions.index') }}" class="alert-link ms-2">Cek Sekarang &rarr;</a>
                </div>
            </div>
            @endif

            @if($pendingKybCount > 0)
            <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center mb-3" role="alert">
                <i class="bi bi-shield-lock fs-4 me-3"></i>
                <div>
                    <strong>Prioritas!</strong> Ada <span class="badge bg-dark">{{ $pendingKybCount }}</span> pengajuan KYB Pivot dari Klien yang menunggu untuk diproses.
                    <a href="{{ route('admin.kyb.index') }}" class="alert-link ms-2">Proses KYB &rarr;</a>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- 3. BARIS LIST TERBARU (3 KOLOM SEJAJAR) --}}
    {{-- ========================================== --}}
    <div class="row mt-2 g-4">
        
        {{-- KOLOM 1: TRANSAKSI LANGGANAN --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom-0">
                    <h6 class="fw-bold m-0"><i class="bi bi-receipt me-2 text-primary"></i>Transaksi Langganan</h6>
                    <a href="{{ route('admin.transactions.index') }}" class="small text-decoration-none">Lihat Semua</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($latestTransactions as $trx)
                        <div class="list-group-item px-3 py-3">
                            <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-truncate" style="max-width: 150px;">{{ $trx->user->name ?? 'User Dihapus' }}</span>
                                <span class="fw-bold text-success">Rp {{ number_format($trx->amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <small class="text-muted">{{ $trx->package_name }}</small>
                                @if($trx->status == 'pending')
                                    <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">Pending</span>
                                @elseif($trx->status == 'approved')
                                    <span class="badge bg-success" style="font-size: 0.65rem;">Lunas</span>
                                @else
                                    <span class="badge bg-danger" style="font-size: 0.65rem;">Ditolak</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted py-4">Belum ada transaksi.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- KOLOM 2: WEBSITE BARU DAFTAR --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom-0">
                    <h6 class="fw-bold m-0"><i class="bi bi-globe me-2 text-success"></i>Website Baru</h6>
                    <a href="{{ route('admin.websites.index') }}" class="small text-decoration-none">Lihat Semua</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($latestWebsites as $web)
                        @php
                            $mainDomain = parse_url(config('app.url'), PHP_URL_HOST);
                            $scheme = app()->environment('local') ? 'http://' : 'https://';
                            $port = app()->environment('local') ? ':8000' : '';
                            $storeUrl = $scheme . $web->subdomain . '.' . $mainDomain . $port;
                        @endphp
                        <div class="list-group-item px-3 py-3">
                            <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 fw-bold text-truncate" style="max-width: 160px;">{{ $web->site_name }}</h6>
                                <small class="text-muted" style="font-size: 0.7rem;">{{ $web->created_at->format('d M y') }}</small>
                            </div>
                            <div class="d-flex w-100 justify-content-between align-items-center mt-2">
                                <p class="mb-0 small text-muted text-truncate" style="max-width: 140px;">Owner: {{ $web->user->name ?? '-' }}</p>
                                <a href="{{ $storeUrl }}" target="_blank" class="btn btn-sm btn-light border py-0 px-2" style="font-size: 0.7rem;">
                                    <i class="bi bi-box-arrow-up-right"></i> Kunjungi
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted py-4">Belum ada website baru.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- KOLOM 3: PENGAJUAN KYB (PIVOT) --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom-0">
                    <h6 class="fw-bold m-0"><i class="bi bi-shield-lock me-2 text-danger"></i>Pengajuan Pivot</h6>
                    <a href="{{ route('admin.kyb.index') }}" class="small text-decoration-none">Lihat Semua</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($latestKyb as $kyb)
                        <div class="list-group-item px-3 py-3">
                            <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-truncate" style="max-width: 160px;">{{ $kyb->short_name ?? 'Data Klien' }}</span>
                                <small class="text-muted" style="font-size: 0.7rem;">{{ $kyb->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="d-flex w-100 justify-content-between align-items-center mt-2">
                                <span class="small text-muted text-truncate" style="max-width: 150px;">{{ $kyb->website }}</span>
                                @if($kyb->status == 'pending')
                                    <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">Menunggu</span>
                                @elseif($kyb->status == 'approved')
                                    <span class="badge bg-success" style="font-size: 0.65rem;">Disetujui</span>
                                @else
                                    <span class="badge bg-danger" style="font-size: 0.65rem;">Ditolak</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted py-4">Tidak ada pengajuan.</div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>
@endsection