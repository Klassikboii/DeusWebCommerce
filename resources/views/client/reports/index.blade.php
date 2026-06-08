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

    {{-- GRAFIK TREN --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <canvas id="revenueChart" height="80"></canvas>
        </div>
    </div>

    {{-- TABEL RINCIAN --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 fw-bold d-flex justify-content-between">
            Rincian Harian
            <a href="#" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Ekspor Excel</a>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr class="bg-light"><th>Tanggal</th><th>Order</th><th class="text-end">Pendapatan</th></tr></thead>
                <tbody>
                    @forelse($reports as $row)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($row->date)->format('d F Y') }}</td>
                        <td>{{ $row->total_orders }} Transaksi</td>
                        <td class="text-end fw-bold text-success">+ Rp {{ number_format($row->revenue, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center py-4">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
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
        }
    });
</script>
@endsection