@extends('layouts.' . ($website->active_template ?? 'modern'))

@section('content')
<body class="bg-light">
<style>
        :root { --primary-color: {{ $website->primary_color }}; }
        .btn-primary-custom { background-color: var(--primary-color); border-color: var(--primary-color); color: white; }
    </style>
    <div class="container py-5" style="max-width: 900px;">
        <div class="mb-4">
            <a href="{{ route('store.home') }}" class="text-decoration-none text-muted">
                <i class="bi bi-arrow-left"></i> Kembali Belanja
            </a>
            <h2 class="fw-bold mt-2">Keranjang & Checkout</h2>
        </div>
        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
     @if (session('error'))
         <div class="alert alert-danger alert-dismissible fade show" role="alert">
             {{ session('error') }}
             <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
         </div>
     @endif
    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
            <strong><i class="bi bi-exclamation-triangle-fill me-2"></i> Perhatian:</strong> {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

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
                                                <form action="{{ route('store.cart.remove', [ 'id' => $id]) }}" method="POST">
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
                                             <form action="{{ route('store.cart.update') }}" method="POST" class="d-flex align-items-center gap-2 justify-content-center">
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
                                    <td colspan="2" class="ps-4 py-3 fw-bold text-uppercase text-muted">Total Barang</td>
                                    <td class="pe-4 py-3 text-end fw-bold fs-5 text-primary " id="product-total">
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
                                        
                                        <form action="{{ route('store.checkout') }}" method="POST">
                                            @csrf
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Nama Penerima</label>
                                                <input type="text" name="customer_name" 
                                                    value="{{ auth('customer')->check() ? auth('customer')->user()->name : old('customer_name') }}" 
                                                    class="form-control" required>
                                            </div>
                                            {{-- Input WhatsApp --}}
                                                <div class="mb-3">
                                                    <label class="form-label">Nomor WhatsApp</label>
                                                    <input type="number" name="customer_whatsapp" 
                                                        value="{{ auth('customer')->check() ? auth('customer')->user()->whatsapp : old('customer_whatsapp') }}" 
                                                        class="form-control" required placeholder="Contoh: 08123456789">
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
                                            <div class="mb-4">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <label class="form-label fw-bold mb-0">Kode Voucher / Promo</label>
                                                    
                                                    @if(count($availableVouchers) > 0)
                                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#voucherModal">
                                                            Lihat Promo <span class="badge bg-danger ms-1">{{ count($availableVouchers) }}</span>
                                                        </button>
                                                    @endif
                                                </div>

                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="voucherCodeInput" placeholder="Ketik atau pilih voucher..." 
                                                        value="{{ session("applied_voucher_{$website->id}")['code'] ?? '' }}" 
                                                        {{ session()->has("applied_voucher_{$website->id}") ? 'readonly' : '' }}>
                                                    
                                                    <button class="btn btn-primary" id="btnApplyVoucher" type="button" 
                                                            style="display: {{ session()->has("applied_voucher_{$website->id}") ? 'none' : 'block' }};">
                                                        Gunakan
                                                    </button>
                                                    <button class="btn btn-danger" id="btnCancelVoucher" type="button" 
                                                            style="display: {{ session()->has("applied_voucher_{$website->id}") ? 'block' : 'none' }};">
                                                        Batalkan
                                                    </button>
                                                </div>
                                                <small id="voucherMessage" class="d-block mt-2"></small>
                                            </div>

                                            <div class="modal fade" id="voucherModal" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-light">
                                                            <h6 class="modal-title fw-bold">Pilih Voucher Tersedia</h6>
                                                            <button type="button" class="btn-close" id="closeVoucherModalBtn" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body p-2">
                                                            @forelse($availableVouchers as $v)
                                                                <div class="card mb-2 border-1 shadow-sm voucher-card">
                                                                    <div class="card-body p-3 d-flex justify-content-between align-items-center">
                                                                        <div>
                                                                            <h6 class="fw-bold mb-1 text-primary">{{ $v->code }}</h6>
                                                                            <p class="mb-1 text-sm">
                                                                                Diskon {{ $v->discount_type == 'percent' ? $v->discount_value.'%' : 'Rp '.number_format($v->discount_value,0,',','.') }}
                                                                                @if($v->min_purchase > 0) <br><small class="text-muted">Min. Blj Rp {{ number_format($v->min_purchase,0,',','.') }}</small> @endif
                                                                            </p>
                                                                            @if($v->target_rfm_segment)
                                                                                <span class="badge bg-warning text-dark" style="font-size: 0.7rem;">Khusus {{ $v->target_rfm_segment }}</span>
                                                                            @endif
                                                                        </div>
                                                                        <button type="button" class="btn btn-sm btn-outline-primary px-3" 
                                                                                onclick="selectVoucherFromModal('{{ $v->code }}')">Pakai</button>
                                                                    </div>
                                                                </div>
                                                            @empty
                                                                <div class="text-center text-muted p-4">Tidak ada promo saat ini.</div>
                                                            @endforelse
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            
                                            <hr>

                                            {{-- TOTAL --}}
                                            <div class="d-flex justify-content-between mb-2 text-muted">
                                                <span>Subtotal</span>
                                                <span>Rp {{ number_format($total, 0, ',', '.') }}</span>
                                            </div>
                                            <!-- ELEMEN TOTAL YANG AKAN BERUBAH -->
                                            <div class="d-flex justify-content-between mt-2 text-success" id="discount-row" style="display: none !important;">
                                                <span>Diskon Voucher</span>
                                                <span id="discount-amount">-Rp 0</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2 text-primary">
                                                <span>Ongkos Kirim</span>
                                                <span id="display-ongkir">Rp 0</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-4 fs-5 fw-bold" id="grand-total">
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
                                            <!-- 🚨 TAMBAHKAN BARIS INI: -->
                                            <input type="hidden" id="discount_amount_val" value="0">

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
                <a href="{{ route('store.home') }}" class="btn btn-primary-custom mt-2">Belanja Dulu</a>
            </div>
        @endif

    </div>
<script>
    var checkShippingUrl = "{{ route('store.cart.checkShipping') }}";
    const csrfToken = "{{ csrf_token() }}";

    function formatRupiah(amount) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    }

    // 🚨 FUNGSI SAPU JAGAT: Menghitung total kapanpun kurir/voucher diubah
    function calculateGrandTotal() {
        let subtotal = parseInt(document.getElementById('subtotal_amount').value) || 0;
        let shipping = parseInt(document.getElementById('input_shipping_cost').value) || 0;
        let discount = parseInt(document.getElementById('discount_amount_val').value) || 0;

        let grandTotal = subtotal + shipping - discount;
        
        // Cegah total menjadi minus jika diskon lebih besar dari subtotal
        // (Tetap biarkan pembeli bayar ongkirnya)
        if (subtotal - discount < 0) {
            grandTotal = shipping; 
        }

        document.getElementById('display-total').innerText = formatRupiah(grandTotal);
    }

   // ==========================================
    // LOGIKA VOUCHER
    // ==========================================
    document.getElementById('btnApplyVoucher').addEventListener('click', function() {
        
        // 🚨 MENGGUNAKAN ID YANG BARU (voucherCodeInput & voucherMessage)
        let codeInput = document.getElementById('voucherCodeInput');
        let msgBox = document.getElementById('voucherMessage');
        
        // Anti-error check: memastikan elemennya ada
        if(!codeInput || !msgBox) {
            console.error("Elemen form voucher tidak ditemukan!");
            return;
        }

        let code = codeInput.value;
        
        if(!code) {
            msgBox.innerHTML = "Masukkan kode voucher!"; 
            msgBox.className = "text-danger mt-1";
            return;
        }

        msgBox.innerHTML = "Memeriksa...";
        msgBox.className = "text-info mt-1";

        fetch("{{ route('store.cart.applyVoucher') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ voucher_code: code })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                msgBox.innerHTML = data.message;
                msgBox.className = "text-success mt-1 fw-bold";
                
                // Munculkan baris diskon di struk
                document.getElementById('discount-row').style.setProperty('display', 'flex', 'important');
                document.getElementById('discount-amount').innerText = data.discount_formatted;
                
                // 🚨 KUNCI INPUT & TUKAR TOMBOL (Gunakan -> Batalkan)
                codeInput.readOnly = true;
                document.getElementById('btnApplyVoucher').style.display = 'none';
                document.getElementById('btnCancelVoucher').style.display = 'block';

                // Simpan nominal diskon ke hidden input & hitung ulang total
                document.getElementById('discount_amount_val').value = data.discount_amount;
                calculateGrandTotal();
                
            } else {
                msgBox.innerHTML = data.message;
                msgBox.className = "text-danger mt-1";
                document.getElementById('discount-row').style.setProperty('display', 'none', 'important');
                
                // Reset diskon jika gagal
                document.getElementById('discount_amount_val').value = 0;
                calculateGrandTotal();
            }
        })
        .catch(error => {
            msgBox.innerHTML = "Terjadi kesalahan sistem saat mengecek voucher.";
            msgBox.className = "text-danger mt-1";
            console.error(error);
        });
    });
    // ==========================================
    // LOGIKA ONGKOS KIRIM
    // ==========================================
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
        
        // Kembalikan ongkir ke 0 dan hitung ulang
        document.getElementById('input_shipping_cost').value = 0;
        document.getElementById('display-ongkir').innerText = formatRupiah(0);
        calculateGrandTotal();

        fetch(checkShippingUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json' 
            },
            body: JSON.stringify({ destination: city, weight: weight })
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Server sedang sibuk.');
            return data;
        })
        .then(data => {
            loading.classList.add('d-none');

            if(data.status === 'success') {
                let html = '<label class="form-label small fw-bold text-success"><i class="bi bi-check-circle me-1"></i>Pilih Pengiriman</label>';
                
                data.options.forEach((opt) => {
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
                container.innerHTML = `<div class="alert alert-warning small border-warning"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i> ${data.message}</div>`;
            }
        })
        .catch(error => {
            loading.classList.add('d-none');
            container.innerHTML = `<div class="alert alert-danger small border-danger"><i class="bi bi-x-circle-fill text-danger me-2"></i> Ekspedisi tidak menjangkau area ini.</div>`;
        });
    }

    function selectShipping(courierName, cost) {
        document.getElementById('input_shipping_cost').value = cost;
        document.getElementById('input_shipping_courier').value = courierName;
        
        document.getElementById('display-ongkir').innerText = formatRupiah(cost);
        
        // 🚨 HITUNG ULANG TOTAL (Sekarang dia akan otomatis memperhitungkan diskon yang sudah ada)
        calculateGrandTotal();
        
        document.getElementById('btn-checkout').disabled = false;
    }

    document.addEventListener("DOMContentLoaded", function() {
        if (window.self !== window.top) {
            const allForms = document.querySelectorAll('form');
            allForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault(); 
                    alert('⚠️ Mode Preview: Interaksi keranjang dinonaktifkan di editor ini.');
                });
                const btn = form.querySelector('button, input[type="submit"]');
                if(btn) {
                    btn.disabled = true;
                    btn.style.cursor = 'not-allowed';
                }
                const input = form.querySelector('input[name="qty"]');
                if(input) input.disabled = true;
            });
        }
    });
</script>

<script>
    // ==========================================
    // 1. FUNGSI PILIH VOUCHER DARI MODAL
    // ==========================================
    function selectVoucherFromModal(code) {
     // 1. Isi inputan
     document.getElementById('voucherCodeInput').value = code;

     // 2. Tutup modal dengan cara mengklik tombol silangnya
     document.getElementById('closeVoucherModalBtn').click();

     // 3. Otomatis tekan tombol "Gunakan"
     document.getElementById('btnApplyVoucher').click();
 }

    // ==========================================
    // 2. FUNGSI BATALKAN VOUCHER (AJAX)
    // ==========================================
    // Bungkus dengan DOMContentLoaded agar Javascript menunggu HTML selesai digambar!
    document.addEventListener("DOMContentLoaded", function() {
        
        let btnCancel = document.getElementById('btnCancelVoucher');
        
        // Pengecekan keamanan (Mencegah error 'null')
        if (btnCancel) {
            btnCancel.addEventListener('click', function() {
                let btn = this;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>...';
                btn.disabled = true;

                fetch('{{ route("store.cart.remove_voucher") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    btn.innerHTML = 'Batalkan';
                    btn.disabled = false;

                    if(data.success) {
                        // Cara paling aman dan instan untuk me-reset total diskon & ongkir
                        window.location.reload(); 
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    btn.innerHTML = 'Batalkan';
                    btn.disabled = false;
                    alert('Terjadi kesalahan koneksi jaringan.');
                });
            });
        }

    });
</script>
    <script>
        // URL API Cek Ongkir
   
    

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