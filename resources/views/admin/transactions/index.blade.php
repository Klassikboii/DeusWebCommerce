@extends('layouts.admin')

@section('content')
<h3 class="fw-bold mb-4">Transaksi Masuk</h3>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead class="bg-light">
                <tr>
                    <th class="px-4 py-3">Tanggal</th>
                    <th>User / Website</th>
                    <th>Paket</th>
                    <th>Bukti Transfer</th>
                    <th>Status</th>
                    <th class="text-end px-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $trx)
                <tr>
                    <td class="px-4 text-muted small">
                        {{ $trx->created_at->format('d M Y') }}<br>
                        {{ $trx->created_at->format('H:i') }}
                    </td>
                    <td>
                        <div class="fw-bold">{{ $trx->user->name }}</div>
                        <div class="small text-muted">{{ $trx->website->site_name }}</div>
                    </td>
                    <td>
                        <span class="badge bg-primary">{{ $trx->package->name }}</span><br>
                        <small>Rp {{ number_format($trx->amount) }}</small>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#proofModal{{ $trx->id }}">
                            <i class="bi bi-image"></i> Lihat Foto
                        </button>

                        <div class="modal fade" id="proofModal{{ $trx->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Bukti Transfer</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-center bg-dark">
                                        <img src="{{ asset('storage/' . $trx->proof_image) }}" class="img-fluid" style="max-height: 400px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($trx->status == 'pending')
                            <span class="badge bg-warning text-dark">Menunggu</span>
                        @elseif($trx->status == 'approved')
                            <span class="badge bg-success">Lunas</span>
                        @else
                            <span class="badge bg-danger">Ditolak</span>
                        @endif
                    </td>
                    <td class="text-end px-4">
                        @if($trx->status == 'pending')
                            <div class="d-flex gap-2 justify-content-end">
                                <form action="{{ route('admin.transactions.update', $trx->id) }}" method="POST" onsubmit="return confirm('Yakin terima pembayaran ini?')">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="status" value="approved">
                                    <button class="btn btn-success btn-sm">
                                        <i class="bi bi-check-lg"></i> Terima
                                    </button>
                                </form>

                                <form action="{{ route('admin.transactions.update', $trx->id) }}" method="POST" onsubmit="return confirm('Yakin tolak pembayaran ini?')">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="status" value="rejected">
                                    <button class="btn btn-danger btn-sm">
                                        <i class="bi bi-x-lg"></i> Tolak
                                    </button>
                                </form>
                            </div>
                        @else
                            <span class="text-muted small italic">Selesai</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection