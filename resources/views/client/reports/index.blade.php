@extends('layouts.client')

@section('title', 'Laporan Penjualan')

@section('content')
<div class="container-fluid p-0">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Laporan Keuangan</h4>
            <p class="text-muted m-0">Ringkasan pendapatan toko Anda.</p>
        </div>
        
        <form action="{{ route('client.reports.index', $website->id) }}" method="GET" class="d-flex gap-2">
            <select name="month" class="form-select form-select-sm" style="width: 120px;">
                @for($i=1; $i<=12; $i++)
                    <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                        {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                    </option>
                @endfor
            </select>
            <select name="year" class="form-select form-select-sm" style="width: 100px;">
                @for($y=date('Y'); $y>=2024; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
        </form>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body">
                    <div class="small opacity-75">Total Pendapatan (Bulan Ini)</div>
                    <h2 class="fw-bold mt-2 mb-0">Rp {{ number_format($grandTotal, 0, ',', '.') }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted">Total Transaksi</div>
                    <h2 class="fw-bold mt-2 mb-0 text-dark">{{ $totalTrx }} <span class="fs-6 fw-normal text-muted">Order</span></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 fw-bold">Rincian Harian</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3">Tanggal</th>
                            <th class="py-3">Jumlah Order</th>
                            <th class="pe-4 py-3 text-end">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $row)
                        <tr>
                            <td class="ps-4">
                                {{ \Carbon\Carbon::parse($row->date)->format('d F Y') }}
                                <span class="badge bg-light text-dark border ms-2">
                                    {{ \Carbon\Carbon::parse($row->date)->format('l') }}
                                </span>
                            </td>
                            <td>{{ $row->total_orders }} Transaksi</td>
                            <td class="pe-4 text-end fw-bold text-success">
                                + Rp {{ number_format($row->revenue, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">
                                Tidak ada transaksi pada bulan ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection