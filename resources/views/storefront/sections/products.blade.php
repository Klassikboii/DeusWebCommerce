@php
    // Ambil settingan limit dari JSON, default 8
    $limit = $data['limit'] ?? 8;
    $title = $data['title'] ?? 'Produk Terbaru';

    // Query Produk (Ambil dari relasi website)
    // $website sudah tersedia otomatis karena di-pass dari Controller utama
    $products = $website->products()->latest()->take($limit)->get();
@endphp

<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-dark">{{ $title }}</h3>
            <a href="#" class="text-decoration-none fw-bold">Lihat Semua &rarr;</a>
        </div>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            @forelse($products as $product)
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px; overflow: hidden;">
                            @if($product->image)
                                <img src="{{ asset('storage/'.$product->image) }}" class="w-100 h-100 object-fit-cover" alt="{{ $product->name }}">
                            @else
                                <i class="bi bi-box-seam fs-1 text-muted"></i>
                            @endif
                        </div>
                        
                        <div class="card-body">
                            <h5 class="card-title fs-6 fw-bold mb-1">{{ $product->name }}</h5>
                            <p class="card-text text-primary fw-bold">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                            <button class="btn btn-outline-primary btn-sm w-100 mt-2">
                                <i class="bi bi-cart-plus me-1"></i> Tambah
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5 text-muted">
                    <p>Belum ada produk yang ditampilkan.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>