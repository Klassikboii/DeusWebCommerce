@extends('layouts.' . ($website->active_template ?? 'modern'))

@section('content')
<body class="bg-light">
<style>
        :root { --primary-color: {{ $website->primary_color }}; }
        .btn-primary-custom { background-color: var(--primary-color); border-color: var(--primary-color); color: white; }
    </style>
    <div class="container py-5" style="max-width: 900px;">
        <div class="mb-4">
            <a href="{{ route('store.home', $website->active_domain) }}" class="text-decoration-none text-muted">
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
                                                <form action="{{ route('store.cart.remove', ['subdomain' => $website->active_domain, 'id' => $id]) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0">
                                                        <i class="bi bi-x-circle fs-5"></i>
                                                    </button>
                                                </form>

                                                <div>
                                                    <div class="fw-bold">{{ $details['name'] }}</div>
                                                    <small class="text-muted">Rp {{ number_format($details['price']) }}</small>
                                                    <small class="text-muted">SKU: {{ $details['sku'] ?? 'SKU: -' }}</small>
                                                </div>
                                            </div>
                                        </td>

                                       {{-- Update Qty Form --}}
                                        <td class="align-middle text-center" style="width: 140px;">
                                             <form action="{{ route('store.cart.update', ['subdomain' => $website->active_domain]) }}" method="POST" class="d-flex align-items-center gap-2 justify-content-center">
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
                                             
                                          {{-- FORM CHECKOUT --}}
                                <div class="card border-0 shadow-sm bg-light">
                                    <div class="card-body">
                                        <h5 class="fw-bold mb-3">Informasi Pengiriman</h5>
                                        
                                        <form action="{{ route('store.checkout', $website->active_domain) }}" method="POST">
                                            @csrf
                                            
                                            {{-- Nama & WA (Tetap) --}}
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Nama Penerima</label>
                                                <input type="text" name="customer_name" class="form-control" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">WhatsApp</label>
                                                <input type="number" name="customer_whatsapp" class="form-control" placeholder="08..." required>
                                            </div>

                                            {{-- ALAMAT LENGKAP (Textarea) --}}
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Alamat Lengkap (Jalan, No Rumah, RT/RW)</label>
                                                <textarea name="customer_address" class="form-control" rows="2" placeholder="Jl. Mawar No. 10..." required></textarea>
                                            </div>

                                            {{-- PILIH KOTA (Dropdown dari Database Ongkir) --}}
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Kota / Kabupaten Tujuan</label>
                                                <select id="destination_city" name="destination_city" class="form-select select2" required onchange="getShippingRates()">
                                                    <option value="" selected disabled>-- Ketik / Pilih Lokasi --</option>
                                                    @foreach($cities as $city)
                                                        <option value="{{ $city->id }}">{{ $city->type }} {{ $city->name }} - Prov. {{ $city->province->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            {{-- PILIHAN KURIR (Akan diisi oleh Javascript) --}}
                                            <div id="shipping-loading" class="text-center d-none py-2">
                                                <div class="spinner-border spinner-border-sm text-primary" role="status"></div> Mencari ongkir...
                                            </div>
                                            
                                            <div id="shipping-options-container" class="mb-4">
                                                {{-- Radio button kurir muncul disini --}}
                                            </div>

                                            {{-- Input Hidden untuk menyimpan detail ongkir yang dipilih (Agar masuk ke Controller) --}}
                                            <input type="hidden" name="shipping_cost" id="input_shipping_cost" value="0">
                                            <input type="hidden" name="shipping_courier" id="input_shipping_courier" value="">

                                            <hr>

                                            {{-- TOTAL --}}
                                            <div class="d-flex justify-content-between mb-2 text-muted">
                                                <span>Subtotal</span>
                                                <span>Rp {{ number_format($total, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2 text-primary">
                                                <span>Ongkos Kirim</span>
                                                <span id="display-ongkir">Rp 0</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-4 fs-5 fw-bold">
                                                <span>Total Bayar</span>
                                                <span id="display-total">Rp {{ number_format($total, 0, ',', '.') }}</span>
                                            </div>

                                            {{-- Hitung Berat Total (Hidden) --}}
                                            @php
                                                $totalWeight = 0;
                                                foreach($cart as $item) {
                                                    $totalWeight += ($item['weight'] ?? 1000) * $item['quantity'];
                                                }
                                            @endphp
                                            <input type="hidden" id="total_weight" value="{{ $totalWeight }}">
                                            <input type="hidden" id="subtotal_amount" value="{{ $total }}">

                                            {{-- CEK APAKAH TOKO BUKA --}}
                                                @if($website->is_open)
                                                    <button type="submit" id="btn-checkout" class="btn btn-primary w-100 py-3 fw-bold" disabled>
                                                        Lanjut ke Pembayaran <i class="bi bi-arrow-right ms-2"></i>
                                                    </button>
                                                @else
                                                    <div class="alert alert-danger text-center mb-0">
                                                        Toko Sedang Tutup. Silakan kembali lagi nanti.
                                                    </div>
                                                    <button class="btn btn-secondary w-100 py-3 fw-bold mt-2" disabled>
                                                        Checkout Dinonaktifkan
                                                    </button>
                                                @endif
                                        </form>
                                    </div>
                                </div>
                            
                    
                </div>
            </div>
        </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-cart-x display-1 text-muted opacity-25"></i>
                <h4 class="mt-3 text-muted">Keranjang Kosong</h4>
                <a href="{{ route('store.home', $website->active_domain) }}" class="btn btn-primary-custom mt-2">Belanja Dulu</a>
            </div>
        @endif

    </div>

    <script>
        // URL API Cek Ongkir
    const checkShippingUrl = "{{ route('store.cart.checkShipping', $website->active_domain) }}";
    const csrfToken = "{{ csrf_token() }}";

    function formatRupiah(amount) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    }

    function getShippingRates() {
        const city = document.getElementById('destination_city').value;
        const weight = document.getElementById('total_weight').value;
        const container = document.getElementById('shipping-options-container');
        const loading = document.getElementById('shipping-loading');
        const btnCheckout = document.getElementById('btn-checkout');

        // Reset
        container.innerHTML = '';
        loading.classList.remove('d-none');
        btnCheckout.disabled = true;
        updateTotal(0);

        // Fetch API
        fetch(checkShippingUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json' // 🚨 Memaksa Laravel membalas dengan JSON, bukan halaman HTML Error
            },
            body: JSON.stringify({ destination: city, weight: weight })
        })
        .then(async response => {
            const data = await response.json();
            // Jika status HTTP bukan 200 OK, lempar error ke catch tapi bawa pesannya
            if (!response.ok) {
                throw new Error(data.message || 'Server sedang sibuk, coba beberapa saat lagi.');
            }
            return data;
        })
        .then(data => {
            loading.classList.add('d-none');

            if(data.status === 'success') {
                let html = '<label class="form-label small fw-bold text-success"><i class="bi bi-check-circle me-1"></i>Pilih Pengiriman</label>';
                
                data.options.forEach((opt, index) => {
                    html += `
                        <div class="form-check border rounded p-2 mb-2 bg-white shipping-option-hover">
                            <input class="form-check-input ms-1 mt-2" type="radio" name="selected_shipping" 
                                   id="ship_${opt.id}" value="${opt.id}" 
                                   onchange="selectShipping('${opt.courier} ${opt.service}', ${opt.cost})">
                            <label class="form-check-label w-100 ps-2" for="ship_${opt.id}" style="cursor:pointer">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold text-uppercase">${opt.courier} <small class="text-muted text-capitalize">${opt.service}</small></span>
                                    <span class="fw-bold text-primary">Rp ${opt.cost_formatted}</span>
                                </div>
                                <div class="small text-muted"><i class="bi bi-clock me-1"></i> ${opt.estimation}</div>
                            </label>
                        </div>
                    `;
                });
                container.innerHTML = html;
            } else {
                // 🚨 Ini akan muncul jika rute tidak didukung, tapi status server 200 OK
                container.innerHTML = `
                    <div class="alert alert-warning small border-warning">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i> 
                        <strong>Oops!</strong> ${data.message}
                    </div>`;
            }
        })
        .catch(error => {
            loading.classList.add('d-none');
            // 🚨 Ini akan muncul jika API error, timeout, atau beda pulau tapi kurir tidak dukung
            container.innerHTML = `
                <div class="alert alert-danger small border-danger">
                    <i class="bi bi-x-circle-fill text-danger me-2"></i> 
                    Ekspedisi yang dipilih tidak menjangkau area ini. Silakan hubungi Admin toko untuk bantuan pengiriman manual.
                </div>`;
            console.error('Shipping Error:', error.message);
        });
    }

    function selectShipping(courierName, cost) {
        // Update Input Hidden (untuk dikirim ke controller)
        document.getElementById('input_shipping_cost').value = cost;
        document.getElementById('input_shipping_courier').value = courierName;
        
        // Update Tampilan Total
        updateTotal(cost);
        
        // Enable Tombol
        document.getElementById('btn-checkout').disabled = false;
    }

    function updateTotal(shippingCost) {
        const subtotal = parseInt(document.getElementById('subtotal_amount').value);
        const grandTotal = subtotal + shippingCost;

        document.getElementById('display-ongkir').innerText = formatRupiah(shippingCost);
        document.getElementById('display-total').innerText = formatRupiah(grandTotal);
    }
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

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // Aktifkan Select2
        $('.select2').select2({
            placeholder: "-- Ketik / Pilih Lokasi --",
            width: '100%' // Agar lebarnya rapi mengikuti form
        });

        // Paksa fungsi ongkir berjalan otomatis ketika kota dipilih lewat Select2
        $('#destination_city').on('select2:select', function (e) {
            getShippingRates(); 
        });
    });
</script>
@endsection