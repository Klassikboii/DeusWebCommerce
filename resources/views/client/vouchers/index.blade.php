@extends('layouts.client')

@section('title', 'Kelola Voucher & Diskon')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark mb-0">Voucher Toko</h3>
        <a href="{{ route('client.vouchers.create', $website->id) }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Buat Voucher Baru
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Kode Voucher</th>
                            <th>Tipe & Nilai Diskon</th>
                            <th>Min. Belanja</th>
                            <th>Kuota Terpakai</th>
                            <th>Masa Berlaku</th>
                            <th>Target RFM</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vouchers as $voucher)
                            <tr>
                                <td><span class="badge bg-dark fs-6">{{ $voucher->code }}</span></td>
                                <td>
                                    @if($voucher->discount_type == 'nominal')
                                        Rp {{ number_format($voucher->discount_value, 0, ',', '.') }}
                                    @else
                                        {{ $voucher->discount_value }}% 
                                        <br><small class="text-muted">(Maks. Rp {{ number_format($voucher->max_discount_amount, 0, ',', '.') }})</small>
                                    @endif
                                </td>
                                <td>Rp {{ number_format($voucher->min_purchase, 0, ',', '.') }}</td>
                                <td>
                                    {{ $voucher->used_count }} / 
                                    {{ $voucher->max_uses ? $voucher->max_uses : '∞' }}
                                </td>
                                <td>
                                    @if($voucher->valid_until)
                                        {{ $voucher->valid_until->format('d M Y H:i') }}
                                    @else
                                        <span class="text-muted">Tanpa Batas</span>
                                    @endif
                                </td>
                                <td>
                                    @if($voucher->target_rfm_segment)
                                        <span class="badge bg-info text-dark">{{ $voucher->target_rfm_segment }}</span>
                                    @else
                                        <span class="badge bg-secondary">Semua Pelanggan</span>
                                    @endif
                                </td>
                                <td>
                                    @if($voucher->is_active && ($voucher->valid_until == null || $voucher->valid_until > now()) && ($voucher->max_uses == null || $voucher->used_count < $voucher->max_uses))
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-danger">Tidak Aktif / Habis</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('client.vouchers.destroy', ['website' => $website->id, 'voucher' => $voucher->id]) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus voucher ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Belum ada voucher yang dibuat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection