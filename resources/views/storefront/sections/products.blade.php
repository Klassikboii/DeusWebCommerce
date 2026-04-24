@php
    // 1. AMBIL DATA KONTEN DARI JSON BUILDER
    $title = $data['title'] ?? 'Produk Terbaru';
    $subtitle = $data['subtitle'] ?? 'Pilihan terbaik untuk Anda';
    $limit = $data['limit'] ?? 8;
    $sectionId = $data['id'] ?? 'products-' . uniqid();

    // 2. AMBIL PENGATURAN GAYA / SETTINGS
    $settings = $settings ?? []; 
    $colorMode = $settings['color_mode'] ?? 'global';
    
    // Logika Warna: Jika Global, ambil dari CSS Variable. Jika Custom, ambil dari Hex.
    if ($colorMode === 'global') {
        $bgColor = 'var(--bg-base)';
        $textColor = 'var(--text-base)';
    } else {
        $bgColor = $settings['bg_color'] ?? '#ffffff';
        $textColor = $settings['text_color'] ?? '#000000';
    }

    // Logika Jarak (Padding)
    $paddingY = $settings['padding'] ?? 'py-5';
    // 🚨 TAMBAHAN BARU: AMBIL VARIABEL TIPOGRAFI
    $textTransform = $settings['text_transform'] ?? 'none';
    $fontWeight = $settings['font_weight'] ?? 'bold'; // Default hero biasanya bold
    $fontStyle = $settings['font_style'] ?? 'normal';
    $headingSize = $settings['heading_size'] ?? 'display-3'; // Default ukuran judul
    // ----------------------------------------------------
@endphp

<section id="{{ $sectionId }}" class="{{ $paddingY }} live-section" style="background-color: {{ $bgColor }};text-transform: {{ $textTransform }}; 
                font-style: {{ $fontStyle }};">
    <div class="container py-4">
        
        {{-- HEADER SECTION --}}
        <div class="text-center mb-5 pb-3">
            {{-- Judul menggunakan Serif dan Uppercase --}}
            <h2 class="display-6 fw-bold section-title live-editable serif text-uppercase" 
                data-section-id="{{ $sectionId }}" 
                data-key="title"
                style="color: {{ $textColor }}; letter-spacing: 2px;font-family: var(--font-heading); text-transform: {{ $textTransform }}; font-weight: {{ $fontWeight }};">
                {{ $title }}
            </h2>
                
            <p class="live-editable" 
               data-section-id="{{ $sectionId }}" 
               data-key="subtitle"
               style="color: {{ $textColor }}; opacity: 0.7; letter-spacing: 0.5px;">
                {{ $subtitle }}
            </p>
        </div>

        {{-- GRID PRODUK --}}
        <div class="row g-4">
            {{-- TRIK REAL-TIME: Hanya produk aktif & harga > 0 --}}
            @foreach($website->products()->where('is_active', true)->where('price', '>', 0)->latest()->take(12)->get() as $index => $product)
                
                <div class="col-6 col-md-4 col-lg-3 product-item" 
                     style="{{ $index >= $limit ? 'display: none !important;' : '' }}">
                    
                    {{-- KARTU PRODUK KLASIK: Tanpa shadow, bersudut siku (rounded-0) --}}
                    <div class="card h-100 rounded-0 bg-transparent classic-product-card"
                         style="border: 1px solid rgba(0,0,0,0.08); transition: border-color 0.3s ease;">
                        
                        <div class="position-relative overflow-hidden rounded-0" style="background-color: #fcfcfc;">
                            
                            <a href="{{ route('store.product', ['slug' => $product->slug, 'subdomain' => $website->active_domain ?? '']) }}">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" 
                                         class="card-img-top rounded-0" 
                                         alt="{{ $product->name }}" 
                                         style="padding: 20px; aspect-ratio: 1/1; object-fit: contain;">
                                @else
                                    <div class="card-img-top rounded-0 d-flex align-items-center justify-content-center" 
                                         style="padding: 20px; aspect-ratio: 1/1; background-color: #f5f5f5;">
                                        <i class="bi bi-image text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                                    </div>
                                @endif
                            </a>
                            
                            {{-- BADGE KLASIK: Desain monokrom yang tegas --}}
                            @if($product->stock <= 0)
                                <div class="position-absolute top-0 end-0 m-0">
                                    <span class="badge rounded-0 bg-dark text-white text-uppercase py-2 px-3" 
                                          style="letter-spacing: 1px; font-size: 0.65rem;">Habis</span>
                                </div>
                            @elseif($product->stock <= 5)
                                <div class="position-absolute top-0 end-0 m-0">
                                    <span class="badge rounded-0 text-dark text-uppercase py-2 px-3" 
                                          style="background-color: #fff; border: 1px solid #000; border-right: none; border-top: none; letter-spacing: 1px; font-size: 0.65rem;">
                                        Terbatas
                                    </span>
                                </div>
                            @endif
                        </div>

                        {{-- INFO PRODUK --}}
                        <div class="card-body p-4 text-center border-top-0">
                            @if($product->category)
                                <small class="d-block mb-2 text-uppercase" 
                                       style="font-size: 0.65rem; letter-spacing: 1.5px; color: {{ $textColor }}; opacity: 0.5;">
                                    {{ $product->category->name }}
                                </small>
                            @endif
                            
                            <h6 class="card-title text-truncate mb-2 fw-bold serif" style="font-size: 1.1rem;">
                                <a href="{{ route('store.product', ['slug' => $product->slug, 'subdomain' => $website->active_domain ?? '']) }}" 
                                   class="text-decoration-none stretched-link"
                                   style="color: {{ $textColor }};">
                                    {{ $product->name }}
                                </a>
                            </h6>
                            
                            <p class="mb-0" style="color: {{ $textColor }}; font-size: 0.95rem;">
                                @if($product->hasVariants())
                                    <span style="opacity: 0.6; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Mulai</span> 
                                    Rp {{ number_format($product->variants->min('price'), 0, ',', '.') }}
                                @else
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- TOMBOL LIHAT SEMUA (Desain Kotak Klasik) --}}
        <div class="text-center mt-5 pt-3">
            <a href="{{ route('store.products', ['subdomain' => $website->active_domain ?? '']) }}" 
               class="btn rounded-0 px-5 py-3 text-uppercase fw-bold"
               style="border: 1px solid {{ $textColor }}; color: {{ $textColor }}; background-color: transparent; font-size: 0.8rem; letter-spacing: 2px; transition: all 0.3s ease;"
               onmouseover="this.style.backgroundColor='{{ $textColor }}'; this.style.color='{{ $bgColor }}';"
               onmouseout="this.style.backgroundColor='transparent'; this.style.color='{{ $textColor }}';">
                Lihat Semua Produk
            </a>
        </div>
    </div>
</section>

{{-- CSS Tambahan untuk Efek Hover Klasik --}}
<style>
    /* Efek hover diganti dari bayangan membesar menjadi border yang menebal/menggelap */
    .classic-product-card:hover { 
        border-color: #000 !important; 
    }
</style>