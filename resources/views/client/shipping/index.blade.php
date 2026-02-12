@extends('layouts.client')

@section('title', 'Pengaturan Ongkos Kirim')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Ongkos Kirim</h4>
            <p class="text-muted small mb-0">Kelola tarif pengiriman toko Anda.</p>
        </div>
        <div class="d-flex gap-2">
            {{-- TOMBOL DELETE ALL --}}
            @if($rates->count() > 0)
            <form action="{{ route('client.shipping.clear', $website->id) }}" method="POST" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin MENGHAPUS SEMUA data ongkir? Tindakan ini tidak bisa dibatalkan.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash me-1"></i> Hapus Semua
                </button>
            </form>
            @endif

            <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Import CSV
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="bi bi-plus-lg me-1"></i> Tambah Manual
            </button>
        </div>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Kota Asal</th>
                            <th>Kota Tujuan</th>
                            <th>Ekspedisi</th>
                            <th>Layanan</th> {{-- KOLOM BARU --}}
                            <th>Tarif / Kg</th>
                            <th>Estimasi</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rates as $rate)
                        <tr>
                            <td class="ps-4 text-muted">{{ $rate->origin_city }}</td>
                            <td class="fw-bold text-primary">{{ $rate->destination_city }}</td>
                            <td>{{ $rate->courier_name }}</td>
                            <td>
                                {{-- BADGE LAYANAN --}}
                                <span class="badge bg-light text-dark border">{{ $rate->service_name ?? '-' }}</span>
                            </td>
                            <td>Rp {{ number_format($rate->rate_per_kg, 0, ',', '.') }}</td>
                            <td>
                                @if($rate->min_day)
                                    {{ $rate->min_day }}{{ $rate->max_day ? ' - '.$rate->max_day : '' }} Hari
                                @elseif($rate->min_day && $rate->min_day == $rate->max_day)
                                    {{ $rate->min_day }} Hari
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <form action="{{ route('client.shipping.destroy', [$website->id, $rate->id]) }}" method="POST" onsubmit="return confirm('Hapus tarif ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-link text-danger p-0"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-box-seam fs-1 d-block mb-2"></i>
                                Belum ada data ongkir.<br>
                                Silakan Import CSV atau Tambah Manual.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($rates->hasPages())
        <div class="card-footer bg-white border-0 py-3">{{ $rates->links() }}</div>
        @endif
    </div>
</div>

{{-- MODAL TAMBAH MANUAL --}}
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('client.shipping.store', $website->id) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Ongkir Manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label">Kota Asal</label>
                        <input type="text" name="origin_city" class="form-control" placeholder="Surabaya" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Kota Tujuan</label>
                        <input type="text" name="destination_city" class="form-control" placeholder="Jakarta" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label">Kurir</label>
                        <input type="text" name="courier_name" class="form-control" placeholder="JNE" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Layanan</label>
                        <input type="text" name="service_name" class="form-control" placeholder="REG / YES" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label">Tarif per Kg (Rp)</label>
                        <input type="number" name="rate_per_kg" class="form-control" placeholder="10000" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Estimasi (Hari)</label>
                        <div class="input-group">
                            <input type="number" name="min_day" class="form-control" placeholder="Min">
                            <span class="input-group-text">-</span>
                            <input type="number" name="max_day" class="form-control" placeholder="Max">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL IMPORT --}}
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('client.shipping.import', $website->id) }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Import CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small mb-3">
                    <strong>Format 8 Kolom:</strong><br>
                    Asal, Tujuan, Kurir, Layanan, Tarif, Min Berat, Est Min, Est Max
                </div>
                <div class="mb-3">
                    <input type="file" name="file" class="form-control" accept=".csv" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Import</button>
            </div>
        </form>
    </div>
</div>
@endsection