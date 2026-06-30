{{-- resources/views/client/tickets/index.blade.php --}}
@extends('layouts.client')

@section('title', 'Pusat Bantuan')

@section('content')
<div class="container-fluid p-0" style="max-width: 1000px;">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Pusat Bantuan</h4>
            <p class="text-muted m-0">Laporkan kendala, bug, atau pertanyaan seputar toko Anda.</p>
        </div>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalBuatTiket">
            <i class="bi bi-plus-lg me-1"></i> Buat Tiket Baru
        </button>
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
                        <th class="ps-4 py-3 border-0" style="width: 15%;">No. Tiket</th>
                        <th class="py-3 border-0" style="width: 20%;">Toko Terkait</th>
                        <th class="py-3 border-0" style="width: 30%;">Subjek & Status</th>
                        <th class="py-3 border-0" style="width: 20%;">Update Terakhir</th>
                        <th class="pe-4 py-3 border-0 text-end" style="width: 15%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr>
                        <td class="ps-4 fw-bold text-dark">{{ $ticket->ticket_number }}</td>
                        <td>
                            @if($ticket->website)
                                <span class="badge bg-light text-dark border"><i class="bi bi-shop me-1"></i> {{ $ticket->website->site_name }}</span>
                            @else
                                <span class="badge bg-light text-secondary border">Masalah Umum/Akun</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold text-dark mb-1">{{ $ticket->subject }}</div>
                            @if($ticket->status == 'pending')
                                <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i> Menunggu Respon</span>
                            @elseif($ticket->status == 'in_progress')
                                <span class="badge bg-info text-dark"><i class="bi bi-tools me-1"></i> Sedang Ditinjau</span>
                            @else
                                <span class="badge bg-success"><i class="bi bi-check-all me-1"></i> Selesai</span>
                            @endif
                        </td>
                        <td class="text-muted small">
                            {{ $ticket->updated_at->diffForHumans() }}
                        </td>
                        <td class="pe-4 text-end">
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalLihatTiket{{ $ticket->id }}">
                                Lihat Detail
                            </button>
                        </td>
                    </tr>

                    {{-- Modal Lihat Detail Tiket --}}
                    <div class="modal fade" id="modalLihatTiket{{ $ticket->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header bg-light">
                                    <h6 class="modal-title fw-bold">Detail Tiket: {{ $ticket->ticket_number }}</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="mb-3">
                                        <label class="text-muted small fw-bold">Pesan Anda:</label>
                                        <div class="p-3 bg-light rounded text-dark" style="white-space: pre-wrap; font-size: 0.9rem;">{{ $ticket->description }}</div>
                                    </div>
                                    
                                    <div class="mb-1">
                                        <label class="text-muted small fw-bold">Balasan Tim Support:</label>
                                        @if($ticket->admin_reply)
                                            <div class="p-3 bg-primary bg-opacity-10 border border-primary-subtle rounded text-dark" style="white-space: pre-wrap; font-size: 0.9rem;">{{ $ticket->admin_reply }}</div>
                                        @else
                                            <div class="p-3 bg-light rounded text-muted fst-italic" style="font-size: 0.9rem;">Belum ada balasan dari admin. Tim kami sedang meninjau tiket Anda.</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="opacity-50 mb-3"><i class="bi bi-headset" style="font-size: 3rem;"></i></div>
                            <h6 class="text-muted fw-bold">Belum Ada Tiket Bantuan</h6>
                            <p class="text-muted small">Jika Anda mengalami kendala, jangan ragu untuk membuat tiket baru.</p>
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

{{-- Modal Buat Tiket Baru --}}
<div class="modal fade" id="modalBuatTiket" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title fw-bold"><i class="bi bi-envelope-paper me-2"></i>Buat Tiket Bantuan</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('client.tickets.store', $website->id) }}" method="POST" >
                @csrf
                <div class="modal-body p-4">
                    {{-- 🚨 DROPDOWN WEBSITE DIHAPUS 🚨 --}}
                    
                    <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center" style="font-size: 0.85rem;">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Tiket ini akan otomatis ditautkan ke toko: <strong>{{ $website->site_name }}</strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Subjek Masalah</label>
                        <input type="text" name="subject" class="form-control" placeholder="Contoh: Gagal tarik data dari Accurate" required>
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-bold">Jelaskan Kendala Anda</label>
                        <textarea name="description" class="form-control" rows="5" placeholder="Jelaskan secara detail langkah-langkah yang Anda lakukan sebelum menemukan error ini..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i> Kirim Tiket</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection