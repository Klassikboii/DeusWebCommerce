@php
    // Ambil data dinamis dari JSON Builder
    $title = $data['title'] ?? 'Produk Terbaru';
    $subtitle = $data['subtitle'] ?? 'Pilihan terbaik untuk Anda';
    $limit = $data['limit'] ?? 8;
    $sectionId = $data['id'] ?? 'products';
@endphp

<section id="{{ $sectionId }}" class="py-5 bg-light">
    <div class="container">
        
        <div class="text-center mb-5">
            {{-- SENSOR LIVE PREVIEW UNTUK JUDUL & SUBJUDUL --}}
            <h2 class="fw-bold section-title live-editable" 
                data-section-id="{{ $sectionId }}" 
                data-key="title">{{ $title }}</h2>
                
            <p class="text-muted live-editable" 
               data-section-id="{{ $sectionId }}" 
               data-key="subtitle">{{ $subtitle }}</p>
        </div>

        <div class="row g-4">
            {{-- 
                TRIK REAL-TIME: Selalu query maksimal 12 produk (batas tertinggi dropdown).
                Lalu gunakan inline CSS untuk menyembunyikan index yang lebih besar dari $limit saat ini.
            --}}
            @foreach($website->products()->latest()->take(12)->get() as $index => $product)
                
                {{-- Tambahkan logika display:none jika melewati limit --}}
                <div class="col-6 col-md-4 col-lg-3 product-item" 
                     style="{{ $index >= $limit ? 'display: none !important;' : '' }}">
                    
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
                            
                            {{-- Badge Stok Habis --}}
                            @if($product->stock <= 0)
                                <div class="position-absolute top-0 end-0 m-2">
                                    <span class="badge bg-danger bg-opacity-75 backdrop-blur">Habis</span>
                                </div>
                            @elseif($product->stock <= 5)
                                <div class="position-absolute top-0 end-0 m-2">
                                    <span class="badge bg-warning bg-opacity-75 backdrop-blur">Stok Terbatas</span>
                                </div>
                            @endif
                        </div>

                        <div class="card-body p-3 text-center">
                            @if($product->category)
                                <small class="text-muted d-block mb-1 text-uppercase" style="font-size: 0.7rem;">{{ $product->category->name }}</small>
                            @endif
                            
                            <h6 class="card-title text-truncate mb-2 fw-bold" style="font-size: 1rem;">
                                <a href="{{ route('store.product', ['subdomain' => $website->subdomain, 'slug' => $product->slug]) }}" class="text-decoration-none text-dark stretched-link">
                                    {{ $product->name }}
                                </a>
                            </h6>
                            
                            <p class=" fw-bold mb-0"  style="color: var(--secondary-color);">
                                @if($product->hasVariants())
                                    <small>Mulai</small> Rp {{ number_format($product->variants->min('price'), 0, ',', '.') }}
                                @else
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- TOMBOL LIHAT SEMUA --}}
        <div class="text-center mt-5">
            <a href="{{ route('store.products', $website->subdomain) }}" class="btn btn-outline-primary rounded-pill px-5 py-2">
                Lihat Semua Produk <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

{{-- CSS Tambahan untuk Efek Hover --}}
<style>
    .hover-up { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .hover-up:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
    .backdrop-blur { backdrop-filter: blur(2px); }
</style>