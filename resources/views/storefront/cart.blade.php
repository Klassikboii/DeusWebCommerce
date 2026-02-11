@extends('layouts.' . ($website->active_template ?? 'modern'))

@section('content')
<body class="bg-light">
<style>
        :root { --primary-color: {{ $website->primary_color }}; }
        .btn-primary-custom { background-color: var(--primary-color); border-color: var(--primary-color); color: white; }
    </style>
    <div class="container py-5" style="max-width: 900px;">
        <div class="mb-4">
            <a href="{{ route('store.home', $website->subdomain) }}" class="text-decoration-none text-muted">
                <i class="bi bi-arrow-left"></i> Kembali Belanja
            </a>
            <h2 class="fw-bold mt-2">Keranjang & Checkout</h2>
        </div>

        @if(count($cart) > 0)
        <div class="row">
            <div class="col-md-7">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-0">
                        <table class="table mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3">Produk</th>
                                    <th class="py-3 text-center">Jml</th>
                                    <th class="pe-4 py-3 text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $total = 0; @endphp
                                
                                {{-- FIX: Loop variabel $cart --}}
                                @foreach($cart as $id => $details)
                                    @php $total += $details['price'] * $details['quantity']; @endphp {{-- Perhatikan 'quantity', bukan 'qty' --}}
                                    
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <div class="d-flex align-items-center gap-3">
                                                <form action="{{ route('store.cart.remove', ['subdomain' => $website->subdomain, 'id' => $id]) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0">
                                                        <i class="bi bi-x-circle fs-5"></i>
                                                    </button>
                                                </form>

                                                <div>
                                                    <div class="fw-bold">{{ $details['name'] }}</div>
                                                    <small class="text-muted">Rp {{ number_format($details['price']) }}</small>
                                                </div>
                                            </div>
                                        </td>

                                       {{-- Update Qty Form --}}
                                        <td class="align-middle text-center" style="width: 140px;">
                                             <form action="{{ route('store.cart.update', ['subdomain' => $website->subdomain]) }}" method="POST" class="d-flex align-items-center gap-2 justify-content-center">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="id" value="{{ $id }}">
                                                
                                                {{-- FIX: Value pakai $details['quantity'] --}}
                                                <input type="number" name="qty" value="{{ $details['quantity'] }}" 
                                                    class="form-control form-control-sm text-center" 
                                                    style="width: 60px;" min="1"
                                                    onchange="this.form.submit()"> 
                                            </form>
                                        </td>
                                        
                                        {{-- Subtotal --}}
                                        <td class="pe-4 align-middle text-end fw-bold">
                                            Rp {{ number_format($details['price'] * $details['quantity']) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-white">
                                <tr>
                                    <td colspan="2" class="ps-4 py-3 fw-bold text-uppercase text-muted">Total Bayar</td>
                                    <td class="pe-4 py-3 text-end fw-bold fs-5 text-primary">
                                        Rp {{ number_format($total) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 fw-bold">Data Pengiriman</div>
                    <div class="card-body">
                        <form action="{{ route('store.checkout', ['subdomain' => $website->subdomain]) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small text-muted">Nama Penerima</label>
                                <input type="text" name="customer_name" class="form-control" required placeholder="Budi Santoso">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted">WhatsApp (Aktif)</label>
                                <input type="number" name="customer_whatsapp" class="form-control" required placeholder="08123456789">
                                <small class="text-muted" style="font-size: 11px;">Untuk konfirmasi pesanan.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted">Alamat Lengkap</label>
                                <textarea name="customer_address" class="form-control" rows="3" required placeholder="Jl. Mawar No. 10, Jakarta"></textarea>
                            </div>
                            
                            <hr>
                            
                            <button type="submit" class="btn btn-primary-custom w-100 py-2 fw-bold">
                                Buat Pesanan <i class="bi bi-arrow-right"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-cart-x display-1 text-muted opacity-25"></i>
                <h4 class="mt-3 text-muted">Keranjang Kosong</h4>
                <a href="{{ route('store.home', $website->subdomain) }}" class="btn btn-primary-custom mt-2">Belanja Dulu</a>
            </div>
        @endif

    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Cek apakah di dalam Iframe (Preview Mode)
        if (window.self !== window.top) {
            
            // TARGETKAN SEMUA FORM (Checkout, Update, Delete, Add)
            const allForms = document.querySelectorAll('form');
            
            allForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault(); // Matikan fungsi
                    alert('⚠️ Mode Preview: Interaksi keranjang dinonaktifkan di editor ini.');
                });

                // Matikan tombol submit di dalamnya agar terlihat disabled
                const btn = form.querySelector('button, input[type="submit"]');
                if(btn) {
                    btn.disabled = true;
                    btn.style.cursor = 'not-allowed';
                    btn.title = "Dinonaktifkan dalam mode preview";
                }
                
                // Matikan input qty
                const input = form.querySelector('input[name="qty"]');
                if(input) input.disabled = true;
            });
        }
    });
</script>
@endsection