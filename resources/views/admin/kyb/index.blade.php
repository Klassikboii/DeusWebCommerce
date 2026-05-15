@extends('layouts.admin')

@section('title', 'Antrean Verifikasi KYB Pivot')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold">Antrean Verifikasi Merchant (KYB)</h4>
    </div>

    {{-- Pesan Sukses --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3">Tanggal Pengajuan</th>
                            <th class="py-3">Nama Merchant</th>
                            <th class="py-3">Website Domain</th>
                            <th class="py-3">Bank Tujuan</th>
                            <th class="py-3">Status</th>
                            <th class="px-4 py-3 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($submissions as $row)
                            <tr>
                                <td class="px-4">{{ $row->created_at->format('d M Y, H:i') }}</td>
                                <td>
                                    <strong>{{ $row->name }}</strong><br>
                                    <small class="text-muted">{{ $row->short_name }}</small>
                                </td>
                                <td>
                                    <a href="https://{{ $row->website }}" target="_blank" class="text-decoration-none">
                                        {{ $row->website }} <i class="bi bi-box-arrow-up-right small"></i>
                                    </a>
                                </td>
                                <td>{{ $row->bank_channel_code }}</td>
                                <td>
                                    @if($row->status == 'pending')
                                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Menunggu</span>
                                    @elseif($row->status == 'approved')
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Aktif</span>
                                    @else
                                        <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Ditolak</span>
                                    @endif
                                </td>
                                <td class="px-4 text-end">
                                    <a href="{{ route('admin.kyb.show', $row->id) }}" class="btn btn-sm btn-primary">
                                        {{ $row->status == 'pending' ? 'Review Sekarang' : 'Lihat Detail' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Belum ada antrean pengajuan verifikasi saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        {{-- Pagination --}}
        @if($submissions->hasPages())
            <div class="card-footer bg-white py-3 border-top-0">
                {{ $submissions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection