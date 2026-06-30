{{-- resources/views/admin/tickets/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Tiket Bantuan Masuk')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Tiket Masuk</h4>
            <p class="text-muted m-0">Kelola keluhan, laporan bug, dan permintaan bantuan dari klien.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm border-0">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted">
                    <tr>
                        <th class="ps-4 py-3 border-0" style="width: 25%;">Klien & Toko</th>
                        <th class="py-3 border-0" style="width: 30%;">Subjek Masalah</th>
                        <th class="py-3 border-0" style="width: 15%;">Status</th>
                        <th class="py-3 border-0" style="width: 15%;">Tanggal</th>
                        <th class="pe-4 py-3 border-0 text-end" style="width: 15%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark">{{ $ticket->user->name ?? 'User Terhapus' }}</div>
                            @if($ticket->website)
                                <div class="small text-muted mt-1"><i class="bi bi-shop me-1"></i> {{ $ticket->website->site_name }}</div>
                            @else
                                <div class="small text-muted mt-1">Masalah Umum / Akun</div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold text-dark mb-1">{{ $ticket->ticket_number }}</div>
                            <div class="small text-muted text-truncate" style="max-width: 250px;">{{ $ticket->subject }}</div>
                        </td>
                        <td>
                            @if($ticket->status == 'pending')
                                <span class="badge bg-warning text-dark">Baru Masuk</span>
                            @elseif($ticket->status == 'in_progress')
                                <span class="badge bg-info text-dark">Diproses</span>
                            @else
                                <span class="badge bg-success">Selesai</span>
                            @endif
                        </td>
                        <td class="text-muted small">
                            {{ $ticket->created_at->format('d M Y, H:i') }}
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-flex justify-content-end align-items-center gap-1">
                                
                                {{-- 🚨 TOMBOL AJAIB: IMPERSONATE --}}
                                @if($ticket->user)
                                    {{-- Catatan: Pastikan nama route ini sesuai dengan route impersonate di web.php Anda --}}
                                    <form action="{{ route('admin.users.impersonate', $ticket->user->id) }}" method="POST" class="m-0" title="Login sebagai Klien ini" onsubmit="return confirm('Anda akan login sebagai {{ $ticket->user->name }}. Lanjutkan?')">
                                        @csrf
                                        {{-- Opsional: Kirim ID website agar Superadmin langsung terlempar ke dashboard toko yang rusak --}}
                                        @if($ticket->website_id)
                                            <input type="hidden" name="redirect_to" value="/manage/{{ $ticket->website_id }}/dashboard">
                                        @endif
                                        <button type="submit" class="btn btn-sm btn-dark d-flex align-items-center">
                                            <i class="bi bi-incognito"></i>
                                        </button>
                                    </form>
                                @endif
                                
                                {{-- Tombol Tinjau & Balas --}}
                                <button class="btn btn-sm btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalBalasTiket{{ $ticket->id }}">
                                    Tinjau & Balas
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- Modal Balas Tiket --}}
                    <div class="modal fade" id="modalBalasTiket{{ $ticket->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header bg-light">
                                    <h6 class="modal-title fw-bold">Detail Tiket: {{ $ticket->ticket_number }}</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('admin.tickets.update', $ticket->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body p-4">
                                        <div class="mb-4">
                                            <label class="text-muted small fw-bold">Laporan Klien:</label>
                                            <div class="p-3 bg-light rounded text-dark border" style="white-space: pre-wrap; font-size: 0.95rem;">{{ $ticket->description }}</div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold">Ubah Status</label>
                                                <select name="status" class="form-select">
                                                    <option value="pending" {{ $ticket->status == 'pending' ? 'selected' : '' }}>Pending (Baru)</option>
                                                    <option value="in_progress" {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>In Progress (Diproses)</option>
                                                    <option value="resolved" {{ $ticket->status == 'resolved' ? 'selected' : '' }}>Resolved (Selesai)</option>
                                                </select>
                                            </div>
                                            <div class="col-md-8">
                                                <label class="form-label fw-bold">Pesan Balasan Admin (Opsional)</label>
                                                <textarea name="admin_reply" class="form-control" rows="4" placeholder="Ketik balasan untuk klien di sini...">{{ $ticket->admin_reply }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light border-0">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan & Update Status</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="opacity-50 mb-3"><i class="bi bi-inbox" style="font-size: 3rem;"></i></div>
                            <h6 class="text-muted fw-bold">Inbox Kosong</h6>
                            <p class="text-muted small">Belum ada tiket bantuan yang masuk saat ini. Klien Anda sedang bahagia!</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($tickets->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</div>
@endsection