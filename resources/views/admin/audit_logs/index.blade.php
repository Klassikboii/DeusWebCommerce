@extends('layouts.admin') {{-- Sesuaikan dengan layout admin Anda --}}

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800 fw-bold">
            <i class="bi bi-shield-check text-primary me-2"></i> Audit Log & Aktivitas Klien
        </h2>
    </div>

    <!-- Filter & Pencarian -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="{{ route('admin.audit_logs.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label text-muted small fw-bold">Cari Klien / Deskripsi</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Nama, email, atau kata kunci..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small fw-bold">Kategori Aktivitas</label>
                    <select name="action_filter" class="form-select">
                        <option value="">-- Semua Aktivitas --</option>
                        @foreach($uniqueActions as $action)
                            <option value="{{ $action }}" {{ request('action_filter') == $action ? 'selected' : '' }}>
                                {{ strtoupper(str_replace('_', ' ', $action)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-1"></i> Terapkan Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Data -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Waktu</th>
                            <th>Klien</th>
                            <th>Kategori</th>
                            <th style="width: 35%;">Detail Aktivitas</th>
                            <th>IP & Perangkat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="ps-4 text-nowrap">
                                    <span class="d-block fw-bold">{{ $log->created_at->format('d M Y') }}</span>
                                    <small class="text-muted">{{ $log->created_at->format('H:i:s') }} WIB</small>
                                </td>
                                <td>
                                    <span class="d-block fw-bold text-dark">{{ $log->user->name ?? 'User Terhapus' }}</span>
                                    <small class="text-muted">{{ $log->user->email ?? '-' }}</small>
                                </td>
                                <td>
                                    @php
                                        // Variasi warna badge berdasarkan kategori
                                        $badgeColor = 'bg-secondary';
                                        if(str_contains($log->action, 'login') || str_contains($log->action, 'logout')) $badgeColor = 'bg-info text-dark';
                                        if(str_contains($log->action, 'product')) $badgeColor = 'bg-primary';
                                        if(str_contains($log->action, 'order')) $badgeColor = 'bg-success';
                                        if(str_contains($log->action, 'delete') || str_contains($log->action, 'cancel')) $badgeColor = 'bg-danger';
                                        if(str_contains($log->action, 'withdrawal')) $badgeColor = 'bg-warning text-dark';
                                    @endphp
                                    <span class="badge {{ $badgeColor }}">
                                        {{ strtoupper(str_replace('_', ' ', $log->action)) }}
                                    </span>
                                </td>
                                <td>
                                    <p class="mb-0 text-wrap" style="max-width: 400px;">
                                        {{ $log->description }}
                                    </p>
                                </td>
                                <td>
                                    <span class="d-block text-muted" style="font-family: monospace; font-size: 0.85rem;">
                                        <i class="bi bi-globe me-1"></i> {{ $log->ip_address ?? 'Unknown' }}
                                    </span>
                                    <span class="d-block text-muted text-truncate" style="max-width: 150px; font-size: 0.75rem;" title="{{ $log->user_agent }}">
                                        <i class="bi bi-pc-display me-1"></i> {{ $log->user_agent ?? 'Unknown' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-3"></i>
                                    Belum ada catatan aktivitas yang terekam.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination -->
        @if($logs->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection