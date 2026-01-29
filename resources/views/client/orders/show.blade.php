@extends('layouts.client')

@section('title', 'Detail Pesanan #' . $order->order_number)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div>
        <a href="{{ route('client.orders.index', $website->id) }}" class="text-decoration-none text-muted small">
            <button type="button" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Kembali
            </button>
            
        </a>
        <h4 class="fw-bold mt-1">Order #{{ $order->order_number }}</h4>
        <span class="text-muted small">Dipesan pada: {{ $order->created_at->format('d M Y, H:i') }}</span>
    </div>
    
    <div>
        @php
            $badges = [
                'pending' => 'bg-warning text-dark',
                'processing' => 'bg-info text-white',
                'shipped' => 'bg-primary',
                'completed' => 'bg-success',
                'cancelled' => 'bg-danger'
            ];
            $statusLabel = [
                'pending' => 'Menunggu Pembayaran',
                'processing' => 'Diproses',
                'shipped' => 'Dikirim',
                'completed' => 'Selesai',
                'cancelled' => 'Dibatalkan'
            ];
        @endphp
        <span class="badge {{ $badges[$order->status] ?? 'bg-secondary' }} fs-6 px-3 py-2">
            {{ $statusLabel[$order->status] ?? ucfirst($order->status) }}
        </span>
    </div>
</div>

<div class="row">
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0">Rincian Produk</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th>Produk</th>
                                <th class="text-end">Harga</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded p-1 me-3" style="width: 50px; height: 50px;">
                                            <i class="bi bi-box-seam text-secondary fs-4 d-flex align-items-center justify-content-center h-100"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $item->product_name }}</div>
                                            <div class="small text-muted">SKU: {{ $item->product_sku ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                <td class="text-center">{{ $item->qty }}</td>
                                <td class="text-end fw-bold">Rp {{ number_format($item->price * $item->qty, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-top">
                            <tr>
                                <td colspan="3" class="text-end pt-3">Subtotal</td>
                                <td class="text-end pt-3">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end text-muted">Ongkos Kirim</td>
                                <td class="text-end">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end fw-bold fs-5">Grand Total</td>
                                <td class="text-end fw-bold fs-5 text-primary">
                                    Rp {{ number_format($order->total_amount + $order->shipping_cost, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0">Informasi Pengiriman</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <small class="text-muted text-uppercase fw-bold">Nama Penerima</small>
                        <p class="fw-bold mb-0">{{ $order->customer_name }}</p>
                        <p class="mb-0">{{ $order->customer_email }}</p>
                        <p class="mb-0">{{ $order->customer_whatsapp ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase fw-bold">Alamat Tujuan</small>
                        <p class="mb-0">{{ $order->customer_address }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0">Update Pesanan</h6>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger small mb-3">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form action="{{ route('client.orders.update', [$website->id, $order->id]) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Status Pesanan</label>
                        <select name="status" id="statusSelect" class="form-select" onchange="toggleResiInput()">
                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                            <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Diproses (Packing)</option>
                            <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Dikirim (Input Resi)</option>
                            <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Selesai</option>
                            <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Catatan Tambahan (Opsional)</label>
                        <textarea name="note" class="form-control form-control-sm" rows="2" placeholder="Contoh: Paket sedang transit di Jakarta..."></textarea>
                    </div>
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-white py-3">
                            <h6 class="fw-bold mb-0">Riwayat Pesanan</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">                                @forelse($order->histories()->latest()->get() as $history)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-light text-dark border">{{ $history->status }}</span>
                                        <small class="text-muted" style="font-size: 10px;">{{ $history->created_at->format('d M H:i') }}</small>
                                    </div>
                                    <p class="mb-0 small text-muted">{{ $history->note }}</p>
                                </div>
                                @empty
                                <div class="p-3 text-center text-muted small">Belum ada riwayat.</div>
                                @endforelse
                                
                                <div class="list-group-item bg-light">
                                    <small class="text-muted fst-italic">Pesanan dibuat pada {{ $order->created_at->format('d M Y, H:i') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="resiInput" class="bg-light p-3 rounded mb-3 {{ $order->status == 'shipped' ? '' : 'd-none' }}">
                        <h6 class="small fw-bold text-primary mb-2"><i class="bi bi-truck me-1"></i> Data Pengiriman</h6>
                        
                        <div class="mb-2">
                            <label class="form-label small">Nama Kurir / Ekspedisi</label>
                            <input type="text" name="courier_name" class="form-control form-control-sm" 
                                   placeholder="Contoh: JNE, J&T, GoSend" 
                                   value="{{ $order->courier_name }}">
                        </div>
                        <div class="mb-0">
                            <label class="form-label small">Nomor Resi / Tracking</label>
                            <input type="text" name="tracking_number" class="form-control form-control-sm font-monospace" 
                                   placeholder="Contoh: JP1234567890" 
                                   value="{{ $order->tracking_number }}">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Simpan Status</button>
                </form>

                <hr>

                <button class="btn btn-outline-secondary w-100 btn-sm">
                    <i class="bi bi-printer me-2"></i> Cetak Label Pengiriman
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleResiInput() {
        var status = document.getElementById('statusSelect').value;
        var resiBox = document.getElementById('resiInput');
        
        if (status === 'shipped') {
            resiBox.classList.remove('d-none');
        } else {
            resiBox.classList.add('d-none');
        }
    }
</script>
@endsection