@extends('layouts.client')

@section('title', 'Daftar Pesanan')

@section('content')
<div class="container-fluid p-0">
    <h4 class="fw-bold mb-4">Pesanan Masuk</h4>

    {{-- TABS FILTER STATUS --}}
    <div class="card border-0 shadow-sm mb-4 position-relative" style="z-index: 10;">
        <div class="card-body p-0">
            <ul class="nav nav-tabs nav-fill border-bottom-0" style="background-color: #f8f9fa;">
                
                {{-- Semua --}}
                <li class="nav-item">
                    <a class="nav-link {{ !request('status') ? 'active fw-bold border-bottom-0 bg-white' : 'text-muted' }} py-3" 
                       href="{{ route('client.orders.index', ['website' => $website->id, 'search' => request('search')]) }}">
                        Semua
                        <span class="badge bg-secondary ms-1">{{ $totalOrders ?? 0 }}</span>
                    </a>
                </li>
                
                {{-- Pending --}}
                <li class="nav-item">
                    <a class="nav-link {{ request('status') == 'pending' ? 'active fw-bold border-bottom-0 bg-white text-danger' : 'text-muted' }} py-3" 
                       href="{{ route('client.orders.index', ['website' => $website->id, 'status' => 'pending', 'search' => request('search')]) }}">
                        Pending
                        @if(($statusCounts['pending'] ?? 0) > 0)
                            <span class="badge bg-danger ms-1">{{ $statusCounts['pending'] }}</span>
                        @endif
                    </a>
                </li>

                {{-- Awaiting Confirmation --}}
                <li class="nav-item">
                    <a class="nav-link {{ request('status') == 'awaiting_confirmation' ? 'active fw-bold border-bottom-0 bg-white text-warning' : 'text-muted' }} py-3" 
                       href="{{ route('client.orders.index', ['website' => $website->id, 'status' => 'awaiting_confirmation', 'search' => request('search')]) }}">
                        Awaiting Confirmation
                        @if(($statusCounts['awaiting_confirmation'] ?? 0) > 0)
                            <span class="badge bg-warning text-dark ms-1">{{ $statusCounts['awaiting_confirmation'] }}</span>
                        @endif
                    </a>
                </li>

                {{-- Processing --}}
                <li class="nav-item">
                    <a class="nav-link {{ request('status') == 'processing' ? 'active fw-bold border-bottom-0 bg-white text-info' : 'text-muted' }} py-3" 
                       href="{{ route('client.orders.index', ['website' => $website->id, 'status' => 'processing', 'search' => request('search')]) }}">
                        Processing
                        @if(($statusCounts['processing'] ?? 0) > 0)
                            <span class="badge bg-info text-white ms-1">{{ $statusCounts['processing'] }}</span>
                        @endif
                    </a>
                </li>

                {{-- Shipped --}}
                <li class="nav-item">
                    <a class="nav-link {{ request('status') == 'shipped' ? 'active fw-bold border-bottom-0 bg-white text-primary' : 'text-muted' }} py-3" 
                       href="{{ route('client.orders.index', ['website' => $website->id, 'status' => 'shipped', 'search' => request('search')]) }}">
                        Shipped
                        @if(($statusCounts['shipped'] ?? 0) > 0)
                            <span class="badge bg-primary text-white ms-1">{{ $statusCounts['shipped'] }}</span>
                        @endif
                    </a>
                </li>

               {{-- Completed & Cancelled --}}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ in_array(request('status'), ['completed', 'cancelled']) ? 'active fw-bold border-bottom-0 bg-white' : 'text-muted' }} py-3" 
                       data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
                        Lainnya
                    </a>
                    <ul class="dropdown-menu border-0 shadow">
                        <li>
                            <a class="dropdown-item {{ request('status') == 'completed' ? 'active' : '' }}" 
                               href="{{ route('client.orders.index', ['website' => $website->id, 'status' => 'completed', 'search' => request('search')]) }}">
                               Completed <span class="badge bg-success ms-1">{{ $statusCounts['completed'] ?? 0 }}</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ request('status') == 'cancelled' ? 'active' : '' }}" 
                               href="{{ route('client.orders.index', ['website' => $website->id, 'status' => 'cancelled', 'search' => request('search')]) }}">
                               Cancelled <span class="badge bg-danger ms-1">{{ $statusCounts['cancelled'] ?? 0 }}</span>
                            </a>
                        </li>
                    </ul>
                </li>

            </ul>
        </div>
    </div>

    {{-- SEARCH BAR --}}
    <div class="row mb-3">
        <div class="col-md-6">
            <form action="{{ route('client.orders.index', $website->id) }}" method="GET">
                {{-- PERHATIKAN INI: Menyimpan filter status saat melakukan pencarian --}}
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                
                <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" 
                           placeholder="Cari No. Order, Nama, atau Email..." value="{{ request('search') }}">
                    <button class="btn btn-primary px-4" type="submit">Cari</button>
                    @if(request('search'))
                        <a href="{{ route('client.orders.index', ['website' => $website->id, 'status' => request('status')]) }}" class="btn btn-danger"><i class="bi bi-x-lg"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- BAGIAN TABEL ORDER ANDA TETAP SAMA SEPERTI SEBELUMNYA DI SINI --}}
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