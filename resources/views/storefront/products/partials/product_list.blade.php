{{-- resources/views/storefront/products/partials/product_list.blade.php --}}

@if($products->count() > 0)
    <div class="row g-4">
        @foreach($products as $product)
            <div class="col-6 col-md-4">
                <div class="card h-100 border-0 shadow-sm product-card hover-up">
                    <div class="position-relative overflow-hidden rounded-top">
                        <a href="{{ route('store.product', ['subdomain' => $website->subdomain, 'slug' => $product->slug]) }}">
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
                                <span class="badge bg-danger">Habis</span>
                            </div>
                        @endif
                    </div>
                    <div class="card-body p-3 text-center">
                        @if($product->category)
                            <small class="text-muted d-block mb-1">{{ $product->category->name }}</small>
                        @endif
                        <h6 class="card-title text-truncate mb-2">
                            <a href="{{ route('store.product', ['subdomain' => $website->subdomain, 'slug' => $product->slug]) }}" class="text-decoration-none text-dark stretched-link">
                                {{ $product->name }}
                            </a>
                        </h6>
                        <p class="text-primary fw-bold mb-0">
                            Rp {{ number_format($product->price, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- PAGINATION (Penting: Class ajax-pagination untuk JS) --}}
    <div class="mt-5 d-flex justify-content-center ajax-pagination">
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