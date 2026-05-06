@extends('layouts.admin') @section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold text-primary">Super Admin Panel</h2>
            <p class="text-muted">Selamat datang, Bos! Ini area kendali pusat.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body">
                    <h5 class="opacity-75">Total Klien</h5>
                    <h1 class="display-4 fw-bold mb-0">{{ $totalUsers }}</h1>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white h-100">
                <div class="card-body">
                    <h5 class="opacity-75">Total Website Aktif</h5>
                    <h1 class="display-4 fw-bold mb-0">{{ $totalWebsites }}</h1>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning text-dark h-100">
                <div class="card-body">
                    <h5 class="opacity-75">Pendapatan</h5>
                    <h1 class="display-4 fw-bold mb-0">
                        Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                    </h1>
                </div>
            </div>
        </div>
    </div>

    {{-- ... (Setelah 3 Card Statistik) ... --}}
    @php
    // Deteksi URL admin (localhost:8000) secara manual
    $adminUrl = env('APP_URL'); 
    
    // Tambah port 8000 jika di local (karena env biasanya cuma localhost tanpa port)
    if (request()->server('SERVER_PORT') == '8000' && !str_contains($adminUrl, ':8000')) {
        $adminUrl .= ':8000';
    }
    
    // Pastikan ada http
    if (!str_starts_with($adminUrl, 'http')) {
        $adminUrl = 'http://' . $adminUrl;
    }
@endphp
    {{-- ALERT JIKA ADA TRANSAKSI PENDING --}}
    @if($pendingTransactions > 0)
    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mt-4" role="alert">
        <i class="bi bi-exclamation-circle-fill fs-4 me-3"></i>
        <div>
            <strong>Perhatian!</strong> Ada <span class="badge bg-dark">{{ $pendingTransactions }}</span> pembayaran baru menunggu verifikasi.
            <a href="{{ route('admin.transactions.index') }}" class="alert-link">Cek Sekarang &rarr;</a>
        </div>
    </div>
    @endif

    <div class="row mt-4">
        {{-- KOLOM KIRI: TRANSAKSI TERBARU --}}
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold m-0">Transaksi Terakhir</h6>
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm btn-light">Lihat Semua</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light small">
                            <tr>
                                <th>User</th>
                                <th>Paket</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($latestTransactions as $trx)
                            <tr>
                                <td>{{ $trx->user->name ?? 'Deleted User' }}</td>
                                <td><span class="badge bg-info text-dark">{{ $trx->package_name }}</span></td>
                                <td class="fw-bold text-success">Rp {{ number_format($trx->amount) }}</td>
                                <td>
                                    @if($trx->status == 'pending')
                                        <span class="badge bg-warning text-dark">Menunggu</span>
                                    @elseif($trx->status == 'approved')
                                        <span class="badge bg-success">Diterima</span>
                                    @else
                                        <span class="badge bg-danger">Ditolak</span>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $trx->created_at->diffForHumans() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: USER BARU --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold m-0">Website Baru Daftar</h6>
                </div>
                <div class="list-group list-group-flush">
                    @foreach($latestWebsites as $web)

                   @php
                        $mainDomain = parse_url(config('app.url'), PHP_URL_HOST);

                        // Scheme dinamis berdasarkan environment
                        $scheme = app()->environment('local') ? 'http://' : 'https://';

                        // Port hanya untuk local
                        $port = app()->environment('local') ? ':8000' : '';

                        $storeUrl = $scheme . $web->subdomain . '.' . $mainDomain . $port;
                    @endphp
                    <div class="list-group-item px-3 py-3">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1 fw-bold">{{ $web->site_name }}</h6>
                            <small class="text-muted">{{ $web->created_at->format('d M') }}</small>
                        </div>
                        <p class="mb-1 small text-muted">Owner: {{ $web->user->name ?? '-' }}</p>
                        <small>
                            <a href="{{ $storeUrl }}" target="_blank" class="text-decoration-none">
                                <i class="bi bi-box-arrow-up-right"></i> Kunjungi
                            </a>
                        </small>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- WIDGET PENDING PENARIKAN DANA -->
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold">Request Penarikan Dana</h6>
                    <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-sm btn-link text-decoration-none">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($pendingWithdrawals as $wd)
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <h6 class="mb-1 text-dark fw-bold">{{ $wd->website->name ?? 'Toko Tidak Diketahui' }}</h6>
                                    <small class="text-muted">{{ $wd->created_at->diffForHumans() }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold text-danger d-block">Rp {{ number_format($wd->amount, 0, ',', '.') }}</span>
                                    <a href="{{ route('admin.withdrawals.index') }}" class="badge bg-warning text-dark text-decoration-none">Proses</a>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted py-4">
                                Tidak ada request penarikan tertunda.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection