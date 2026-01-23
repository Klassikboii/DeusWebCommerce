@extends('layouts.client')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Dashboard Analitik</h4>
            <p class="text-muted m-0">Ringkasan performa toko Anda hari ini.</p>
        </div>
        <button class="btn btn-primary btn-sm">
            <i class="bi bi-download me-1"></i> Download Laporan
        </button>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            <span class="text-muted small text-uppercase fw-bold">Total Pendapatan</span>
                            <h3 class="fw-bold mb-0 mt-1">Rp 0</h3>
                        </div>
                        <div class="bg-success-subtle text-success rounded p-2" style="height: fit-content;">
                            <i class="bi bi-currency-dollar h5 m-0"></i>
                        </div>
                    </div>
                    <small class="text-success"><i class="bi bi-arrow-up"></i> 0%</small> <small class="text-muted">dari bulan lalu</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            <span class="text-muted small text-uppercase fw-bold">Total Order</span>
                            <h3 class="fw-bold mb-0 mt-1">0</h3>
                        </div>
                        <div class="bg-primary-subtle text-primary rounded p-2" style="height: fit-content;">
                            <i class="bi bi-cart h5 m-0"></i>
                        </div>
                    </div>
                    <small class="text-success"><i class="bi bi-arrow-up"></i> 0%</small> <small class="text-muted">dari bulan lalu</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            <span class="text-muted small text-uppercase fw-bold">Pengunjung</span>
                            <h3 class="fw-bold mb-0 mt-1">0</h3>
                        </div>
                        <div class="bg-info-subtle text-info rounded p-2" style="height: fit-content;">
                            <i class="bi bi-people h5 m-0"></i>
                        </div>
                    </div>
                    <small class="text-danger"><i class="bi bi-arrow-down"></i> 0%</small> <small class="text-muted">dari bulan lalu</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            <span class="text-muted small text-uppercase fw-bold">Konversi</span>
                            <h3 class="fw-bold mb-0 mt-1">Rp {{ number_format($stats['total_revenue'] ?? 0, 0, ',', '.') }}</h3>

                            <h3 class="fw-bold mb-0 mt-1">{{ $stats['total_orders'] ?? 0 }}</h3>
                        </div>
                        <div class="bg-warning-subtle text-warning rounded p-2" style="height: fit-content;">
                            <i class="bi bi-activity h5 m-0"></i>
                        </div>
                    </div>
                    <small class="text-muted">Rata-rata industri: 2.5%</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4 text-center text-muted" style="min-height: 300px; display: flex; flex-direction: column; justify-content: center;">
            <i class="bi bi-graph-up h1 mb-3"></i>
            <h5>Grafik Penjualan</h5>
            <p>Data grafik akan muncul setelah ada transaksi.</p>
        </div>
    </div>

</div>
@endsection