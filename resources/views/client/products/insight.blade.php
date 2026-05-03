@extends('layouts.client')

@section('title', 'Analisis Produk: ' . $product->name)

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <a href="{{ route('client.products.index', $website->id) }}" class="text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Produk
        </a>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <h3 class="fw-bold text-dark mb-1">Analisis Pergerakan Stok (AI)</h3>
            <p class="text-muted">Menganalisis kecepatan penjualan produk <strong>{{ $product->name }}</strong> berdasarkan data 30 hari terakhir.</p>
        </div>
    </div>

    <div class="row g-4">
        {{-- KOTAK KIRI: METRIK UTAMA --}}
        <div class="col-md-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 text-center">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" class="img-fluid rounded mb-3" style="max-height: 150px;">
                    @else
                        <div class="bg-light rounded mb-3 d-flex align-items-center justify-content-center mx-auto" style="height: 150px; width: 150px;">
                            <i class="bi bi-image text-muted fs-1"></i>
                        </div>
                    @endif
                    
                    <h5 class="fw-bold mb-2">{{ $product->name }}</h5>
                    
                    {{-- 🚨 TAMBAHAN BARU: BADGE KARAKTERISTIK PRODUK --}}
                    <div class="mb-4">
                        @if($product->moving_class == 'fast')
                            <span class="badge bg-danger rounded-pill px-3 py-2"><i class="bi bi-fire me-1"></i> Fast Moving (Cepat Habis)</span>
                        @elseif($product->moving_class == 'slow')
                            <span class="badge bg-success rounded-pill px-3 py-2"><i class="bi bi-turtle me-1"></i> Slow Moving (Jarang Laku)</span>
                        @else
                            <span class="badge bg-primary rounded-pill px-3 py-2"><i class="bi bi-box-seam me-1"></i> Pergerakan Normal</span>
                        @endif
                    </div>

                    <p class="text-muted mb-4">Sisa Stok Fisik: <span class="fw-bold text-dark fs-3">{{ $product->stock }}</span> Unit</p>
                    <p class="text-muted mb-4">Total Penjualan: <span class="fw-bold text-dark fs-8">{{ $penjualantotal }}</span> Unit</p>

                    {{-- KOTAK VELOCITY --}}
                    <div class="p-3 rounded bg-light border mb-3">
                        <span class="d-block text-muted small text-uppercase fw-bold mb-1">Kecepatan Penjualan (Velocity)</span>
                        <h2 class="fw-bold text-primary mb-0">{{ number_format($product->velocity, 2) }} <span class="fs-6 text-muted fw-normal">unit/hari</span></h2>
                    </div>

                    {{-- KOTAK REKOMENDASI AI --}}
                    <div class="p-3 rounded border {{ $recommendedRestock > 0 ? 'bg-danger bg-opacity-10 border-danger' : 'bg-success bg-opacity-10 border-success' }}">
                        <span class="d-block text-muted small text-uppercase fw-bold mb-1">
                            <i class="bi bi-robot me-1"></i> Rekomendasi Restock ({{ $targetDays }} Hari)
                        </span>
                        
                        @if($recommendedRestock > 0)
                            <h2 class="fw-bold text-danger mb-0">+{{ $recommendedRestock }} <span class="fs-6 fw-normal">Unit</span></h2>
                            <small class="text-danger">Segera pesan ke supplier!</small>
                        @else
                            <h2 class="fw-bold text-success mb-0">0 <span class="fs-6 fw-normal">Unit</span></h2>
                            <small class="text-success">Stok masih sangat aman.</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- KOTAK KANAN: DIAGNOSA AI --}}
        <div class="col-md-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-4 border-bottom pb-2"><i class="bi bi-robot me-2 text-info"></i>Diagnosa Sistem AI</h6>

                    {{-- 🚨 PERBAIKAN: LOGIKA DIAGNOSA YANG LEBIH CERDAS SESUAI MOVING CLASS --}}
                    @if($product->stock_status === 'Critical')
                        <div class="alert alert-danger border-danger">
                            <h5 class="alert-heading fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Status Kritis: Segera Restock!</h5>
                            <hr>
                            <p class="mb-0">
                                Sebagai produk berstatus <strong>{{ ucfirst($product->moving_class) }}</strong>, sisa batas waktu aman Anda sangat ketat. Berdasarkan tren, stok diprediksi <strong>habis dalam {{ $product->runway_days }} hari</strong>. <br><br>
                                Segera lakukan pemesanan (Purchase Order) ke supplier agar operasional tidak terhenti.
                            </p>
                        </div>
                    @elseif($product->stock_status === 'Warning')
                        <div class="alert alert-warning border-warning text-dark">
                            <h5 class="alert-heading fw-bold"><i class="bi bi-exclamation-circle-fill me-2"></i>Status: Bersiap Restock</h5>
                            <hr>
                            <p class="mb-0">
                                Stok produk <strong>{{ ucfirst($product->moving_class) }}</strong> ini akan <strong>habis dalam {{ $product->runway_days }} hari</strong>. <br>
                                Anda belum kehabisan barang, namun disarankan untuk mulai menyiapkan rencana pemesanan ke supplier minggu ini.
                            </p>
                        </div>
                    @elseif($product->stock_status === 'Safe')
                        @if($product->stock < ($product->velocity * $targetDays))
                            <div class="alert alert-info border-info">
                                <h5 class="alert-heading fw-bold"><i class="bi bi-info-circle-fill me-2"></i>Status: Perlu Perhatian</h5>
                                <hr>
                                <p class="mb-0">
                                    Stok saat ini ({{ $product->stock }}) masih cukup untuk <strong>{{ $product->runway_days }} hari</strong>. Namun, jumlah ini berada di bawah target ideal {{ $targetDays }} hari ({{ ceil($product->velocity * $targetDays) }} unit). Pertimbangkan menambah sedikit stok.
                                </p>
                            </div>
                        @else
                            <div class="alert alert-success border-success">
                                <h5 class="alert-heading fw-bold"><i class="bi bi-check-circle-fill me-2"></i>Status Aman</h5>
                                <hr>
                                <p class="mb-0">Stok produk <strong>{{ ucfirst($product->moving_class) }}</strong> ini sangat mencukupi (sisa runway: <strong>{{ $product->runway_days }} hari</strong>). Tidak perlu ada tindakan restock saat ini.</p>
                            </div>
                        @endif
                    @elseif(in_array($product->stock_status, ['Overstock', 'Dead Stock']))
                        <div class="alert alert-secondary border-secondary text-dark">
                            <h5 class="alert-heading fw-bold"><i class="bi bi-box-seam me-2"></i>Overstock / Dead Stock</h5>
                            <hr>
                            <p class="mb-0">
                                Produk ini memiliki stok yang menumpuk namun <strong>tidak mengalami penjualan</strong> yang berarti.
                                <br><br>
                                <strong>Rekomendasi Aksi:</strong> 
                                <ul class="mb-0 mt-1">
                                    <li>Evaluasi ulang <strong>Harga Jual</strong>, apakah terlalu mahal dibanding pasar?</li>
                                    <li>Pertimbangkan untuk membuat promo cuci gudang (*clearance sale*).</li>
                                </ul>
                            </p>
                        </div>
                    @else
                        <div class="alert alert-dark border-dark">
                            <h5 class="alert-heading fw-bold"><i class="bi bi-x-circle-fill me-2"></i>Stok Kosong</h5>
                            <hr>
                            <p class="mb-0">Barang saat ini kosong (0 unit) di gudang Anda.</p>
                        </div>
                    @endif

                    {{-- AREA GRAFIK PROYEKSI --}}
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="fw-bold mb-3"><i class="bi bi-graph-down text-primary me-2"></i>Proyeksi Penurunan Stok & Batas Aman</h6>
                        
                        @if($product->stock > 0 && $product->velocity > 0)
                            <div style="position: relative; height:250px; width:100%">
                                <canvas id="runwayChart"></canvas>
                            </div>
                            <small class="text-muted d-block mt-2 text-center">Garis Merah memprediksi penurunan stok. Garis Hijau adalah batas minimal stok aman.</small>
                        @elseif($product->stock > 0 && $product->velocity == 0)
                            <div class="bg-light rounded p-4 text-center border">
                                <i class="bi bi-dash-circle text-muted fs-1"></i>
                                <p class="text-muted mb-0 mt-2">Grafik proyeksi tidak tersedia karena belum ada histori penjualan (Velocity = 0).</p>
                            </div>
                        @else
                            <div class="bg-light rounded p-4 text-center border">
                                <i class="bi bi-box2 text-muted fs-1"></i>
                                <p class="text-muted mb-0 mt-2">Stok kosong (0). Tidak ada data yang bisa diproyeksikan.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        @if($product->stock > 0 && $product->velocity > 0)
            const ctx = document.getElementById('runwayChart').getContext('2d');
            
            const labels = @json($chartLabels);
            const dataPoints = @json($chartData);
            const targetPoints = @json($targetLineData);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Proyeksi Sisa Stok',
                            data: dataPoints,
                            borderColor: 'rgba(220, 53, 69, 1)',
                            backgroundColor: 'rgba(220, 53, 69, 0.1)',
                            borderWidth: 3,
                            pointBackgroundColor: 'rgba(220, 53, 69, 1)',
                            pointRadius: 4,
                            fill: true,
                            tension: 0.3,
                            order: 2
                        },
                        {
                            label: 'Target Stok Aman',
                            data: targetPoints,
                            borderColor: 'rgba(25, 135, 84, 1)',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            pointRadius: 0,
                            fill: false,
                            tension: 0,
                            order: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Jumlah Unit' }
                        },
                        x: {
                            title: { display: true, text: 'Garis Waktu' }
                        }
                    },
                    plugins: {
                        legend: { 
                            display: true,
                            position: 'bottom' 
                        }
                    }
                }
            });
        @endif
    });
</script>
@endsection