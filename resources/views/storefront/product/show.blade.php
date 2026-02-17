@extends('layouts.modern')

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
    
    /* Default: Tampilan Mobile (Aktif di layar < 696px) */
    .desktop-cart-actions { display: none !important; }
    .mobile-cart-actions { display: block !important; }
    .mobile-spacer { display: block !important; }

    /* Jika layar LEBIH BESAR dari 695px -> Pindah ke Desktop Mode */
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
        
        /* Auto Extend ke Bawah: Padding Safe Area + Shadow */
        padding: 1rem;
        padding-bottom: max(1rem, env(safe-area-inset-bottom)); /* Support Poni HP */
        
        box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.08); /* Shadow halus ke atas */
        border-top: 1px solid #f0f0f0;
    }
</style>

<div class="container py-5">
    
    {{-- BREADCRUMB --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('store.home', $website->subdomain) }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row g-5">
        {{-- KOLOM KIRI: GAMBAR --}}
        <div class="col-md-6">
            <div class="border rounded overflow-hidden shadow-sm bg-white">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" class="w-100 object-fit-cover" style="aspect-ratio: 1/1;" alt="{{ $product->name }}">
                @else
                    <div class="d-flex align-items-center justify-content-center bg-light" style="aspect-ratio: 1/1;">
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

                <hr>

                {{-- FORM ADD TO CART --}}
                <form action="{{ route('store.cart.add', ['subdomain' => $website->subdomain, 'id' => $product->id]) }}" method="POST">
                    @csrf

                    {{-- LOGIKA VARIAN --}}
                    @if($product->hasVariants())
                        <div class="mb-4">
                            <label class="form-label fw-bold">Pilih Varian:</label>
                            <select name="variant_id" id="variant-selector" class="form-select" required>
                                <option value="" selected disabled>-- Pilih Opsi --</option>
                                @foreach($product->variants as $variant)
                                    <option value="{{ $variant->id }}" 
                                            data-price="{{ $variant->price }}"
                                            data-stock="{{ $variant->stock }}">
                                        {{ $variant->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text text-danger d-none" id="variant-error">Mohon pilih varian terlebih dahulu.</div>
                        </div>
                    @endif

                    {{-- 
                        === TAMPILAN DESKTOP (> 695px) === 
                        Menggunakan class 'desktop-cart-actions' yang diatur CSS di atas
                    --}}
                    <div class="desktop-cart-actions align-items-center gap-3 mb-4">
                        <div class="input-group" style="width: 140px;">
                            <button class="btn btn-outline-secondary" type="button" onclick="adjustQty(-1)">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="number" name="quantity" class="form-control text-center desktop-qty no-arrow fw-bold" 
                                   value="1" min="1" max="{{ $product->stock }}" onchange="syncQty(this.value)">
                            <button class="btn btn-outline-secondary" type="button" onclick="adjustQty(1)">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                        
                        <button type="submit" id="desktop-add-btn" class="btn btn-primary btn-lg rounded-pill px-5 add-btn"
                                {{ (!$product->hasVariants() && $product->stock < 1) ? 'disabled' : '' }}>
                            <i class="bi bi-bag-plus me-2"></i> Masukkan Keranjang
                        </button>
                    </div>

                    {{-- 
                        === TAMPILAN MOBILE (< 696px) === 
                        Menggunakan class 'mobile-cart-actions' yang Sticky di bawah
                    --}}
                    <div class="mobile-cart-actions">
                        <div class="d-flex gap-2 align-items-center">
                            {{-- Quantity Selector Compact --}}
                            <div class="input-group" style="width: 120px;">
                                <button class="btn btn-outline-secondary btn-sm" type="button" onclick="adjustQty(-1)">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input type="number" class="form-control text-center mobile-qty no-arrow fw-bold" 
                                       value="1" min="1" readonly onchange="syncQty(this.value)"> 
                                <button class="btn btn-outline-secondary btn-sm" type="button" onclick="adjustQty(1)">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>

                            {{-- Tombol Beli --}}
                            <button type="submit" id="mobile-add-btn" class="btn btn-primary rounded-pill flex-grow-1 add-btn"
                                    {{ (!$product->hasVariants() && $product->stock < 1) ? 'disabled' : '' }}>
                                <i class="bi bi-bag-plus me-1"></i> Beli
                            </button>
                        </div>
                    </div>

                </form>

                {{-- Spacer untuk Mobile agar konten tidak ketutup bar --}}
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

    {{-- PRODUK TERKAIT --}}
    @if($relatedProducts->count() > 0)
    <div class="mt-5 pt-5 border-top">
        <h3 class="fw-bold mb-4 text-center">Produk Terkait</h3>
        <div class="row g-4">
            @foreach($relatedProducts as $related)
                <div class="col-6 col-md-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="position-relative overflow-hidden rounded-top">
                            <a href="{{ route('store.product', ['subdomain' => $website->subdomain, 'slug' => $related->slug]) }}">
                                @if($related->image)
                                    <img src="{{ asset('storage/' . $related->image) }}" class="card-img-top object-fit-cover" style="aspect-ratio: 1/1;">
                                @else
                                    <div class="bg-light card-img-top d-flex align-items-center justify-content-center" style="aspect-ratio: 1/1;"><i class="bi bi-image text-muted"></i></div>
                                @endif
                            </a>
                        </div>
                        <div class="card-body text-center p-3">
                            <h6 class="card-title text-truncate">
                                <a href="{{ route('store.product', ['subdomain' => $website->subdomain, 'slug' => $related->slug]) }}" class="text-decoration-none text-dark">
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

</div>

<script>
    // JS DETEKSI EDITOR (Iframe Fix)
    // Jika di dalam editor, naikkan bar sedikit agar tidak tertutup footer editor
    function inIframe() {
        try { return window.self !== window.top; } catch (e) { return true; }
    }
    if (inIframe()) {
        const style = document.createElement('style');
        style.innerHTML = ` .mobile-cart-actions { bottom: 30px !important; } `; 
        // Note: Anda bisa ubah 0px jadi 50px/80px jika toolbar editor menutupi
        document.head.appendChild(style);
    }

    // FUNGSI SINKRONISASI JUMLAH
    function syncQty(val) {
        const desktopInput = document.querySelector('.desktop-qty');
        const mobileInput = document.querySelector('.mobile-qty');
        
        let maxVal = parseInt(desktopInput.max) || 999;
        let cleanVal = parseInt(val);

        if (isNaN(cleanVal) || cleanVal < 1) cleanVal = 1;
        if (cleanVal > maxVal) cleanVal = maxVal;

        desktopInput.value = cleanVal;
        mobileInput.value = cleanVal;
    }

    function adjustQty(amount) {
        const desktopInput = document.querySelector('.desktop-qty');
        let currentVal = parseInt(desktopInput.value) || 0;
        syncQty(currentVal + amount); 
    }

    document.addEventListener("DOMContentLoaded", function() {
        const variantSelector = document.getElementById('variant-selector');
        const priceElement = document.getElementById('product-price');
        const stockBadge = document.getElementById('product-stock-badge');
        
        const addToCartBtns = document.querySelectorAll('.add-btn');
        const quantityInputs = document.querySelectorAll('.desktop-qty, .mobile-qty');
        
        const formatRupiah = (number) => {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
        }

        if (variantSelector) {
            // Disable awal
            addToCartBtns.forEach(btn => {
                btn.disabled = true;
                if(btn.id === 'desktop-add-btn') btn.innerHTML = 'Pilih Varian';
                if(btn.id === 'mobile-add-btn') btn.innerHTML = 'Pilih Varian';
            });

            variantSelector.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const price = parseFloat(selectedOption.getAttribute('data-price'));
                const stock = parseInt(selectedOption.getAttribute('data-stock'));

                priceElement.innerText = formatRupiah(price);
                quantityInputs.forEach(input => { input.max = stock; input.value = 1; });

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
</script>
@endsection