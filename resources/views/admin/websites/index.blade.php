@extends('layouts.admin')

@section('content')
<h3 class="fw-bold mb-4">Users & Websites</h3>

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
                    <th class="px-4 py-3">Website / Toko</th>
                    <th>Pemilik (User)</th>
                    <th>Paket Aktif</th>
                    <th>Domain</th>
                    <th>Created At</th>
                    <th class="text-end px-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($websites as $web)
                <tr>
                    <td class="px-4">
                        <div class="d-flex align-items-center gap-3">
                            @if($web->logo)
                                <img src="{{ asset('storage/'.$web->logo) }}" class="rounded-circle border" width="40" height="40">
                            @else
                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    {{ substr($web->site_name, 0, 1) }}
                                </div>
                            @endif
                            <div>
                                <div class="fw-bold">{{ $web->site_name }}</div>
                                <a href="{{ route('store.home', $web->subdomain) }}" target="_blank" class="small text-decoration-none text-primary">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> Kunjungi
                                </a>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="fw-bold">{{ $web->user->name }}</div>
                        <div class="small text-muted">{{ $web->user->email }}</div>
                    </td>
                    <td>
                        @if($web->activeSubscription)
                            <span class="badge {{ $web->activeSubscription->package->price > 0 ? 'bg-primary' : 'bg-success' }}">
                                {{ $web->activeSubscription->package->name }}
                            </span>
                        @else
                            <span class="badge bg-secondary">Tidak Ada Paket</span>
                        @endif
                    </td>
                    <td>
                        <div class="small font-monospace">{{ $web->subdomain }}.webcommerce.id</div>
                        @if($web->custom_domain)
                            <div class="small text-success fw-bold">{{ $web->custom_domain }}</div>
                        @endif
                    </td>
                    <td class="text-muted small">
                        {{ $web->created_at->format('d M Y') }}
                    </td>
                    <td class="text-end px-4">
                        <form action="{{ route('admin.websites.destroy', $web->id) }}" method="POST" onsubmit="return confirm('HATI-HATI! Menghapus website ini akan menghapus semua produk dan datanya secara permanen. Lanjutkan?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-trash"></i> Hapus
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection