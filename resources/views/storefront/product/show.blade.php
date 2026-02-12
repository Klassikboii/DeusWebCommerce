@extends('layouts.modern')

@section('title', $product->name . ' - ' . $website->site_name)

@section('content')
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
                
                {{-- HARGA DINAMIS (Diberi ID agar bisa diubah JS) --}}
                <h3 class="text-primary-custom fw-bold mb-3" id="product-price">
                    @if($product->hasVariants())
                        {{-- Tampilkan Range Harga: Rp 50.000 - Rp 80.000 --}}
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
                    
                    {{-- STOK DINAMIS --}}
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
                    
                    {{-- === LOGIKA VARIAN (BARU) === --}}
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
                    {{-- ============================ --}}

                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="input-group" style="width: 130px;">
                            <button class="btn btn-outline-secondary" type="button" onclick="this.parentNode.querySelector('input[type=number]').stepDown()">-</button>
                            <input type="number" name="quantity" class="form-control text-center" value="1" min="1" max="{{ $product->stock }}" id="quantity-input">
                            <button class="btn btn-outline-secondary" type="button" onclick="this.parentNode.querySelector('input[type=number]').stepUp()">+</button>
                        </div>
                        
                        {{-- Tombol disabled jika stok 0 (atau jika varian belum dipilih nanti dihandle JS) --}}
                        <button type="submit" id="add-to-cart-btn" class="btn btn-primary btn-lg rounded-pill px-5" 
                                {{ (!$product->hasVariants() && $product->stock < 1) ? 'disabled' : '' }}>
                            <i class="bi bi-bag-plus me-2"></i> Masukkan Keranjang
                        </button>
                    </div>
                </form>

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
    document.addEventListener("DOMContentLoaded", function() {
        const variantSelector = document.getElementById('variant-selector');
        const priceElement = document.getElementById('product-price');
        const stockBadge = document.getElementById('product-stock-badge');
        const stockText = document.getElementById('stock-text');
        const addToCartBtn = document.getElementById('add-to-cart-btn');
        const quantityInput = document.getElementById('quantity-input');
        
        // Format Rupiah Helper
        const formatRupiah = (number) => {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
        }

        if (variantSelector) {
            // Awal: Disable tombol Add to Cart sampai user milih varian
            addToCartBtn.disabled = true;
            addToCartBtn.innerText = "Pilih Varian Dulu";

            variantSelector.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const price = parseFloat(selectedOption.getAttribute('data-price'));
                const stock = parseInt(selectedOption.getAttribute('data-stock'));

                // 1. Update Harga
                priceElement.innerText = formatRupiah(price);

                // 2. Update Stok
                if (stock > 0) {
                    stockBadge.className = 'badge bg-success';
                    stockBadge.innerHTML = `<i class="bi bi-check-circle me-1"></i> Stok: ${stock}`;
                    
                    addToCartBtn.disabled = false;
                    addToCartBtn.innerHTML = `<i class="bi bi-bag-plus me-2"></i> Masukkan Keranjang`;
                    quantityInput.max = stock; // Batasi input jumlah sesuai stok varian
                    quantityInput.value = 1;   // Reset ke 1
                } else {
                    stockBadge.className = 'badge bg-danger';
                    stockBadge.innerHTML = `<i class="bi bi-x-circle me-1"></i> Stok Habis`;
                    
                    addToCartBtn.disabled = true;
                    addToCartBtn.innerText = "Stok Habis";
                }
            });
        }
    });
</script>
@endsection