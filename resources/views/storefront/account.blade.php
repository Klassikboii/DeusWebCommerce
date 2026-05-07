@extends('layouts.' . ($website->active_template ?? 'modern'))

@section('title', 'Akun Saya - ' . $website->site_name)

@section('content')
<div class="container py-5">
    <div class="row">
        
        {{-- SIDEBAR PROFIL & MENU --}}
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body text-center p-4">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 60px; height: 60px; font-size: 1.5rem; font-weight: bold;">
                        {{ substr($customer->name, 0, 1) }}
                    </div>
                    <h5 class="fw-bold mb-1">{{ $customer->name }}</h5>
                    <p class="text-muted small mb-3">{{ $customer->email }}</p>
                    {{-- Di account.blade.php, bagian sidebar --}}
                    <a href="{{ route('store.profile.edit') }}" class="btn btn-outline-primary fw-bold mb-2 d-block shadow-sm">
                        <i class="bi bi-person-gear me-1"></i> Edit Profil
                    </a>
                    <form action="{{ route('store.logout') }}" method="POST" class="d-grid"  onsubmit="return confirm('Yakin ingin keluar dari akun ini?');">
                        @csrf
                        <button type="submit" class="btn btn-light border text-danger fw-bold shadow-sm">
                            <i class="bi bi-box-arrow-right me-1"></i> Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- KONTEN UTAMA: RIWAYAT PESANAN --}}
        <div class="col-lg-9">
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white p-4 border-bottom">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-bag-check me-2 text-primary"></i>Riwayat Pesanan Anda</h5>
                </div>
                <div class="card-body p-0">
                    @if($orders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-4 py-3">Order ID</th>
                                        <th class="py-3">Tanggal</th>
                                        <th class="py-3">Total Belanja</th>
                                        <th class="py-3">Status Pembayaran</th>
                                        <th class="py-3">Status Pesanan</th>
                                        <th class="px-4 py-3 text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td class="px-4 py-3 fw-bold text-dark">{{ $order->order_number }}</td>
                                            <td class="py-3 text-muted small">{{ $order->created_at->format('d M Y, H:i') }}</td>
                                            <td class="py-3 fw-bold">Rp {{ number_format($order->total_amount + $order->shipping_cost, 0, ',', '.') }}</td>
                                            
                                            {{-- BADGE STATUS PEMBAYARAN --}}
                                            <td class="py-3">
                                                @if($order->payment_status == 'paid')
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success"><i class="bi bi-check-circle me-1"></i> Lunas</span>
                                                @else
                                                    <span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning"><i class="bi bi-clock me-1"></i> Belum Bayar</span>
                                                @endif
                                            </td>

                                            {{-- BADGE STATUS PESANAN (Bisa disesuaikan dengan enum database Anda) --}}
                                            <td class="py-3">
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'bg-secondary',
                                                        'awaiting_confirmation' => 'bg-info text-dark',
                                                        'processing' => 'bg-primary',
                                                        'shipped' => 'bg-info',
                                                        'completed' => 'bg-success',
                                                        'cancelled' => 'bg-danger'
                                                    ];
                                                    $statusLabels = [
                                                        'pending' => 'Menunggu Pembayaran',
                                                        'awaiting_confirmation' => 'Verifikasi Admin',
                                                        'processing' => 'Sedang Diproses',
                                                        'shipped' => 'Sedang Dikirim',
                                                        'completed' => 'Selesai',
                                                        'cancelled' => 'Dibatalkan'
                                                    ];
                                                    
                                                    $color = $statusColors[$order->status] ?? 'bg-secondary';
                                                    $label = $statusLabels[$order->status] ?? strtoupper($order->status);
                                                @endphp
                                                <span class="badge {{ $color }}">{{ $label }}</span>
                                            </td>

                                            <td class="px-4 py-3 text-end">
                                                {{-- Tombol menuju halaman Invoice/Pembayaran --}}
                                                <a href="{{ route('store.payment', ['order_number' => $order->order_number]) }}" class="btn btn-sm btn-outline-primary fw-bold shadow-sm">
                                                    @if($order->payment_status == 'paid')
                                                        Lihat Detail
                                                    @else
                                                        Bayar Sekarang
                                                    @endif
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <h6 class="mt-3 fw-bold">Belum ada pesanan</h6>
                            <p class="text-muted small">Anda belum melakukan transaksi di toko ini.</p>
                            <a href="{{ route('store.home') }}" class="btn btn-primary px-4 mt-2">Mulai Belanja</a>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection