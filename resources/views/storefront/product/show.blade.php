@extends('layouts.' . ($website->active_template ?? 'simple'))

@section('title', $product->name . ' - ' . $website->site_name)

@section('content')

<style>
    /* 1. Hilangkan panah input number */
    .no-arrow::-webkit-outer-spin-button,
    .no-arrow::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .no-arrow {
        -moz-appearance: textfield;
    }

    /* 2. LOGIKA TAMPILAN CUSTOM (Breakpoint 695px) */
    .desktop-cart-actions { display: none !important; }
    .mobile-cart-actions { display: block !important; }
    .mobile-spacer { display: block !important; }

    @media (min-width: 696px) {
        .desktop-cart-actions { display: flex !important; }
        .mobile-cart-actions { display: none !important; }
        .mobile-spacer { display: none !important; }
    }

    /* 3. STYLING STICKY BAR MOBILE */
    .mobile-cart-actions {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        width: 100%;
        z-index: 9999;
        background-color: white;
        padding: 1rem;
        padding-bottom: max(1rem, env(safe-area-inset-bottom));
        box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.08); 
        border-top: 1px solid #f0f0f0;
    }

    /* STYLING KHUSUS BUNDLING MBA */
    .bundle-card-hover { transition: all 0.2s ease-in-out; }
    .bundle-card-hover:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; border: 1px solid #0d6efd !important; }
    @media (min-width: 992px) { 
        .border-start-lg { border-left: 2px dashed #dee2e6; }
    }
</style>

<div class="container py-5">
    
    {{-- BREADCRUMB --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('store.home') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row g-5">
        {{-- KOLOM KIRI: GAMBAR --}}
        <div class="col-md-6">
            <div class="border rounded overflow-hidden shadow-sm" style="background-color: white;">
                @if($product->image)
                    <img id="main-product-image" src="{{ asset('storage/' . $product->image) }}" class="w-100 object-fit-cover" style="aspect-ratio: 1/1;" alt="{{ $product->name }}">
                @else
                    <div class="d-flex align-items-center justify-content-center bg-light" style="aspect-ratio: 1/1;" id="main-product-image-container">
                        <i class="bi bi-image fs-1 text-muted"></i>
                    </div>
                @endif
            </div>
        </div>

        {{-- KOLOM KANAN: DETAIL --}}
        <div class="col-md-6">
            <div class="ps-lg-4">
                @if($product->category)
                    <span class="badge mb-2" style="background-color: var(--secondary-color); color: white;">{{ $product->category->name }}</span>
                @endif

                <h1 class="fw-bold mb-2">{{ $product->name }}</h1>
                
                {{-- HARGA DINAMIS --}}
                <h3 class="text-primary-custom fw-bold mb-3" id="product-price">
                    @if($product->hasVariants())
                        Rp {{ number_format($product->variants->min('price'), 0, ',', '.') }} 
                        @if($product->variants->min('price') != $product->variants->max('price'))
                            - Rp {{ number_format($product->variants->max('price'), 0, ',', '.') }}
                        @endif
                    @else
                        Rp {{ number_format($product->price, 0, ',', '.') }}
                    @endif
                </h3>

                {{-- DESKRIPSI SINGKAT & STOK --}}
                <div class="mb-4">
                    <p class="text-muted">{{ $product->short_description ?? Str::limit(strip_tags($product->description), 150) }}</p>
                    
                    <span id="product-stock-badge" class="badge {{ $product->stock > 0 ? 'bg-success' : 'bg-danger' }}">
                        <i class="bi {{ $product->stock > 0 ? 'bi-check-circle' : 'bi-x-circle' }} me-1"></i> 
                        <span id="stock-text">
                            @if($product->hasVariants())
                                Stok Total: {{ $product->stock }}
                            @else
                                {{ $product->stock > 0 ? 'Stok Tersedia: ' . $product->stock : 'Stok Habis' }}
                            @endif
                        </span>
                    </span>
                </div>

                {{-- ========================================== --}}
                {{-- INFO HARGA GROSIR (B2B UPSELLING)          --}}
                {{-- ========================================== --}}
                @if($product->wholesalePrices && $product->wholesalePrices->count() > 0)
                    <div class="card border-primary mb-4 shadow-sm" id="wholesale-info-card">
                        <div class="card-header bg-primary bg-opacity-10 text-primary py-2 fw-bold d-flex align-items-center">
                            <i class="bi bi-box-seam me-2"></i> Beli Banyak, Lebih Murah!
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-striped table-borderless mb-0 text-center align-middle" style="font-size: 0.9rem;">
                                <thead>
                                    <tr class="text-muted">
                                        <th scope="col" class="py-2 w-50">Minimal Beli</th>
                                        <th scope="col" class="py-2 w-50">Harga Satuan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $globalWholesales = $product->wholesalePrices->whereNull('product_variant_id')->sortBy('min_qty');
                                    @endphp

                                    @if($globalWholesales->count() > 0)
                                        @foreach($globalWholesales as $wholesale)
                                            <tr class="wholesale-row-global">
                                                <td class="fw-bold py-2">≥ {{ $wholesale->min_qty }} pcs</td>
                                                <td class="text-primary fw-bold py-2">Rp {{ number_format($wholesale->price, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr id="wholesale-placeholder-text">
                                            <td colspan="2" class="py-3 text-muted fst-italic">
                                                Pilih varian produk terlebih dahulu untuk melihat penawaran grosir.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
                {{-- ========================================== --}}
                
                <hr>
                
                {{-- FORM ADD TO CART --}}
                <form action="{{ route('store.cart.add', [ 'id' => $product->id]) }}" method="POST">
                    @csrf
                    {{-- LOGIKA VARIAN --}}
                    @if($product->variants()->where('is_active', true)->count() > 0)
                        <div class="mb-4">
                            <label class="form-label fw-bold">Pilih Varian:</label>
                            <select name="variant_id" id="variant-selector" class="form-select" required>
                                <option value="" selected disabled>-- Pilih Opsi --</option>
                                @foreach($product->variants()->where('is_active', true)->get() as $variant)
                                    <option value="{{ $variant->id }}" 
                                            data-price="{{ $variant->price }}"
                                            data-stock="{{ $variant->stock }}"
                                            data-wholesales="{{ json_encode($variant->wholesalePrices->sortBy('min_qty')->map(function($w) { return ['min_qty' => $w->min_qty, 'price' => $w->price]; })->values()) }}"
                                            data-image="{{ $variant->image ? asset('storage/' . $variant->image) : '' }}">
                                        {{ $variant->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text text-danger d-none" id="variant-error">Mohon pilih varian terlebih dahulu.</div>
                        </div>
                    @endif
                    
                    {{-- DESKTOP ADD TO CART --}}
                    <div class="desktop-cart-actions align-items-center gap-3 mb-4">
                        <div class="input-group" style="width: 140px;">
                            <button class="btn btn-outline-secondary" type="button" onclick="adjustQty(-1)">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="text" oninput="this.value = this.value.replace(/[^0-9]/g, '');" inputmode="numeric" pattern="[0-9]*" name="quantity" class="form-control desktop-qty"  style="text-align: center;" value="1" @guest('customer') disabled @endguest>
                            <button class="btn btn-outline-secondary" type="button" onclick="adjustQty(1)">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                        @auth('customer')
                        <button type="submit" id="desktop-add-btn" class="btn btn-primary btn-lg rounded-pill px-5 add-btn"
                                {{ (!$product->hasVariants() && $product->stock < 1) ? 'disabled' : '' }}>
                            <i class="bi bi-bag-plus "></i> Masukkan Keranjang
                        </button>
                        @else
                            <a href="{{ route('store.login') }}" class="btn btn-outline-primary w-100 py-3 fw-bold" style="border-radius: var(--radius-base); border-width: 2px;">
                                <i class="bi bi-box-arrow-in-right me-2"></i> Masuk untuk Berbelanja
                            </a>
                        @endauth
                    </div>

                    {{-- MOBILE ADD TO CART --}}
                    <div class="mobile-cart-actions">
                        <div class="d-flex gap-2 align-items-center">
                            <div class="input-group" style="width: 120px;">
                                <button class="btn btn-outline-secondary btn-sm" type="button" onclick="adjustQty(-1)">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input type="number" class="form-control text-center mobile-qty no-arrow fw-bold" value="1" min="1" readonly onchange="syncQty(this.value)"> 
                                <button class="btn btn-outline-secondary btn-sm" type="button" onclick="adjustQty(1)">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                            @auth('customer')
                            <button type="submit" id="mobile-add-btn" class="btn btn-primary rounded-pill flex-grow-1 add-btn"
                                    {{ (!$product->hasVariants() && $product->stock < 1) ? 'disabled' : '' }}>
                                <i class="bi bi-bag-plus me-1"></i> Beli
                            </button>
                            @else
                            <a href="{{ route('store.login') }}" class="btn btn-outline-primary w-100 py-3 fw-bold" style="border-radius: var(--radius-base); border-width: 2px;">
                                <i class="bi bi-box-arrow-in-right me-2"></i> Masuk untuk Berbelanja
                            </a>
                            @endauth
                        </div>
                    </div>
                </form>

                <div class="mobile-spacer" style="height: 100px;"></div>

                <hr class="my-4">

                <div>
                    <h5 class="fw-bold mb-3">Deskripsi Produk</h5>
                    <div class="text-muted">
                        {!! nl2br(e($product->description)) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- RAK 1: REKOMENDASI AI (CROSS-SELLING)      --}}
    {{-- ========================================== --}}
    @if($aiProducts->count() > 0)
    <div class="mt-5 pt-5 border-top">
        <h3 class="fw-bold mb-4 text-center">Sering Dibeli Bersamaan</h3>
        <div class="row g-4 justify-content-center">
            @foreach($aiProducts as $related)
                <div class="col-6 col-md-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="position-relative overflow-hidden rounded-top" style="background-color: white;">
                            <a href="{{ route('store.product', [ 'slug' => $related->slug]) }}">
                                @if($related->image)
                                    <img src="{{ asset('storage/' . $related->image) }}" class="card-img-top object-fit-cover" style="aspect-ratio: 1/1;">
                                @else
                                    <div class="bg-light card-img-top d-flex align-items-center justify-content-center" style="aspect-ratio: 1/1;"><i class="bi bi-image text-muted"></i></div>
                                @endif
                            </a>
                        </div>
                        <div class="card-body text-center p-3">
                            <h6 class="card-title text-truncate">
                                <a href="{{ route('store.product', ['slug' => $related->slug]) }}" class="text-decoration-none text-dark">
                                    {{ $related->name }}
                                </a>
                            </h6>
                            <p class="text-primary fw-bold mb-0">Rp {{ number_format($related->price, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ========================================== --}}
    {{-- RAK 2: KATEGORI SAMA (UP-SELLING)          --}}
    {{-- ========================================== --}}
    @if($categoryProducts->count() > 0)
    <div class="mt-5 pt-5 border-top">
        <h3 class="fw-bold mb-4 text-center">Produk Terkait</h3>
        <div class="row g-4 justify-content-center">
            @foreach($categoryProducts as $related)
                <div class="col-6 col-md-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="position-relative overflow-hidden rounded-top" style="background-color: white;">
                            <a href="{{ route('store.product', [ 'slug' => $related->slug]) }}">
                                @if($related->image)
                                    <img src="{{ asset('storage/' . $related->image) }}" class="card-img-top object-fit-cover" style="aspect-ratio: 1/1;">
                                @else
                                    <div class="bg-light card-img-top d-flex align-items-center justify-content-center" style="aspect-ratio: 1/1;"><i class="bi bi-image text-muted"></i></div>
                                @endif
                            </a>
                        </div>
                        <div class="card-body text-center p-3">
                            <h6 class="card-title text-truncate">
                                <a href="{{ route('store.product', ['slug' => $related->slug]) }}" class="text-decoration-none text-dark">
                                    {{ $related->name }}
                                </a>
                            </h6>
                            <p class="text-primary fw-bold mb-0">Rp {{ number_format($related->price, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <hr>
    {{-- =================================================== --}}
    {{-- 🤖 SECTION AI BUNDLING: SERING DIBELI BERSAMAAN     --}}
    {{-- =================================================== --}}
    @if(isset($bundles) && $bundles->count() > 0)
    <div class="container my-5 py-5 border-top border-bottom rounded-3 shadow-sm" style="background-color: #f8faff;">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <span class="badge bg-primary bg-opacity-10 text-primary mb-2 px-3 py-2 rounded-pill"><i class="bi bi-stars me-1"></i> Penawaran Cerdas</span>
                <h4 class="fw-bold">Sering Dibeli Bersamaan</h4>
                <p class="text-muted small">Kombinasi produk yang paling disukai oleh pelanggan kami.</p>
            </div>
        </div>

        <div class="row align-items-center justify-content-center g-3">
            <div class="col-12 col-lg-8">
                <div class="d-flex flex-wrap align-items-center justify-content-center gap-3">
                    
                    <div class="text-center" style="width: 140px;">
                        <div class="card border-primary shadow-sm h-100">
                            <img src="{{ asset('storage/' . $product->image) }}" class="card-img-top p-2 rounded" alt="{{ $product->name }}" style="aspect-ratio: 1/1; object-fit: cover;">
                            <div class="card-body p-2 bg-primary bg-opacity-10 border-top border-primary">
                                <p class="small fw-bold mb-0 text-truncate text-primary" title="{{ $product->name }}">Barang Ini</p>
                            </div>
                        </div>
                        <div class="mt-2 text-dark fw-bold small">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                    </div>

                    @php 
                        $bundleOriginalPrice = $product->price; 
                        $bundleProductIds = [];
                        
                        foreach($bundles as $bundle) {
                            $recProduct = $bundle->recommendedProduct; 
                            $bundleOriginalPrice += $recProduct->price;
                            $bundleProductIds[] = $recProduct->id;
                        }

                        $bundleFinalPrice = $bundleOriginalPrice;
                        if(isset($isDiscountBundle) && $isDiscountBundle) {
                            $discountAmount = $bundleOriginalPrice * ($bundleDiscountPercentage / 100);
                            $bundleFinalPrice = $bundleOriginalPrice - $discountAmount;
                        }
                    @endphp

                    @foreach($bundles as $bundle)
                        @php $recProduct = $bundle->recommendedProduct; @endphp
                        
                        <div class="text-center text-muted"><i class="bi bi-plus-lg fs-4"></i></div>
                        
                        <div class="text-center" style="width: 140px;">
                            <a href="{{ route('store.product', ['slug' => $recProduct->slug]) }}" class="text-decoration-none text-dark">
                                <div class="card border-0 shadow-sm h-100 position-relative bundle-card-hover">
                                    <img src="{{ asset('storage/' . $recProduct->image) }}" class="card-img-top p-2 rounded" alt="{{ $recProduct->name }}" style="aspect-ratio: 1/1; object-fit: cover;">
                                    <div class="card-body p-2 border-top">
                                        <p class="small text-muted mb-0 text-truncate" title="{{ $recProduct->name }}">{{ $recProduct->name }}</p>
                                        <p class="small text-muted mb-0 text-truncate" title="{{ $recProduct->name }}">Stok: {{ $recProduct->stock }}</p>
                                    </div>
                                </div>
                            </a>
                            <div class="mt-2 text-dark fw-bold small">Rp {{ number_format($recProduct->price, 0, ',', '.') }}</div>
                        </div>
                    @endforeach

                </div>
            </div>

            <div class="col-auto text-center d-none d-lg-block">
                <i class="bi bi-pause fs-2 text-muted" style="transform: rotate(90deg); display: inline-block;"></i>
            </div>

            <div class="col-12 col-lg-3 text-center text-lg-start mt-4 mt-lg-0 ps-lg-4 border-start-lg">
                <p class="text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Total Harga Paket:</p>
                
                @if(isset($isDiscountBundle) && $isDiscountBundle)
                    <div class="mb-1 d-flex align-items-center justify-content-center justify-content-lg-start">
                        <span class="text-decoration-line-through text-muted small me-2">Rp {{ number_format($bundleOriginalPrice, 0, ',', '.') }}</span>
                        <span class="badge bg-danger rounded-pill shadow-sm">Hemat {{ $bundleDiscountPercentage }}%</span>
                    </div>
                @endif

                <h3 class="fw-bold text-primary mb-3">Rp {{ number_format($bundleFinalPrice, 0, ',', '.') }}</h3>
                
                @auth('customer')
                        <button onclick="addBundleToCart({{ $product->id }}, {{ json_encode($bundleProductIds) }}, {{ isset($isDiscountBundle) && $isDiscountBundle ? 'true' : 'false' }}, {{ $bundleDiscountPercentage ?? 0 }})" class="btn btn-primary w-100 fw-bold shadow-sm py-2" id="btn-add-bundle">
                            <i class="bi bi-cart-plus me-1"></i> Beli {{ count($bundleProductIds) + 1 }} Barang
                        </button>
                        @else
                            <a href="{{ route('store.login') }}" class="btn btn-outline-primary w-100 py-3 fw-bold" style="border-radius: var(--radius-base); border-width: 2px;">
                                <i class="bi bi-box-arrow-in-right me-2"></i> Masuk untuk Berbelanja
                            </a>
                        @endauth
                
                @if(isset($isDiscountBundle) && $isDiscountBundle)
                    <p class="small text-danger fw-bold mt-2 mb-0"><i class="bi bi-fire me-1"></i>Kombinasi Sempurna (Promo)</p>
                @else
                    <p class="small text-success mt-2 mb-0"><i class="bi bi-check2-circle me-1"></i>Kombinasi teruji algoritma</p>
                @endif
            </div>
        </div>
    </div>
    @endif

</div>

<script>
    // FUNGSI SINKRONISASI JUMLAH (Dipindahkan ke luar agar bisa diakses global)
    window.syncQty = function(val) {
        const desktopInput = document.querySelector('.desktop-qty');
        const mobileInput = document.querySelector('.mobile-qty');
        
        let maxVal = parseInt(desktopInput.max) || 999;
        let cleanVal = parseInt(val);

        if (isNaN(cleanVal) || cleanVal < 1) cleanVal = 1;
        if (cleanVal > maxVal) cleanVal = maxVal;

        desktopInput.value = cleanVal;
        mobileInput.value = cleanVal;
    };

    window.adjustQty = function(amount) {
        const desktopInput = document.querySelector('.desktop-qty');
        let currentVal = parseInt(desktopInput.value) || 0;
        window.syncQty(currentVal + amount); 
    };

    document.addEventListener("DOMContentLoaded", function() {
        
        // Iframe Fix
        function inIframe() { try { return window.self !== window.top; } catch (e) { return true; } }
        if (inIframe()) {
            const style = document.createElement('style');
            style.innerHTML = ` .mobile-cart-actions { bottom: 30px !important; } `; 
            document.head.appendChild(style);
        }

        const variantSelector = document.getElementById('variant-selector');
        const priceElement = document.getElementById('product-price');
        const stockBadge = document.getElementById('product-stock-badge');
        const mainImageElement = document.getElementById('main-product-image');
        const defaultImageSrc = mainImageElement ? mainImageElement.src : '';
        const addToCartBtns = document.querySelectorAll('.add-btn');
        const quantityInputs = document.querySelectorAll('.desktop-qty, .mobile-qty');

        const formatRupiah = (number) => {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
        }

        if (variantSelector) {
            // Disable tombol beli di awal jika produk bervarian
            addToCartBtns.forEach(btn => {
                btn.disabled = true;
                if(btn.id === 'desktop-add-btn') btn.innerHTML = 'Pilih Varian';
                if(btn.id === 'mobile-add-btn') btn.innerHTML = 'Pilih Varian';
            });

            // Logika ketika varian diubah
            variantSelector.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const price = parseFloat(selectedOption.getAttribute('data-price'));
                const stock = parseInt(selectedOption.getAttribute('data-stock'));
                const variantImage = selectedOption.getAttribute('data-image'); 
                
                // --- 1. UPDATE TABEL GROSIR DINAMIS ---
                const wholesaleDataStr = selectedOption.getAttribute('data-wholesales');
                const wholesaleCard = document.getElementById('wholesale-info-card');
                
                if (wholesaleCard && wholesaleDataStr) {
                    try {
                        const wholesales = JSON.parse(wholesaleDataStr);
                        const tbody = wholesaleCard.querySelector('tbody');
                        
                        if (wholesales.length > 0) {
                            let html = '';
                            wholesales.forEach(w => {
                                html += `
                                    <tr>
                                        <td class="fw-bold py-2">≥ ${w.min_qty} pcs</td>
                                        <td class="text-primary fw-bold py-2">${formatRupiah(w.price)}</td>
                                    </tr>
                                `;
                            });
                            tbody.innerHTML = html;
                        } 
                        else if(document.getElementById('wholesale-placeholder-text')) {
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="2" class="py-3 text-muted fst-italic text-center">
                                        Tidak ada harga grosir spesifik untuk varian ini.
                                    </td>
                                </tr>
                            `;
                        }
                    } catch(e) { console.error("Error parsing wholesale data"); }
                }

                // --- 2. UPDATE HARGA, STOK, DAN GAMBAR ---
                priceElement.innerText = formatRupiah(price);
                quantityInputs.forEach(input => { input.max = stock; input.value = 1; });

                if (mainImageElement) {
                    mainImageElement.src = (variantImage && variantImage !== '') ? variantImage : defaultImageSrc;
                }

                // --- 3. UPDATE TOMBOL CART ---
                if (stock > 0) {
                    stockBadge.className = 'badge bg-success';
                    stockBadge.innerHTML = `<i class="bi bi-check-circle me-1"></i> Stok: ${stock}`;
                    
                    addToCartBtns.forEach(btn => {
                        btn.disabled = false;
                        if(btn.id === 'desktop-add-btn') btn.innerHTML = `<i class="bi bi-bag-plus me-2"></i> Masukkan Keranjang`;
                        if(btn.id === 'mobile-add-btn') btn.innerHTML = `<i class="bi bi-bag-plus me-1"></i> Beli`;
                    });
                } else {
                    stockBadge.className = 'badge bg-danger';
                    stockBadge.innerHTML = `<i class="bi bi-x-circle me-1"></i> Habis`;
                    
                    addToCartBtns.forEach(btn => {
                        btn.disabled = true;
                        btn.innerText = "Stok Habis";
                    });
                }
            });
        }
    });

    // FUNGSI BUNDLING
    window.addBundleToCart = async function(mainProductId, bundleProductIds, isDiscount, discountPercentage) {
        let variantId = null;
        const variantSelector = document.getElementById('variant-selector');
        if (variantSelector) {
            variantId = variantSelector.value;
            if (!variantId) {
                alert('Mohon pilih varian produk utama (Warna/Ukuran) terlebih dahulu!');
                variantSelector.focus();
                return;
            }
        }

        const btn = document.getElementById('btn-add-bundle');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memasukkan...';
        btn.disabled = true;

        try {
            let response = await fetch("{{ route('store.cart.add_bundle', ['subdomain' => $website->subdomain]) }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    main_product_id: mainProductId,
                    variant_id: variantId,
                    bundle_product_ids: bundleProductIds,
                    is_discount: isDiscount,
                    discount_percentage: discountPercentage
                })
            });

            let data = await response.json();
            
            if (response.ok && data.status === 'success') {
                btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Berhasil Masuk!';
                btn.classList.replace('btn-primary', 'btn-success');
                setTimeout(() => {
                    window.location.href = "{{ route('store.cart', ['subdomain' => $website->subdomain]) }}";
                }, 1000);
            } else {
                alert(data.message || 'Gagal menambahkan paket.');
                btn.innerHTML = '<i class="bi bi-cart-plus me-1"></i> Coba Lagi';
                btn.disabled = false;
            }
        } catch (error) {
            console.error(error);
            alert('Terjadi kesalahan jaringan. Silakan coba lagi.');
            btn.innerHTML = '<i class="bi bi-cart-plus me-1"></i> Coba Lagi';
            btn.disabled = false;
        }
    };
</script>
@endsection