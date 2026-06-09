@extends('layouts.client')
@section('title', 'Laporan Keuangan')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Laporan Keuangan</h4>
            <p class="text-muted m-0">Ringkasan performa penjualan toko Anda.</p>
        </div>
        <form action="{{ route('client.reports.index', $website->id) }}" method="GET" class="d-flex gap-2">
            <select name="month" class="form-select form-select-sm">
                @for($i=1; $i<=12; $i++)
                    <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $i)->format('F') }}</option>
                @endfor
            </select>
            <select name="year" class="form-select form-select-sm">
                @for($y=date('Y'); $y>=2024; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i></button>
        </form>
    </div>

    {{-- BARIS KARTU STATISTIK --}}
    <div class="row mb-4">
        <div class="col-md-6"><div class="card p-3 border-0 shadow-sm"><div class="small text-muted">Pendapatan</div><h3 class="fw-bold text-primary">Rp {{ number_format($grandTotal, 0, ',', '.') }}</h3></div></div>
        <div class="col-md-6"><div class="card p-3 border-0 shadow-sm"><div class="small text-muted">Total Transaksi</div><h3 class="fw-bold">{{ $totalTrx }} Order</h3></div></div>
    </div>

   {{-- GRAFIK TREN & PRODUK TERLARIS --}} 
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body"> {{-- Added card-body to provide proper padding inside the card --}}
        <div class="row g-4"> {{-- Added row class and g-4 for spacing between columns --}}
            
            {{-- LEFT COLUMN: GRAFIK TREN --}} 
            <div class="col-lg-8"> 
                <canvas id="revenueChart" height="400"></canvas> 
            </div> 

            {{-- RIGHT COLUMN: PRODUK TERLARIS --}} 
            <div class="col-lg-4"> 
                <div class="card border-0 shadow-sm h-100"> 
                    <div class="card-header bg-white py-3 fw-bold"> 
                        <i class="bi bi-trophy text-warning me-2"></i>5 Produk Terlaris (Bulan Ini) 
                    </div> 
                    <div class="list-group list-group-flush"> 
                        @forelse($topProducts as $index => $item) 
                            <div class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center"> 
                                <div class="d-flex align-items-center gap-3"> 
                                    <h5 class="m-0 text-muted">#{{ $index + 1 }}</h5> 
                                    <div> 
                                        <h6 class="mb-1 fw-bold text-truncate" style="max-width: 180px;">{{ $item->product_name }}</h6> 
                                        <small class="text-muted">{{ $item->total_qty }} Unit Terjual</small> 
                                    </div> 
                                </div> 
                                <div class="text-end"> 
                                    <span class="fw-bold d-block" style="font-size: 0.9rem;">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</span> 
                                </div> 
                            </div> 
                        @empty 
                            <div class="list-group-item text-center text-muted py-5"> 
                                Belum ada produk yang terjual bulan ini. 
                            </div> 
                        @endforelse 
                    </div> 
                </div> 
            </div>

        </div> {{-- End of row --}}
    </div> {{-- End of card-body --}}
</div>

{{-- AREA TABEL BAWAH --}}
    <div class="row g-4">
        
        {{-- TABEL KIRI: RINCIAN HARIAN --}}
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 fw-bold d-flex justify-content-between align-items-center">
                    Rincian Pendapatan Harian
                    {{-- 🚨 FIX: Hubungkan tombol ekspor dengan parameter filter bulan & tahun --}}
                    <a href="{{ route('client.reports.export', ['website' => $website->id, 'month' => $month, 'year' => $year]) }}" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i>Ekspor CSV
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr class="bg-light">
                            <th class="ps-4">Tanggal</th>
                            <th>Order</th>
                            <th class="text-end pe-4">Pendapatan</th>
                        </tr></thead>
                        <tbody>
                            @forelse($reports as $row)
                            <tr>
                                <td class="ps-4">{{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}</td>
                                <td>{{ $row->total_orders }} x</td>
                                <td class="text-end pe-4 fw-bold text-success">+ Rp {{ number_format($row->revenue, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-5 text-muted">Tidak ada data transaksi bulan ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: {!! json_encode($chartValues) !!},
                borderColor: '#0d6efd',
                tension: 0.4,
                fill: true,
                backgroundColor: 'rgba(13, 110, 253, 0.1)'
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
</script>
@endsection