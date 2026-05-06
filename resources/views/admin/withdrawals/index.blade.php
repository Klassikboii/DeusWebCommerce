@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="m-0">Kelola Penarikan Dana (Withdrawals)</h2>
    </div>

    <!-- Menampilkan Pesan Notifikasi -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Waktu Request</th>
                            <th>Nama Toko / Klien</th>
                            <th>Nominal Pencairan</th>
                            <th>Bank Tujuan</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($withdrawals as $wd)
                            <tr>
                                <td>{{ $wd->created_at->format('d M Y, H:i') }}</td>
                                <td>
                                    <strong>{{ $wd->website->site_name ?? 'Toko Tidak Diketahui' }}</strong>
                                </td>
                                <td class="fw-bold text-primary">Rp {{ number_format($wd->amount, 0, ',', '.') }}</td>
                                <td>
                                    <span class="fw-bold">{{ $wd->bank_name }}</span><br>
                                    <small class="text-muted">{{ $wd->bank_account_number }} a.n {{ $wd->bank_account_name }}</small>
                                </td>
                                <td>
                                    @if($wd->status == 'pending')
                                        <span class="badge bg-warning text-dark">Menunggu Transfer</span>
                                    @elseif($wd->status == 'approved')
                                        <span class="badge bg-success">Berhasil Selesai</span>
                                    @else
                                        <span class="badge bg-danger">Ditolak</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($wd->status == 'pending')
                                        <!-- Tombol Aksi untuk Pending -->
                                        <button class="btn btn-sm btn-success mb-1" data-bs-toggle="modal" data-bs-target="#approveModal{{ $wd->id }}">
                                            Setujui
                                        </button>
                                        <button class="btn btn-sm btn-danger mb-1" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $wd->id }}">
                                            Tolak
                                        </button>
                                    @elseif($wd->status == 'approved')
                                        <!-- Jika sudah disetujui, tampilkan tombol lihat bukti transfer -->
                                        <a href="{{ Storage::url($wd->transfer_proof) }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat Bukti</a>
                                        @if($wd->admin_note)
                                            <div class="mt-1"><small class="text-muted">Catatan: {{ $wd->admin_note }}</small></div>
                                        @endif
                                    @else
                                        <!-- Jika ditolak, tampilkan alasan penolakan -->
                                        <small class="text-danger fw-bold">Alasan Penolakan:</small><br>
                                        <small class="text-muted">{{ $wd->admin_note }}</small>
                                    @endif
                                </td>
                            </tr>

                           <!-- MODAL SETUJUI (UPLOAD BUKTI TRANSFER) -->
                            @if($wd->status == 'pending')
                            <div class="modal fade" id="approveModal{{ $wd->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-success text-white">
                                            <h5 class="modal-title">Setujui Pencairan Dana</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('admin.withdrawals.approve', $wd->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="modal-body text-start">
                                                <p>Peringatan: Pastikan Anda sudah mentransfer uang sebesar <strong>Rp {{ number_format($wd->amount, 0, ',', '.') }}</strong> ke rekening berikut:</p>
                                                <div class="bg-light p-3 rounded mb-3">
                                                    <strong>Bank:</strong> {{ $wd->bank_name }}<br>
                                                    <strong>No. Rek:</strong> {{ $wd->bank_account_number }}<br>
                                                    <strong>Atas Nama:</strong> {{ $wd->bank_account_name }}
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Upload Bukti Transfer <span class="text-danger">*</span></label>
                                                    <input type="file" class="form-control" name="transfer_proof" accept="image/png, image/jpeg, image/jpg" required>
                                                    <small class="text-muted">Format: JPG, PNG. Maksimal 2MB.</small>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Catatan Tambahan (Opsional)</label>
                                                    <textarea class="form-control" name="admin_note" rows="2" placeholder="Contoh: Transfer dari rekening BCA Deus..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-success">Upload & Selesaikan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- MODAL TOLAK -->
                            <div class="modal fade" id="rejectModal{{ $wd->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">Tolak Pencairan Dana</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('admin.withdrawals.reject', $wd->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-body text-start">
                                                <p>Anda yakin ingin menolak pencairan sebesar <strong>Rp {{ number_format($wd->amount, 0, ',', '.') }}</strong> milik toko <strong>{{ $wd->website->name }}</strong>? Saldo akan dikembalikan ke dompet klien.</p>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Alasan Penolakan <span class="text-danger">*</span></label>
                                                    <textarea class="form-control" name="admin_note" rows="3" required placeholder="Contoh: Nama rekening tidak sesuai dengan nama KTP pemilik toko..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-danger">Tolak & Kembalikan Saldo</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Tidak ada data penarikan dana saat ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($withdrawals->hasPages())
            <div class="card-footer bg-white border-top-0">
                {{ $withdrawals->links() }}
            </div>
        @endif
    </div>
</div>
@endsection