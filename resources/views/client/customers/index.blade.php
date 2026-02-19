@extends('layouts.client')

@section('title', 'Data Pelanggan')

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <h4 class="fw-bold mb-1">Pelanggan Toko</h4>
        <p class="text-muted m-0">Daftar orang yang pernah berbelanja di toko Anda.</p>
    </div>
<div class="row mb-3">
        <div class="col-md-6">
            <form action="{{ route('client.customers.index', $website->id) }}" method="GET">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" 
                           placeholder="Cari Nama, Email, atau Nomor Whatsapp..." value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit">Cari</button>
                    @if(request('search'))
                        <a href="{{ route('client.customers.index', $website->id) }}" class="btn btn-outline-danger"><i class="bi bi-x-lg"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3">Nama Pelanggan</th>
                            <th class="py-3">Kontak (WA)</th>
                            <th class="py-3">Frekuensi Belanja</th>
                            <th class="py-3">Total Pengeluaran</th>
                            <th class="py-3">Terakhir Order</th>
                            <th class="pe-4 py-3 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                        <tr>
                            <td class="ps-4 fw-bold">
                                {{ $customer->customer_name }}
                            </td>
                            <td>
                                <a href="https://wa.me/{{ $customer->customer_whatsapp }}" target="_blank" class="text-decoration-none text-success">
                                    <i class="bi bi-whatsapp me-1"></i> {{ $customer->customer_whatsapp }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary border">
                                    {{ $customer->total_orders }}x Order
                                </span>
                            </td>
                            <td class="fw-bold text-primary">
                                Rp {{ number_format($customer->total_spent, 0, ',', '.') }}
                            </td>
                            <td class="text-muted small">
                                {{ \Carbon\Carbon::parse($customer->last_order_date)->diffForHumans() }}
                            </td>
                            <td class="pe-4 text-end">
                                <a href="https://wa.me/{{ $customer->customer_whatsapp }}?text=Halo%20kak%20{{ $customer->customer_name }},%20terima%20kasih%20sudah%20berbelanja%20di%20{{ $website->site_name }}..." target="_blank" class="btn btn-sm btn-success text-white">
                                    <i class="bi bi-chat-dots"></i> Sapa
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-people h1 d-block mb-3 opacity-25"></i>
                                Belum ada data pelanggan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($customers->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection