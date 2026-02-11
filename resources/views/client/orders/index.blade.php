@extends('layouts.client')

@section('title', 'Order Masuk')

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <h4 class="fw-bold mb-1">Daftar Pesanan</h4>
        <p class="text-muted m-0">Pantau semua transaksi masuk di toko Anda.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3">No Invoice</th>
                            <th class="py-3">Pelanggan</th>
                            <th class="py-3">Total Belanja</th>
                            <th class="py-3">Status</th>
                            <th class="py-3">Tanggal</th>
                            <th class="pe-4 py-3 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td class="ps-4 fw-bold text-primary">
                                #{{ $order->order_number }}
                            </td>
                            <td>
                                <div class="fw-bold">{{ $order->customer_name }}</div>
                                <div class="small text-muted"><i class="bi bi-whatsapp"></i> {{ $order->customer_whatsapp }}</div>
                            </td>
                            <td class="fw-bold">
                                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                <div class="small text-muted fw-normal">{{ $order->items->sum('qty') }} Barang</div>
                            </td>
                            <td>
                                @php
                                    $badges = [
                                        'pending' => 'bg-warning text-dark',
                                        'processing' => 'bg-info text-white',
                                        'shipped' => 'bg-primary text-white',
                                        'completed' => 'bg-success text-white',
                                        'cancelled' => 'bg-danger text-white',
                                    ];
                                @endphp
                                <span class="badge {{ $badges[$order->status] ?? 'bg-secondary' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="text-muted small">
                                {{ $order->created_at->format('d M Y H:i') }}
                            </td>
                            <td class="pe-4 text-end">
                                <a href="{{ route('client.orders.show', [$website->id, $order->id]) }}" class="btn btn-sm btn-outline-dark">
                                    Detail <i class="bi bi-arrow-right"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox h1 d-block mb-3 opacity-25"></i>
                                Belum ada pesanan masuk.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($orders->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection