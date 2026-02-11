@extends('layouts.client')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<div class="container-fluid py-4">
    @php
    // LOGIKA PINTAR PEMBUAT URL
    $port = request()->server('SERVER_PORT') == 8000 ? ':8000' : ''; // Deteksi Port otomatis
    $protocol = 'http://'; // Localhost biasanya http

    if ($website->custom_domain) {
        // Jika punya domain sendiri (elecjos.com)
        $storeUrl = $protocol . $website->custom_domain . $port;
    } else {
        // Jika pakai subdomain bawaan (elecjos.localhost)
        // Kita paksa pakai .localhost agar terbaca di sistem host
        $storeUrl = $protocol . $website->subdomain . '.localhost' . $port;
    }
@endphp
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Dashboard Toko</h4>
            <p class="text-muted small">Overview performa toko <b>{{ $website->site_name }}</b>.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('store.home', ['subdomain' => $website->subdomain]) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-box-arrow-up-right"></i> Lihat Toko
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small fw-bold text-uppercase">Pendapatan Bulan Ini</span>
                        <div class="bg-success bg-opacity-10 text-success rounded p-1">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1">Rp {{ number_format($revenueThisMonth, 0, ',', '.') }}</h3>
                    
                    {{-- Logic Badge Growth --}}
                    @if($revenueGrowth > 0)
                        <small class="text-success fw-bold">
                            <i class="bi bi-arrow-up-short"></i> {{ number_format($revenueGrowth, 1) }}%
                        </small>
                        <span class="text-muted small">dari bulan lalu</span>
                    @elseif($revenueGrowth < 0)
                        <small class="text-danger fw-bold">
                            <i class="bi bi-arrow-down-short"></i> {{ number_format(abs($revenueGrowth), 1) }}%
                        </small>
                        <span class="text-muted small">dari bulan lalu</span>
                    @else
                        <span class="text-muted small">Sama seperti bulan lalu</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small fw-bold text-uppercase">Total Transaksi</span>
                        <div class="bg-primary bg-opacity-10 text-primary rounded p-1">
                            <i class="bi bi-cart-check"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold">{{ $totalOrder }}</h3>
                    <span class="text-muted small">Sejak toko dibuat</span>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small fw-bold text-uppercase">Perlu Diproses</span>
                        <div class="bg-warning bg-opacity-10 text-warning rounded p-1">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-warning">{{ $pendingOrders }}</h3>
                    <a href="{{ route('client.orders.index', $website->id) }}" class="small text-decoration-none stretched-link">Lihat Pesanan &rarr;</a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small fw-bold text-uppercase">Total Produk</span>
                        <div class="bg-info bg-opacity-10 text-info rounded p-1">
                            <i class="bi bi-box-seam"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold">{{ $totalProduk }}</h3>
                    <span class="text-muted small">Item aktif dijual</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">Tren Penjualan (30 Hari Terakhir)</h6>
                </div>
                <div class="card-body">
                    <div id="salesChart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">Produk Terlaris</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0" >
                            <thead class="bg-light small text-muted">
                                <tr>
                                    <th class="ps-3">Produk</th>
                                    <th class="text-end pe-3">Terjual</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topProducts as $item)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold text-truncate" style="max-width: 180px;">{{ $item->product_name }}</div>
                                            <small class="text-success">Rp {{ number_format($item->total_revenue) }}</small>
                                        </td>
                                        <td class="text-end pe-3 fw-bold">
                                            {{ $item->total_qty }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center py-4 text-muted small">
                                            Belum ada data penjualan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white py-3 border-bottom border-danger border-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-danger">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Stok Menipis
                        </h6>
                        <a href="{{ route('client.products.index', $website->id) }}" class="text-decoration-none small">Lihat Semua</a>
                    </div>
            </div>
            <div class="card-body p-0" style="max-height: 100px; overflow-y: auto;">
                <ul class="list-group list-group-flush">
                    @forelse($lowStockProducts as $product)
                        <li class="list-group-item d-flex justify-content-between align-items-center"  
                        onmouseover="this.style.filter='brightness(95%)'" onmouseout="this.style.filter='brightness(100%)'" style="cursor: pointer;">
                            <div class="d-flex align-items-center gap-2" onclick="window.location.href='{{ route('client.products.edit', [$website->id, $product->id]) }}#stok_barang'" >
                                @if($product->image)
                                    <img src="{{ asset('storage/'.$product->product_image) }}" width="32" height="32" class="rounded object-fit-cover">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:32px; height:32px;">
                                        <i class="bi bi-box small"></i>
                                    </div>
                                @endif
                                <div class="small fw-bold text-truncate" style="max-width: 150px;">
                                    {{ $product->name }}
                                </div>
                            </div>
                            <span class="badge {{ $product->stock == 0 ? 'bg-danger' : 'bg-warning text-dark' }} rounded-pill">
                                Sisa: {{ $product->stock }}
                            </span>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted small py-3">
                            <i class="bi bi-check-circle text-success mb-1 d-block"></i>
                            Stok aman terkendali.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
                </div>
                
            </div>
            
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">Transaksi Terbaru</h6>
            <a href="{{ route('client.orders.index', $website->id) }}" class="btn btn-sm btn-light border">Lihat Semua</a>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light small text-muted">
                    <tr>
                        <th class="ps-4">No. Invoice</th>
                        <th>Pelanggan</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Tanggal</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                    <tr>
                        <td class="ps-4 fw-bold text-primary">#{{ $order->order_number }}</td>
                        <td>
                            <div class="fw-bold">{{ $order->customer_name }}</div>
                            <small class="text-muted">{{ $order->customer_whatsapp }}</small>
                        </td>
                        <td>
                            @if($order->status == 'paid')
                                <span class="badge bg-success bg-opacity-10 text-success px-3">Lunas</span>
                            @elseif($order->status == 'pending')
                                <span class="badge bg-warning bg-opacity-10 text-warning px-3">Pending</span>
                            @else
                                <span class="badge bg-secondary px-3">{{ $order->status }}</span>
                            @endif
                        </td>
                        <td class="fw-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        <td class="text-muted small">{{ $order->created_at->format('d M Y H:i') }}</td>
                        <td class="text-end pe-4">
                            <a href="{{ route('client.orders.show', [$website->id, $order->id]) }}" class="btn btn-sm btn-light border">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                            Belum ada transaksi masuk.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Data dari Controller
        var options = {
            series: [{
                name: 'Penjualan',
                data: @json($chartValues) // Array Angka [100000, 250000, ...]
            }],
            chart: {
                height: 350,
                type: 'area', // Tipe Area lebih cantik daripada Line biasa
                toolbar: { show: false },
                fontFamily: 'Segoe UI, sans-serif'
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 }, // Garis halus melengkung
            xaxis: {
                categories: @json($chartLabels), // Array Tanggal ['01 Feb', '02 Feb', ...]
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return "Rp " + new Intl.NumberFormat('id-ID').format(value);
                    }
                }
            },
            colors: ['#0d6efd'], // Warna Biru Primary
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return "Rp " + new Intl.NumberFormat('id-ID').format(val)
                    }
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#salesChart"), options);
        chart.render();
    });
</script>
@endsection