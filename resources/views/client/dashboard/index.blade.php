@extends('layouts.client')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

{{-- ==================================================== --}}
        {{-- WIDGET ONBOARDING CHECKLIST (Tampil Jika Belum 100%) --}}
        {{-- ==================================================== --}}
        @if($setupProgress < 100)
        <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(to right, #ffffff, #f8faff); border-left: 4px solid #0d6efd !important;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-end mb-3">
                    <div>
                        <h5 class="fw-bold text-primary mb-1">Persiapan Toko Anda ({{ round($setupProgress) }}%)</h5>
                        <p class="text-muted small mb-0">Selesaikan langkah-langkah di bawah ini agar toko Anda siap menerima pembeli.</p>
                    </div>
                </div>

                <div class="progress mb-4" style="height: 8px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: {{ $setupProgress }}%"></div>
                </div>

                <div class="row g-3">
                    {{-- Langkah 1: Profil --}}
                    <div class="col-md-6 col-lg-3">
                        <div class="p-3 border rounded {{ $setupStatus['profile'] ? 'bg-light border-success' : 'bg-white border-primary border-opacity-50' }} h-100 position-relative">
                            @if($setupStatus['profile'])
                                <span class="position-absolute top-0 end-0 p-2 text-success"><i class="bi bi-check-circle-fill fs-5"></i></span>
                            @endif
                            <h6 class="fw-bold {{ $setupStatus['profile'] ? 'text-muted text-decoration-line-through' : 'text-dark' }}">1. Profil & Alamat</h6>
                            <p class="small text-muted mb-3">Atur alamat toko agar ongkir dapat dihitung otomatis.</p>
                            @if(!$setupStatus['profile'])
                                <a href="{{ route('client.settings.index', $website->id) }}" class="btn btn-sm btn-outline-primary w-100">Atur Profil</a>
                            @endif
                        </div>
                    </div>

                    {{-- Langkah 2: Produk --}}
                    <div class="col-md-6 col-lg-3">
                        <div class="p-3 border rounded {{ $setupStatus['product'] ? 'bg-light border-success' : 'bg-white border-primary border-opacity-50' }} h-100 position-relative">
                            @if($setupStatus['product'])
                                <span class="position-absolute top-0 end-0 p-2 text-success"><i class="bi bi-check-circle-fill fs-5"></i></span>
                            @endif
                            <h6 class="fw-bold {{ $setupStatus['product'] ? 'text-muted text-decoration-line-through' : 'text-dark' }}">2. Tambah Produk</h6>
                            <p class="small text-muted mb-3">Upload produk pertama yang ingin Anda jual.</p>
                            @if(!$setupStatus['product'])
                                <a href="{{ route('client.products.create', $website->id) }}" class="btn btn-sm btn-outline-primary w-100">Tambah Produk</a>
                            @endif
                        </div>
                    </div>

                    {{-- Langkah 3: Midtrans --}}
                    <div class="col-md-6 col-lg-3">
                        <div class="p-3 border rounded {{ $setupStatus['payment'] ? 'bg-light border-success' : 'bg-white border-primary border-opacity-50' }} h-100 position-relative">
                            @if($setupStatus['payment'])
                                <span class="position-absolute top-0 end-0 p-2 text-success"><i class="bi bi-check-circle-fill fs-5"></i></span>
                            @endif
                            <h6 class="fw-bold {{ $setupStatus['payment'] ? 'text-muted text-decoration-line-through' : 'text-dark' }}">3. Pembayaran</h6>
                            <p class="small text-muted mb-3">Hubungkan Midtrans agar bisa menerima transfer.</p>
                            {{-- Tombol Midtrans --}}
                            @if(!$setupStatus['payment'])
                                <div class="d-flex gap-2">
                                    <a href="{{ route('client.settings.index', $website->id) }}#midtrans-section" class="btn btn-sm btn-outline-primary flex-grow-1">Hubungkan</a>
                                    <button type="button" class="btn btn-sm btn-light border text-muted" data-bs-toggle="modal" data-bs-target="#modalPanduanMidtrans" title="Cara Setup">
                                        <i class="bi bi-question-circle"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Langkah 4: Accurate --}}
                    <div class="col-md-6 col-lg-3">
                        <div class="p-3 border rounded {{ $setupStatus['accurate'] ? 'bg-light border-success' : 'bg-white border-primary border-opacity-50' }} h-100 position-relative">
                            @if($setupStatus['accurate'])
                                <span class="position-absolute top-0 end-0 p-2 text-success"><i class="bi bi-check-circle-fill fs-5"></i></span>
                            @endif
                            <h6 class="fw-bold {{ $setupStatus['accurate'] ? 'text-muted text-decoration-line-through' : 'text-dark' }}">4. Pembukuan</h6>
                            <p class="small text-muted mb-3">Sinkronisasi dengan Accurate Online (Opsional).</p>
                            {{-- Tombol Accurate --}}
                            @if(!$setupStatus['accurate'])
                                <div class="d-flex gap-2">
                                    <a href="{{ route('client.settings.index', $website->id) }}#accurate-section" class="btn btn-sm btn-outline-secondary flex-grow-1">Hubungkan</a>
                                    <button type="button" class="btn btn-sm btn-light border text-muted" data-bs-toggle="modal" data-bs-target="#modalPanduanAccurate" title="Cara Setup">
                                        <i class="bi bi-question-circle"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        {{-- ==================================================== --}}
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
                                @if($product->product_image)
                                            <img src="{{ asset('storage/'.$product->product_image) }}" width="40" class="rounded">
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

<div class="modal fade" id="modalPanduanMidtrans" tabindex="-1" aria-labelledby="modalMidtransLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title fw-bold" id="modalMidtransLabel"><i class="bi bi-credit-card me-2"></i>Panduan Setup Midtrans</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <ol class="mb-0 text-muted" style="line-height: 1.8;">
                    <li>Daftar atau Login ke akun <a href="https://dashboard.midtrans.com/" target="_blank" class="fw-bold text-primary text-decoration-none">Dashboard Midtrans</a> Anda.</li>
                    <li>Di menu sebelah kiri, masuk ke bagian <strong>Settings</strong> (Pengaturan) ➔ <strong>Access Keys</strong>.</li>
                    <li>Anda akan melihat <strong>Client Key</strong> dan <strong>Server Key</strong>. Salin (Copy) kedua kunci tersebut.</li>
                    <li>Kembali ke Dashboard ini, klik tombol "Hubungkan" dan <em>Paste</em> kunci tersebut ke form yang tersedia.</li>
                    <li>Uang dari pembeli akan langsung masuk ke akun Midtrans Anda secara otomatis!</li>
                </ol>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mengerti</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalPanduanAccurate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h6 class="modal-title fw-bold"><i class="bi bi-box-seam me-2"></i>Panduan Setup Accurate Online</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-warning small mb-3">
                    <strong>⚠️ PENTING SEBELUM MENGHUBUNGKAN:</strong><br>
                    Pastikan Anda sudah membuat 2 item dengan tipe <strong>"JASA" (Service)</strong> di Accurate Online Anda dengan Nomor/SKU berikut:<br>
                    1. SKU: <strong>ONGKIR</strong> (Untuk mencatat biaya pengiriman)<br>
                    2. SKU: <strong>DISKON</strong> (Jika Anda berencana menggunakan fitur kupon/promo)
                </div>
                <ol class="mb-0 text-muted small" style="line-height: 1.8;">
                    <li>Klik tombol <strong>"Hubungkan"</strong> di sebelah kotak ini.</li>
                    <li>Anda akan diarahkan ke halaman Login resmi Accurate Online.</li>
                    <li>Berikan izin pada aplikasi Webcommerce ini untuk mengakses data Anda.</li>
                    <li>Setelah kembali, <strong>Pilih Database Accurate</strong> yang ingin dihubungkan.</li>
                    <li>Selesai! Anda kini bisa menarik produk langsung dari Accurate.</li>
                </ol>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mengerti</button>
            </div>
        </div>
    </div>
</div>
@endsection