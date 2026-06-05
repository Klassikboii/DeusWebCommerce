{{-- resources/views/storefront/products/partials/product_list.blade.php --}}
@if($products->count() > 0)
    {{-- 🚨 PERBAIKAN: Gunakan row-cols untuk memaksa grid dari level induk! --}}
    {{-- row-cols-2 = Paksa 2 kolom di HP --}}
    {{-- row-cols-md-3 = Paksa 3 kolom di Tablet --}}
    {{-- row-cols-lg-4 = Paksa 4 kolom di Desktop --}}
    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3 g-md-4">
        @foreach($products as $product)
            {{-- Hapus col-6 col-md-4, cukup gunakan class 'col' biasa --}}
            <div class="col">
                <div class="card h-100 border-0 shadow-sm product-card hover-up">
                    <div class="position-relative overflow-hidden rounded-top"  style="background-color: white;">
                        <a href="{{ route('store.product', ['slug' => $product->slug]) }}">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" class="card-img-top object-fit-cover" style="aspect-ratio: 1/1;" alt="{{ $product->name }}">
                            @else
                                <div class="bg-light card-img-top d-flex align-items-center justify-content-center" style="aspect-ratio: 1/1;">
                                    <i class="bi bi-image text-muted fs-1"></i>
                                </div>
                            @endif
                        </a>
                        @if($product->stock <= 0)
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-danger" style="font-size: 0.7rem;">Habis</span>
                            </div>
                        @endif
                    </div>
                    <div class="card-body p-2 p-md-3 text-center">
                        @if($product->category)
                            <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">{{ $product->category->name }}</small>
                        @endif
                        <h6 class="card-title text-truncate mb-1 mb-md-2" style="font-size: 0.9rem;">
                            <a href="{{ route('store.product', ['slug' => $product->slug]) }}" class="text-decoration-none text-dark stretched-link">
                                {{ $product->name }}
                            </a>
                        </h6>
                        <p class="text-primary fw-bold mb-0" style="font-size: 0.95rem;">
                            Rp {{ number_format($product->price, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- PAGINATION (Penting: Class ajax-pagination untuk JS) --}}
    <div class="mt-4 mt-md-5 d-flex justify-content-center ajax-pagination">
        {{ $products->links() }} 
    </div>

@else
    <div class="text-center py-5">
        <div class="mb-3">
            <i class="bi bi-search fs-1 text-muted opacity-50"></i>
        </div>
        <h5 class="fw-bold">Produk Tidak Ditemukan</h5>
        <p class="text-muted">Coba kata kunci lain atau reset filter.</p>
    </div>
@endif