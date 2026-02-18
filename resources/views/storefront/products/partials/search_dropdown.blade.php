@if($products->count() > 0)
    <div class="list-group list-group-flush">
        @foreach($products as $product)
            <a href="{{ route('store.product', ['subdomain' => $website->subdomain, 'slug' => $product->slug]) }}" 
               class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-0 py-2">
                
                {{-- Gambar Mini --}}
                <div style="width: 40px; height: 40px; flex-shrink: 0;" class="bg-light rounded overflow-hidden">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" class="w-100 h-100 object-fit-cover" alt="img">
                    @else
                        <div class="d-flex w-100 h-100 align-items-center justify-content-center text-muted"><i class="bi bi-image"></i></div>
                    @endif
                </div>

                {{-- Info Text --}}
                <div class="flex-grow-1" style="min-width: 0;">
                    <h6 class="mb-0 text-truncate small fw-bold">{{ $product->name }}</h6>
                    <small class="text-primary fw-bold" style="font-size: 0.75rem;">
                        Rp {{ number_format($product->price, 0, ',', '.') }}
                    </small>
                </div>
            </a>
        @endforeach
    </div>

    {{-- Link Lihat Semua --}}
    <div class="text-center mt-2 border-top pt-2">
        <a href="{{ route('store.products', ['subdomain' => $website->subdomain, 'search' => request('search')]) }}" class="btn btn-link btn-sm text-decoration-none p-0">
            Lihat semua hasil <i class="bi bi-arrow-right"></i>
        </a>
    </div>
@else
    <div class="text-center py-4">
        <p class="text-muted small mb-0">Produk tidak ditemukan.</p>
    </div>
@endif