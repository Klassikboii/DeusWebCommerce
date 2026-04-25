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
                                <span class="position-absolute top-0 end-0 p-2 text-success"><i class="bi bi-check-circle-fill fs-6"></i></span>
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
                                <span class="position-absolute top-0 end-0 p-2 text-success"><i class="bi bi-check-circle-fill fs-6"></i></span>
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
                                <span class="position-absolute top-0 end-0 p-2 text-success"><i class="bi bi-check-circle-fill fs-6"></i></span>
                            @endif
                            <h6 class="fw-bold {{ $setupStatus['payment'] ? 'text-muted text-decoration-line-through' : 'text-dark' }}">3. Pembayaran</h6>
                            <p class="small text-muted mb-3">Hubungkan Pivot agar bisa menerima transfer.</p>
                            {{-- Tombol Midtrans --}}
                            @if(!$setupStatus['payment'])
                                <div class="d-flex gap-2">
                                    <a href="{{ route('client.settings.index', $website->id) }}#pivot-section" class="btn btn-sm btn-outline-primary flex-grow-1">Hubungkan</a>
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
                                <span class="position-absolute top-0 end-0 p-2 text-success"><i class="bi bi-check-circle-fill fs-6"></i></span>
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
    $mainDomain = parse_url(config('app.url'), PHP_URL_HOST);

    // Tentukan protocol berdasarkan environment
    $scheme = app()->environment('local') ? 'http://' : 'https://';

    // Port hanya untuk local
    $port = app()->environment('local') ? ':8000' : '';

    // Prioritas: custom domain > subdomain
    if (!empty($website->custom_domain)) {
        $storeUrl = $scheme . $website->custom_domain . $port;
    } else {
        $storeUrl = $scheme . $website->subdomain . '.' . $mainDomain . $port;
    }
@endphp
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Dashboard Toko</h4>
            <p class="text-muted small">Overview performa toko <b>{{ $website->site_name }}</b>.</p>
        </div>
        <div class="d-flex gap-2">
            @if($website->hasFeature('has_custom_dashboard'))
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCustomizeDashboard">
                    <i class="bi bi-gear-fill"></i> Atur Tampilan
                </button>
            @endif
            <a href="{{ $storeUrl }}"" target="_blank" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-box-arrow-up-right"></i> Lihat Toko
            </a>
        </div>
    </div>
    {{-- BARIS 1: STATISTIK ANGKA --}}
    @php
        // Hitung berapa banyak widget yang menyala (bernilai true)
        $activeWidgetsCount = collect($preferences)->filter()->count();
    @endphp

    @php
    // 1. Definisikan daftar widget statistik di baris ini
    $topStats = [
        'show_stat_revenue'      => 'stat_revenue',
        'show_stat_transactions' => 'stat_transactions',
        'show_stat_pending'      => 'stat_pending',
        'show_stat_products'     => 'stat_products',
    ];

    // 2. Filter mana yang benar-benar aktif (preferences == true)
    $activeStats = collect($topStats)->filter(function($value, $key) use ($preferences) {
        return $preferences[$key] ?? true;
    });

    $count = $activeStats->count();

    // 3. Tentukan lebar kolom secara otomatis
    // Jika 1 widget = col-12, 2 = col-6, 3 = col-4, 4 = col-3
    $colClass = match($count) {
        1 => 'col-12',
        2 => 'col-md-6',
        3 => 'col-md-4',
        default => 'col-md-3',
    };
@endphp
        @if($activeWidgetsCount == 0)
            {{-- TAMPILAN JIKA SEMUA WIDGET DIMATIKAN --}}
            <div class="text-center py-5 my-5">
                <i class="bi bi-layout-text-window fs-1 text-muted opacity-50 mb-3 d-block"></i>
                <h4 class="fw-bold text-dark">Dashboard Anda Kosong</h4>
                <p class="text-muted">Anda telah menyembunyikan semua widget. Silakan atur kembali tampilan dashboard Anda.</p>
                <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#modalCustomizeDashboard">
                    <i class="bi bi-gear-fill me-1"></i> Atur Tampilan Sekarang
                </button>
            </div>
        @else
        @if($count > 0)
    <div class="row g-3 mb-4">
        @if($preferences['show_stat_revenue'] ?? true)
        <div class="{{ $colClass }}">
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
        @endif
        @if($preferences['show_stat_transactions'] ?? true)
        <div class="{{ $colClass }}">
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
        @endif
        @if($preferences['show_stat_pending'] ?? true)
        <div class="{{ $colClass }}">
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
         @endif
         @if($preferences['show_stat_products'] ?? true)

       <div class="{{ $colClass }}">
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
        @endif
    </div>
    

 @endif
 <!-- ------------------------------------------------------------------------------------------------- -->
  {{-- BARIS 2: GRAFIK & PRODUK TERLARIS --}}
    @php
        $isChartActive = $preferences['show_sales_chart'] ?? true;
        $isTopProductsActive = $preferences['show_top_products'] ?? true;
        $isLowStockActive = $preferences['show_low_stock'] ?? true;
        $isRightColumnActive = $isTopProductsActive || $isLowStockActive;
    @endphp
{{-- ==================================================== --}}
    {{-- BARIS 2: GRAFIK TREN (FULL WIDTH) --}}
    {{-- ==================================================== --}}
    @if($isChartActive)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-graph-up text-primary me-2"></i>Tren Penjualan (30 Hari Terakhir)</h6>
                </div>
                <div class="card-body pt-0">
                    {{-- Kita kurangi min-height ke 250px agar tidak terlalu memakan tempat vertikal --}}
                    <div id="salesChart" style="min-height: 250px;"></div>
                </div>
            </div>
        </div>
    </div>
    @endif
{{-- ==================================================== --}}
    {{-- BARIS 3: PRODUK TERLARIS & STOK MENIPIS (SEJAJAR / DINAMIS) --}}
    {{-- ==================================================== --}}
    @if($isRightColumnActive)
    @php
        // Logika kolom: Jika dua-duanya nyala jadi col-6, jika salah satu saja jadi col-12
        $isFullWidth = !($isTopProductsActive && $isLowStockActive);
        $subColClass = $isFullWidth ? 'col-12' : 'col-md-6';
    @endphp
    
    <div class="row g-4 mb-4">
        {{-- WIDGET: PRODUK TERLARIS --}}
        @if($isTopProductsActive)
        <div class="{{ $subColClass }}">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 text-success border-0">
                    <i class="bi bi-currency-dollar me-1"></i><b class="{{ $isFullWidth ? 'fs-6' : '' }}">Produk Terlaris</b>
                </div>
                <div class="card-body p-0">
                    {{-- Jika full width, kita buat listnya sedikit lebih tinggi agar proporsional --}}
                    <div class="table-responsive" style="max-height: {{ $isFullWidth ? '350px' : '250px' }}; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted {{ $isFullWidth ? '' : 'small' }}">
                                <tr>
                                    <th class="ps-4 border-0">Produk</th>
                                    <th class="text-end pe-4 border-0">Terjual</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topProducts as $item)
                                    <tr>
                                        <td class="ps-4 border-0" style="width: 75%;">
                                            {{-- max-width dinamis: 700px jika lebar, 250px jika sempit --}}
                                            <div class="fw-bold text-truncate {{ $isFullWidth ? 'fs-6 mb-1' : '' }}" style="max-width: {{ $isFullWidth ? '700px' : '250px' }};">{{ $item->product_name }}</div>
                                            <span class="text-success fw-bold {{ $isFullWidth ? 'fs-6' : 'small' }}">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="text-end pe-4 fw-bold border-0 {{ $isFullWidth ? 'fs-4' : '' }}">{{ $item->total_qty }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center py-4 text-muted">Belum ada data.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- WIDGET: STOK MENIPIS --}}
        @if($isLowStockActive)
        <div class="{{ $subColClass }}">
            <div class="card border-0 shadow-sm h-100 border-top border-danger border-3">
                <div class="card-header bg-white py-3 border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-danger {{ $isFullWidth ? 'fs-6' : '' }}"><i class="bi bi-exclamation-triangle-fill me-1"></i> Stok Menipis</h6>
                        <a href="{{ route('client.products.index', $website->id) }}" class="text-decoration-none {{ $isFullWidth ? '' : 'small' }}">Lihat Semua</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush" style="max-height: {{ $isFullWidth ? '350px' : '250px' }}; overflow-y: auto;">
                        @forelse($lowStockProducts as $product)
                            {{-- Tambahkan padding vertikal (py-4) jika full width agar baris terasa lega --}}
                            <li class="list-group-item d-flex justify-content-between align-items-center px-4 border-0 border-bottom {{ $isFullWidth ? 'py-4' : 'py-3' }}">
                                <span class="fw-bold text-truncate {{ $isFullWidth ? 'fs-6' : 'small' }}" style="max-width: {{ $isFullWidth ? '700px' : '250px' }};">{{ $product->name }}</span>
                                
                                {{-- Membesarkan ukuran badge pelabelan stok jika sedang lebar --}}
                                <span class="badge {{ $product->stock == 0 ? 'bg-danger' : 'bg-warning text-dark' }} rounded-pill {{ $isFullWidth ? 'fs-6 px-3 py-2' : '' }}">
                                    Sisa: {{ $product->stock }}
                                </span>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted small py-4 border-0">Stok aman terkendali.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif
    {{-- BARIS 4: TRANSAKSI TERBARU --}}
    @if($preferences['show_recent_orders'] ?? true)
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
    @endif

    {{-- BARIS 5: AI INSIGHTS (DIGABUNG) --}}
    @if($website->hasFeature('has_ai_insights'))
   
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                @if($preferences['show_rfm']?? true)
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="bi bi-people-fill text-success me-2"></i>Ringkasan AI: Segmen Pelanggan</h6>
                    <a href="{{ route('client.insights.index', $website->id) }}" class="btn btn-sm btn-outline-primary">Lihat Laporan Lengkap</a>
                </div>
                
                <div class="card-body">
                    <div class="row align-items-center">
                        
                        {{-- SISI KIRI: DONUT CHART KECIL --}}
                        <div class="col-md-4 text-center border-end">
                            @if(empty($rfmSummary))
                                <p class="text-muted small mt-4">Belum ada data analitik yang cukup.</p>
                            @else
                                <div style="position: relative; height: 160px; width: 100%; display: flex; justify-content: center;">
                                    <canvas id="dashboardRfmChart"></canvas>
                                </div>
                            @endif
                        </div>

                        {{-- SISI KANAN: ANGKA HIGHLIGHT (TINDAKAN) --}}
                        <div class="col-md-8">
                            <div class="row g-3 ps-md-3 mt-3 mt-md-0">
                                {{-- Insight 1: Champions --}}
                                <div class="col-sm-6">
                                    <div class="p-3 rounded h-100" style="background-color: #f8fff9; border: 1px solid #c3e6cb;">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="text-success fw-bold small">CHAMPIONS</span>
                                            <i class="bi bi-trophy-fill text-success fs-6"></i>
                                        </div>
                                        <h3 class="fw-bold text-success mb-1">{{ $championsCount }} <span class="fs-6 fw-normal text-muted">Orang</span></h3>
                                        <p class="text-muted small mb-0 lh-sm">Aset terbesar Anda. Jangan lupa sapa mereka!</p>
                                    </div>
                                </div>

                                {{-- Insight 2: At Risk --}}
                                <div class="col-sm-6">
                                    <div class="p-3 rounded h-100" style="background-color: #fffcf8; border: 1px solid #ffeeba;">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="text-warning text-dark fw-bold small">RAWAN KABUR (AT RISK)</span>
                                            <i class="bi bi-exclamation-triangle-fill text-warning fs-6"></i>
                                        </div>
                                        <h3 class="fw-bold text-warning mb-1">{{ $atRiskCount }} <span class="fs-6 fw-normal text-muted">Orang</span></h3>
                                        <p class="text-muted small mb-0 lh-sm">Dulu loyal, kini hilang. Kirim diskon untuk menarik mereka.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>{{-- ==================================================== --}}
        {{-- B. RADAR & BUNDLE: Berbagi Baris (Dinamis) --}}
        {{-- ==================================================== --}}
        @php
            $subAiKeys = ['show_ai_radar', 'show_bundles'];
            $activeSubAiCount = collect($subAiKeys)->filter(fn($k) => $preferences[$k] ?? true)->count();
            
            // Logika Pendeteksi: True jika hanya 1 widget AI yang menyala
            $isAiFullWidth = ($activeSubAiCount == 1);
            $subAiColClass = $isAiFullWidth ? 'col-12' : 'col-md-6';
        @endphp

        @if($activeSubAiCount > 0)
        <div class="row g-4 mb-4">
            
            {{-- WIDGET AI: RADAR INVENTARIS --}}
            @if($preferences['show_ai_radar'] ?? true)
            <div class="{{ $subAiColClass }}">
                <div class="card border-0 shadow-sm h-100 border-top border-warning border-3 d-flex flex-column">
                    <div class="card-header bg-white pt-3 pb-2 border-0">
                        <h6 class="fw-bold mb-0 text-warning {{ $isAiFullWidth ? 'fs-6' : '' }}">
                            <i class="bi bi-box-seam me-2"></i>Radar Inventaris
                        </h6>
                    </div>
                    
                    {{-- Tinggi dinamis: 400px jika lebar, 300px jika sempit --}}
                    <div class="card-body p-0 flex-grow-1" style="max-height: {{ $isAiFullWidth ? '400px' : '300px' }}; overflow-y: auto;">
                        @if(isset($attentionStocks) && $attentionStocks->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($attentionStocks as $item)
                                    {{-- Padding dinamis (p-4 vs p-3) --}}
                                    <div class="list-group-item d-flex justify-content-between align-items-center border-0 border-bottom {{ $isAiFullWidth ? 'p-4' : 'p-3' }}">
                                        <div>
                                            {{-- Lebar pemotongan teks dinamis (600px vs 200px) --}}
                                            <h6 class="mb-0 fw-bold text-truncate {{ $isAiFullWidth ? 'fs-6' : '' }}" style="max-width: {{ $isAiFullWidth ? '600px' : '200px' }};">{{ $item->name }}</h6>
                                            <span class="text-muted {{ $isAiFullWidth ? 'fs-6' : 'small' }}">Sisa {{ $item->stock }} unit • Laku {{ number_format($item->velocity, 1) }}/hari</span>
                                        </div>
                                        <div class="text-end">
                                            @if($item->stock_status == 'Critical')
                                                <span class="badge bg-danger mb-1 {{ $isAiFullWidth ? 'px-3 py-2 fs-6' : '' }}">Kritis ({{ $item->runway_days }} Hari)</span>
                                            @else
                                                <span class="badge bg-secondary mb-1 {{ $isAiFullWidth ? 'px-3 py-2 fs-6' : '' }}">Overstock</span>
                                            @endif
                                            <br>
                                            <a href="{{ route('client.products.insight', [$website->id, $item->id]) }}" class="btn btn-outline-primary mt-1 {{ $isAiFullWidth ? 'btn-sm px-3' : 'btn-sm py-0' }}" style="{{ $isAiFullWidth ? '' : 'font-size: 0.75rem;' }}">Analisis</a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted {{ $isAiFullWidth ? 'p-5' : 'p-4' }}">
                                <i class="bi bi-check-circle text-success mb-2 d-block {{ $isAiFullWidth ? 'fs-1' : 'fs-2' }}"></i>
                                <span class="{{ $isAiFullWidth ? 'fs-6' : 'small' }}">Gudang optimal! Tidak ada stok kritis maupun overstock.</span>
                            </div>
                        @endif
                    </div>
                    
                    {{-- Footer Ringkasan AI --}}
                    <div class="card-footer bg-light border-top p-3 mt-auto">
                        <div class="row g-2">
                            <div class="col-6">
                                {{-- Tombol membesar jika mode full width --}}
                                <a href="{{ route('client.products.index', [$website, 'stock_status' => 'Critical']) }}" class="btn btn-outline-danger w-100 d-flex justify-content-between align-items-center {{ $isAiFullWidth ? 'p-2' : 'btn-sm' }}">
                                    <span><i class="bi bi-exclamation-circle me-1"></i> Kritis</span>
                                    <span class="badge bg-danger rounded-pill">{{ $totalCritical ?? 0 }}</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('client.products.index', [$website, 'stock_status' => 'Overstock']) }}" class="btn btn-outline-secondary w-100 d-flex justify-content-between align-items-center {{ $isAiFullWidth ? 'p-2' : 'btn-sm' }}">
                                    <span><i class="bi bi-box-seam me-1"></i> Overstock</span>
                                    <span class="badge bg-secondary rounded-pill">{{ $totalOverstock ?? 0 }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- WIDGET AI: PELUANG BUNDLING --}}
            @if($preferences['show_bundles'] ?? true)
            <div class="{{ $subAiColClass }}">
                <div class="card border-0 shadow-sm h-100 border-top border-info border-3">
                    <div class="card-header bg-white pt-3 pb-2 border-0">
                        <h6 class="fw-bold mb-0 text-info {{ $isAiFullWidth ? 'fs-6' : '' }}">
                            <i class="bi bi-tags-fill me-2"></i>Peluang Bundle
                        </h6>
                    </div>
                    <div class="card-body {{ $isAiFullWidth ? 'p-4' : 'p-3' }}" style="max-height: {{ $isAiFullWidth ? '400px' : '300px' }}; overflow-y: auto;">
                        @if(isset($topBundles) && $topBundles->count() > 0)
                            <ul class="list-unstyled mb-0">
                                @foreach($topBundles as $bundle)
                                    {{-- Spasi dan ukuran font dinamis antar list bundle --}}
                                    <li class="border-bottom {{ $isAiFullWidth ? 'mb-3 pb-3 fs-6' : 'mb-2 pb-2 small' }}">
                                        <strong>{{ $bundle->product->name ?? '?' }}</strong> 
                                        <span class="text-muted mx-1">+</span> 
                                        <strong>{{ $bundle->recommendedProduct->name ?? '?' }}</strong> 
                                        
                                        <span class="text-success ms-2 fw-bold {{ $isAiFullWidth ? 'fs-6' : '' }}">
                                            <i class="bi bi-arrow-up-right"></i> {{ number_format($bundle->lift, 1) }}x
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="text-center text-muted {{ $isAiFullWidth ? 'p-5 fs-6' : 'p-2 small' }}">
                                Data belum cukup untuk rekomendasi bundle.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

        </div>
        @endif
        @endif
@endif
    @if(!empty($rfmSummary))
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const segmentData = @json($rfmSummary);
            const labels = Object.keys(segmentData);
            const dataValues = Object.values(segmentData);

            const bgColors = labels.map(label => {
                if(label.includes('Champion')) return '#198754'; 
                if(label.includes('Loyal')) return '#0dcaf0';    
                if(label.includes('New')) return '#0d6efd';      
                if(label.includes('Risk')) return '#ffc107';     
                if(label.includes('Hibernating')) return '#6c757d'; 
                return '#212529'; 
            });

            new Chart(document.getElementById('dashboardRfmChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: dataValues,
                        backgroundColor: bgColors,
                        borderWidth: 0,
                        cutout: '70%' // Membuat lubang donat lebih besar agar minimalis
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }, // Matikan legend agar rapi di dashboard
                        tooltip: {
                            callbacks: {
                                label: function(context) { return ' ' + context.label + ': ' + context.raw + ' Orang'; }
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endif
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
{{-- 
<div class="modal fade" id="modalPanduanMidtrans" tabindex="-1" aria-labelledby="modalMidtransLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
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
                    
                    {{-- TAMBAHAN LANGKAH WEBHOOK --}}
                    <li>Masih di menu Settings, pindah ke bagian <strong>Configuration</strong> (Konfigurasi).</li>
                    <li>Temukan kolom <strong>Payment Notification URL</strong>, lalu masukkan (Paste) alamat web khusus di bawah ini:
                        <div class="d-flex align-items-center mt-2 mb-3 p-2 bg-light border rounded">
                            <code id="webhookUrlText" class="flex-grow-1 text-dark fs-6">{{ url('/api/webhook/midtrans') }}</code>
                            <button type="button" class="btn btn-sm btn-outline-primary ms-2 shadow-sm" onclick="copyWebhookUrl()">
                                <i class="bi bi-clipboard"></i> Salin URL
                            </button>
                        </div>
                    </li>
                    
                    <li>Kembali ke halaman Dashboard ini, klik tombol "Hubungkan" dan <em>Paste</em> kunci-kunci yang sudah disalin tadi ke form yang tersedia.</li>
                    <li>Selesai! Uang dari pembeli akan langsung masuk ke akun Midtrans Anda dan status pesanan akan ter-<em>update</em> secara otomatis.</li>
                </ol>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Saya Mengerti</button>
            </div>
        </div>
    </div>
</div>
<script>
    function copyWebhookUrl() {
        var copyText = document.getElementById("webhookUrlText").innerText;
        
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(copyText).then(function() {
                alert("URL Webhook berhasil disalin! Silakan paste di dashboard Midtrans Anda.");
            });
        } else {
            // Cara klasik penembus HTTP
            let textArea = document.createElement("textarea");
            textArea.value = copyText;
            textArea.style.position = "absolute"; 
            textArea.style.left = "-999999px";
            
            // 🚨 PERBAIKAN: Tempelkan ke dalam modal, bukan ke document.body
            var modalBody = document.querySelector('#modalPanduanMidtrans .modal-body');
            if(modalBody) {
                modalBody.appendChild(textArea);
            } else {
                document.body.appendChild(textArea);
            }
            
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                alert("URL Webhook berhasil disalin!");
            } catch (err) {
                alert("Gagal menyalin. Silakan block dan copy manual teksnya.");
            }
            
            textArea.remove();
        }
    }
</script> --}}
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
<div class="modal fade" id="modalCustomizeDashboard" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg"> {{-- Pakai modal-lg agar lebar --}}
        <div class="modal-content border-0 shadow">
            <form action="{{ route('client.dashboard.save_preferences', $website->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold"><i class="bi bi-sliders me-2"></i> Sesuaikan Tampilan Dashboard</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-4">Pilih dan kelompokkan informasi yang paling penting bagi bisnis Anda untuk ditampilkan di halaman depan.</p>
                    <div class="row g-4">
                        {{-- Kategori 1: Ringkasan Utama --}}
                        <div class="col-md-6">
                            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Ringkasan Angka</h6>

                            <label class="form-check form-switch mb-2 d-flex align-items-center" style="cursor: pointer;">
                                <input class="form-check-input me-2" type="checkbox" name="show_stat_revenue" value="1" {{ ($preferences['show_stat_revenue'] ?? true) ? 'checked' : '' }}>
                                <i class="bi bi-cash-stack text-success me-2"></i>
                                <span>Pendapatan Bulan Ini</span>
                            </label>

                            <label class="form-check form-switch mb-2 d-flex align-items-center" style="cursor: pointer;">
                                <input class="form-check-input me-2" type="checkbox" name="show_stat_transactions" value="1" {{ ($preferences['show_stat_transactions'] ?? true) ? 'checked' : '' }}>
                                <i class="bi bi-cart-check-fill text-primary me-2"></i>
                                <span>Total Transaksi</span>
                            </label>

                            <label class="form-check form-switch mb-2 d-flex align-items-center" style="cursor: pointer;">
                                <input class="form-check-input me-2" type="checkbox" name="show_stat_pending" value="1" {{ ($preferences['show_stat_pending'] ?? true) ? 'checked' : '' }}>
                                <i class="bi bi-hourglass-split text-warning me-2"></i>
                                <span>Pesanan Perlu Diproses</span>
                            </label>

                            <label class="form-check form-switch mb-2 d-flex align-items-center" style="cursor: pointer;">
                                <input class="form-check-input me-2" type="checkbox" name="show_stat_products" value="1" {{ ($preferences['show_stat_products'] ?? true) ? 'checked' : '' }}>
                                <i class="bi bi-box-seam-fill text-info me-2"></i>
                                <span>Total Produk Aktif</span>
                            </label>
                        </div>

                        {{-- Kategori 2: Analitik Standar --}}
                        <div class="col-md-6">
                            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Analitik & Aktivitas</h6>

                            <label class="form-check form-switch mb-2 d-flex align-items-center" style="cursor: pointer;">
                                <input class="form-check-input me-2" type="checkbox" name="show_sales_chart" value="1" {{ ($preferences['show_sales_chart'] ?? true) ? 'checked' : '' }}>
                                <i class="bi bi-graph-up text-primary me-2"></i>
                                <span>Grafik Tren Penjualan (30 Hari)</span>
                            </label>

                            <label class="form-check form-switch mb-2 d-flex align-items-center" style="cursor: pointer;">
                                <input class="form-check-input me-2" type="checkbox" name="show_top_products" value="1" {{ ($preferences['show_top_products'] ?? true) ? 'checked' : '' }}>
                                <i class="bi bi-currency-dollar text-success me-2"></i>
                                <span>Tabel Produk Terlaris</span>
                            </label>

                            <label class="form-check form-switch mb-2 d-flex align-items-center" style="cursor: pointer;">
                                <input class="form-check-input me-2" type="checkbox" name="show_low_stock" value="1" {{ ($preferences['show_low_stock'] ?? true) ? 'checked' : '' }}>
                                <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                                <span>Peringatan Stok Menipis</span>
                            </label>

                            <label class="form-check form-switch mb-2 d-flex align-items-center" style="cursor: pointer;">
                                <input class="form-check-input me-2" type="checkbox" name="show_recent_orders" value="1" {{ ($preferences['show_recent_orders'] ?? true) ? 'checked' : '' }}>
                                <i class="bi bi-arrow-left-right text-info me-2"></i>
                                <span>Daftar Transaksi Terbaru</span>
                            </label>
                        </div>

                        {{-- Kategori 3: AI Insights --}}
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-warning mb-3 border-bottom pb-2">
                                <i class="bi bi-stars me-1"></i> Wawasan Kecerdasan Buatan (AI)
                            </h6>

                            <div class="row">
                                <div class="col-md-6">

                                    <label class="form-check form-switch mb-2 d-flex align-items-center" style="cursor: pointer;">
                                        <input class="form-check-input me-2" type="checkbox" name="show_rfm" value="1" {{ ($preferences['show_rfm'] ?? true) ? 'checked' : '' }}>
                                        <i class="bi bi-people-fill text-success me-2"></i>
                                        <span>Segmen Pelanggan (RFM)</span>
                                    </label>

                                    <label class="form-check form-switch mb-2 d-flex align-items-center" style="cursor: pointer;">
                                        <input class="form-check-input me-2" type="checkbox" name="show_ai_radar" value="1" {{ ($preferences['show_ai_radar'] ?? true) ? 'checked' : '' }}>
                                        <i class="bi bi-box-seam-fill text-warning me-2"></i>
                                        <span>Radar Inventaris (Kritis/Overstock)</span>
                                    </label>

                                </div>

                                <div class="col-md-6">

                                    <label class="form-check form-switch mb-2 d-flex align-items-center" style="cursor: pointer;">
                                        <input class="form-check-input me-2" type="checkbox" name="show_bundles" value="1" {{ ($preferences['show_bundles'] ?? true) ? 'checked' : '' }}>
                                        <i class="bi bi-tags-fill text-info me-2"></i>
                                        <span>Peluang Bundling Produk</span>
                                    </label>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan Tampilan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection