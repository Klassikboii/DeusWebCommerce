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
                                            @if($item->product_image)
                                            <img src="{{ asset('storage/'.$item->product_image) }}" width="40" class="rounded">
                                            @else
                                            <i class="bi bi-box-seam text-secondary fs-4 d-flex align-items-center justify-content-center h-100"></i>
                                            @endif
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
                <h6 class="fw-bold text-muted mb-3">INFO PELANGGAN</h6>
                    <div class=" row-md-6">
                        <div class="mb-3">
                            <label class="small text-muted d-block">Nama</label>
                            <strong>{{ $order->customer_name }}</strong>
                        </div>
                        
                        <div class="mb-3">
                            <label class="small text-muted d-block">WhatsApp</label>
                            <strong>{{ $order->customer_whatsapp }}</strong>
                        </div>
                    </div>
                    <div class=" row-md-6">
                        <div class="mb-3">
                            <label class="small text-muted d-block">Alamat Kirim</label>
                            <p class="mb-0">{{ $order->customer_address }}</p>
                        </div>
                    </div>

                    <hr>

                    {{-- LOGIKA PESAN WA DINAMIS --}}
                    @php
                        // 1. Format Nomor
                        $waNumber = $order->customer_whatsapp;
                        if(str_starts_with($waNumber, '0')) {
                            $waNumber = '62' . substr($waNumber, 1);
                        }
                        
                        // 2. Generate Link Pembayaran Toko
                        $paymentLink = route('store.payment', ['subdomain' => $website->subdomain, 'order_number' => $order->order_number]);

                        // 3. Pesan Berdasarkan Status
                        if ($order->status == 'pending') {
                            $text = "Halo kak {$order->customer_name}. Mohon segera selesaikan pembayaran untuk pesanan {$order->order_number} agar tidak kehabisan stok.\n\nUpload bukti bayar disini ya: {$paymentLink}";
                        } elseif ($order->status == 'awaiting_confirmation') {
                            $text = "Halo kak, bukti pembayaran untuk {$order->order_number} sedang kami cek ya. Mohon ditunggu.";
                        } elseif ($order->status == 'shipped') {
                            $text = "Halo kak, pesanan {$order->order_number} sudah dikirim via {$order->courier_name}. Resi: {$order->tracking_number}.";
                        } else {
                            $text = "Halo kak {$order->customer_name}, update status pesanan {$order->order_number}: " . ucfirst($order->status);
                        }

                        $waLink = "https://wa.me/{$waNumber}?text=" . urlencode($text);
                    @endphp

                    <a href="{{ $waLink }}" target="_blank" class="btn btn-success w-100 text-white fw-bold mb-2">
                        <i class="bi bi-whatsapp me-1"></i> Hubungi Customer
                    </a>
                    <small class="text-muted d-block text-center" style="font-size: 11px;">
                        Klik untuk membuka WhatsApp Web dengan pesan otomatis.
                    </small>
            </div>
        </div>
    </div>

    {{-- KOLOM KANAN --}}
    <div class="col-lg-4">
        
        {{-- CARD 1: UPDATE PESANAN --}}
        <div class="card border-0 shadow-sm mb-4">
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
    
                            {{-- LOGIKA PENGUNCIAN: --}}
                            {{-- Jika sedang 'awaiting_confirmation', kunci dropdown hanya menampilkan status itu sendiri. --}}
                            {{-- Admin HARUS menggunakan tombol Terima/Tolak di bawah untuk mengubah status. --}}
                            
                            @if($order->status == 'awaiting_confirmation')
                                <option value="awaiting_confirmation" selected>Menunggu Konfirmasi Admin</option>
                            @else
                                {{-- Jika status BUKAN awaiting_confirmation, tampilkan opsi standar --}}
                                
                                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                                
                                {{-- Opsi awaiting_confirmation disembunyikan di sini agar admin tidak bisa manual memilihnya --}}
                                
                                <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Diproses (Packing)</option>
                                <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Dikirim (Input Resi)</option>
                                <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Selesai</option>
                                <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                            @endif

                        </select>

                        {{-- Tambahkan pesan kecil agar Admin tidak bingung kenapa dropdown terkunci --}}
                        @if($order->status == 'awaiting_confirmation')
                            <div class="form-text text-primary small">
                                <i class="bi bi-info-circle"></i> Gunakan tombol di kartu <strong>Bukti Pembayaran</strong> untuk memproses pesanan ini.
                            </div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Catatan Tambahan (Opsional)</label>
                        <textarea name="note" class="form-control form-control-sm" rows="2" placeholder="Contoh: Paket sedang transit di Jakarta..."></textarea>
                    </div>
                    
                    {{-- RIWAYAT PESANAN --}}
                    <div class="card border-0 shadow-sm mt-4 mb-3">
                        <div class="card-header bg-white py-2">
                            <h6 class="fw-bold mb-0 small">Riwayat</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush" style="max-height: 200px; overflow-y: auto;">
                                @forelse($order->histories()->latest()->get() as $history)
                                <div class="list-group-item py-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-light text-dark border" style="font-size: 10px;">{{ $history->status }}</span>
                                        <small class="text-muted" style="font-size: 9px;">{{ $history->created_at->format('d/m H:i') }}</small>
                                    </div>
                                    <p class="mb-0 text-muted lh-1" style="font-size: 11px;">{{ $history->note }}</p>
                                </div>
                                @empty
                                <div class="p-3 text-center text-muted small">Belum ada riwayat.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div id="resiInput" class="bg-light p-3 rounded mb-3 {{ $order->status == 'shipped' ? '' : 'd-none' }}">
                        <h6 class="small fw-bold text-primary mb-2"><i class="bi bi-truck me-1"></i> Data Pengiriman</h6>
                        
                        <div class="mb-2">
                            <label class="form-label small">Nama Kurir</label>
                            <input type="text" name="courier_name" class="form-control form-control-sm" 
                                   placeholder="JNE, J&T..." value="{{ $order->courier_name }}">
                        </div>
                        <div class="mb-0">
                            <label class="form-label small">No. Resi</label>
                            <input type="text" name="tracking_number" class="form-control form-control-sm font-monospace" 
                                   placeholder="JP123..." value="{{ $order->tracking_number }}">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Simpan Status</button>
                </form>
            </div>
        </div>

        {{-- CARD 2: BUKTI PEMBAYARAN (SEKARANG SUDAH BENAR POSISINYA) --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Bukti Pembayaran</h6>
            </div>
            <div class="card-body text-center">
                @if($order->status == 'pending')
                    <div class="alert alert-warning small mb-0">
                        <i class="bi bi-clock me-1"></i> Menunggu Customer upload bukti.
                    </div>
                    
                    {{-- Helper Link Pembayaran untuk Admin --}}
                    <div class="mt-3">
                        <small class="text-muted d-block mb-1">Link Pembayaran Customer:</small>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" id="paymentLinkInput" 
                                   value="{{ route('store.payment', ['subdomain' => $website->subdomain, 'order_number' => $order->order_number]) }}" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="copyPaymentLink()">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>

                @elseif($order->payment_proof)
                    <div class="mb-3 border rounded p-2 bg-light">
                        <a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank">
                            <img src="{{ asset('storage/' . $order->payment_proof) }}" class="img-fluid rounded" style="max-height: 200px;" alt="Bukti Bayar">
                        </a>
                    </div>
                    <div class="text-start small mb-3 bg-light p-2 rounded">
                        <div><strong>Bank:</strong> {{ $order->bank_name ?? '-' }}</div>
                        <div><strong>Waktu:</strong> {{ $order->updated_at->format('d M H:i') }}</div>
                    </div>

                    @if($order->status == 'awaiting_confirmation')
                        <div class="d-grid gap-2">
                            <form action="{{ route('client.orders.update', [$website->id, $order->id]) }}" method="POST">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="processing">
                                <input type="hidden" name="note" value="Pembayaran diterima. Pesanan diproses.">
                                <button class="btn btn-success w-100 btn-sm"><i class="bi bi-check-lg"></i> Terima</button>
                            </form>

                            <form action="{{ route('client.orders.update', [$website->id, $order->id]) }}" method="POST">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="pending">
                                <input type="hidden" name="note" value="Bukti tidak valid. Silakan upload ulang.">
                                <button class="btn btn-warning w-100 btn-sm"><i class="bi bi-arrow-repeat"></i> Minta Upload Ulang</button>
                            </form>

                            {{-- Tolak Keras: Batalkan Order --}}
                            <form action="{{ route('client.orders.update', [$website->id, $order->id]) }}" method="POST" onsubmit="return confirm('Yakin batalkan order? Stok akan dikembalikan.')">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="cancelled">
                                <input type="hidden" name="note" value="Pembayaran ditolak. Order dibatalkan.">
                                <button type="submit" class="btn btn-danger w-100 btn-sm">
                                    <i class="bi bi-x-circle"></i> Tolak & Batalkan
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="alert alert-success py-2 small mb-0"><i class="bi bi-check-all"></i> Bukti tersimpan.</div>
                    @endif
                @endif
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
    function copyPaymentLink() {
        var copyText = document.getElementById("paymentLinkInput");
        copyText.select();
        copyText.setSelectionRange(0, 99999); 
        navigator.clipboard.writeText(copyText.value);
        alert("Link pembayaran disalin! Kirim ke customer.");
    }
</script>
@endsection