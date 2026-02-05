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
                        // LOGIKA PINTAR PEMBUAT URL
                        $port = request()->server('SERVER_PORT') == 8000 ? ':8000' : ''; // Deteksi Port otomatis
                        $protocol = 'http://'; // Localhost biasanya http

                        if ($web->custom_domain) {
                            // Jika punya domain sendiri (elecjos.com)
                            $storeUrl = $protocol . $web->custom_domain . $port;
                        } else {
                            // Jika pakai subdomain bawaan (elecjos.localhost)
                            // Kita paksa pakai .localhost agar terbaca di sistem host
                            $storeUrl = $protocol . $web->subdomain . '.localhost' . $port;
                        }
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
        </div>
    </div>
</div>
@endsection