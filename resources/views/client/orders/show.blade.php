@extends('layouts.client')

@section('title', 'Detail Order #' . $order->order_number)

@section('content')
<div class="container-fluid p-0" style="max-width: 900px;">
    
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <a href="{{ route('client.orders.index', $website->id) }}" class="text-decoration-none text-muted mb-2 d-inline-block">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar
            </a>
            <h4 class="fw-bold">Order #{{ $order->order_number }}</h4>
            <span class="text-muted small">Dipesan pada {{ $order->created_at->format('d F Y, H:i') }} WIB</span>
        </div>
        
        <div class="dropdown">
            <button class="btn btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">
                Ubah Status: <strong>{{ ucfirst($order->status) }}</strong>
            </button>
            <ul class="dropdown-menu">
                <li><form action="{{ route('client.orders.update', [$website->id, $order->id]) }}" method="POST">@csrf @method('PUT') <input type="hidden" name="status" value="processing"> <button class="dropdown-item">Proses Pesanan</button></form></li>
                <li><form action="{{ route('client.orders.update', [$website->id, $order->id]) }}" method="POST">@csrf @method('PUT') <input type="hidden" name="status" value="shipped"> <button class="dropdown-item">Kirim Barang</button></form></li>
                <li><hr class="dropdown-divider"></li>
                <li><form action="{{ route('client.orders.update', [$website->id, $order->id]) }}" method="POST">@csrf @method('PUT') <input type="hidden" name="status" value="completed"> <button class="dropdown-item text-success">Selesai</button></form></li>
                <li><form action="{{ route('client.orders.update', [$website->id, $order->id]) }}" method="POST">@csrf @method('PUT') <input type="hidden" name="status" value="cancelled"> <button class="dropdown-item text-danger">Batalkan</button></form></li>
            </ul>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">Item Pesanan</div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td class="ps-4 py-3" width="60">
                                    @if($item->product_image)
                                        <img src="{{ asset('storage/' . $item->product_image) }}" class="rounded border" width="50" height="50" style="object-fit:cover">
                                    @else
                                        <div class="bg-light rounded border d-flex align-items-center justify-content-center" style="width:50px; height:50px"><i class="bi bi-image"></i></div>
                                    @endif
                                </td>
                                <td class="py-3">
                                    <div class="fw-bold">{{ $item->product_name }}</div>
                                    <div class="text-muted small">Rp {{ number_format($item->price) }} x {{ $item->qty }}</div>
                                </td>
                                <td class="pe-4 py-3 text-end fw-bold">
                                    Rp {{ number_format($item->subtotal) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="2" class="ps-4 py-3 text-end fw-bold">Total Pembayaran</td>
                                <td class="pe-4 py-3 text-end fw-bold fs-5 text-primary">
                                    Rp {{ number_format($order->total_amount) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold">Informasi Pelanggan</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Nama Penerima</label>
                        <div class="fw-bold">{{ $order->customer_name }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Kontak WhatsApp</label>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold">{{ $order->customer_whatsapp }}</span>
                            <a href="https://wa.me/{{ $order->customer_whatsapp }}" target="_blank" class="btn btn-success btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center" style="width:24px; height:24px;">
                                <i class="bi bi-whatsapp" style="font-size: 12px;"></i>
                            </a>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Alamat Pengiriman</label>
                        <div class="fw-bold text-wrap">{{ $order->customer_address }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection