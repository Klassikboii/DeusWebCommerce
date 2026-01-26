<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $website->site_name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=Playfair+Display:wght@400;700&family=Roboto:wght@400;700&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">

    <meta name="description" content="{{ $website->meta_description ?? 'Selamat datang di ' . $website->site_name }}">
    <meta name="keywords" content="{{ $website->meta_keywords }}">
    
    <meta property="og:title" content="{{ $website->meta_title ?? $website->site_name }}">
    <meta property="og:description" content="{{ $website->meta_description }}">
    <meta property="og:image" content="{{ $website->logo ? asset('storage/'.$website->logo) : '' }}">
    
    @if($website->favicon && Storage::disk('public')->exists($website->favicon))
        <link rel="icon" href="{{ asset('storage/'.$website->favicon) }}?v={{ time() }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif
    <style>

        body { 
            font-family: var(--font-main); 
            font-size: {{ $website->base_font_size }}px; /* Ukuran dasar dari DB */
        }
        /* Variabel Warna (Nanti ini yang akan diedit oleh Website Builder) */
        :root {
            --primary-color: {{ $website->primary_color ?? '#0d6efd' }}; 
            --secondary-color: {{ $website->secondary_color ?? '#6c757d' }};
            --font-main: '{{ $website->font_family ?? 'Inter' }}', sans-serif;
            --ratio-product: {{ $website->product_image_ratio ?? '1/1' }};
            --hero-bg-color: {{ $website->hero_bg_color ?? '#333333' }};
        }
        .product-img, .card-img-top {
            width: 100%;
            aspect-ratio: var(--ratio-product); /* Fitur CSS modern magic! */
            object-fit: cover;
            height: auto !important; /* Reset height statis lama */
        }
        
        .bg-primary-custom { background-color: var(--primary-color) !important; }
        .text-primary-custom { color: var(--primary-color) !important; }
        .btn-primary-custom { 
            background-color: var(--primary-color); 
            border-color: var(--primary-color); 
            color: white;
        }
        .btn-primary-custom:hover { opacity: 0.9; color: white; }
        /* --- TAMBAHAN BARU UNTUK WARNA SEKUNDER --- */
        .text-secondary-custom { color: var(--secondary-color) !important; }
        .bg-secondary-custom { background-color: var(--secondary-color) !important; color: white; }
        .btn-secondary-custom {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            color: white;
        }
        .btn-outline-secondary-custom {
            color: var(--secondary-color);
            border-color: var(--secondary-color);
            background-color: transparent;
        }
        .btn-outline-secondary-custom:hover {
            background-color: var(--secondary-color);
            color: white;
        }
        /* ------------------------------------------ */
        
        .hero-section {
        background-color: var(--hero-bg-color); /* Fallback Color */
        
        @if($website->hero_image)
            /* Kalau ada gambar, tumpuk dengan gradient */
            background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('{{ asset("storage/".$website->hero_image) }}');
            background-size: cover;
            background-position: center;
        @else
            /* Kalau tidak ada gambar, kosongkan image (jadi pakai background-color saja) */
            background-image: none;
        @endif
        
        color: white; /* Pastikan teks putih di banner modern */
    }
        .product-card { transition: transform 0.2s; }
        .product-card:hover { transform: translateY(-5px); shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .product-img { height: 200px; object-fit: cover; width: 100%; }

        /* CSS RESPONSIVE TAMBAHAN */
        @media (max-width: 768px) {
            /* Saat di HP, kecilkan font judul banner */
            .display-4, .display-6 {
                font-size: 2rem !important; /* Paksa jadi lebih kecil */
            }
            .lead {
                font-size: 1rem !important;
            }
            /* Padding banner dikurangi biar gak terlalu tinggi */
            .hero-section, .hero-section-simple {
                padding: 60px 0 !important;
            }
        }
    </style>
</head>
<body>
    

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            
            <a class="navbar-brand fw-bold" href="#">
                <img src="{{ $website->logo ? asset('storage/'.$website->logo) : '' }}" 
                     id="logo-img-preview" 
                     style="height: 40px; {{ $website->logo ? '' : 'display:none;' }}" 
                     alt="Logo">
                
                <span id="site-name-text" style="{{ $website->logo ? 'display:none;' : '' }}">
                    {{ $website->site_name }}
                </span>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto gap-2 align-items-center">
                    
                    @php
                        // Logika: Ambil JSON dari DB, kalau kosong pakai default
                        $navMenus = $website->navigation_menu 
                                    ? json_decode($website->navigation_menu, true) 
                                    : [
                                        ['label' => 'Beranda', 'url' => '#'],
                                        ['label' => 'Produk', 'url' => '#products']
                                      ];
                    @endphp

                    @foreach($navMenus as $menu)
                    <li class="nav-item">
                        <a class="nav-link" href="{{ $menu['url'] }}">{{ $menu['label'] }}</a>
                    </li>
                    @endforeach

                    <li class="nav-item ms-2">
                        <a class="btn btn-primary-custom rounded-pill px-4" href="{{ route('store.cart', $website->subdomain) }}">
                            <i class="bi bi-cart"></i> Cart ({{ count(session('cart', [])) }})
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    @if(session('success'))
        <div class="container mt-4">
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <header class="hero-section text-center" id="hero-section-bg">
        <div class="container margin-top-5 padding-y-5" style="padding: 80px 0;">
            <h1 class="display-4 fw-bold mb-3 " id="hero-title-text">
                {{ $website->hero_title ?? 'Selamat Datang di ' . $website->site_name }}
            </h1>
            
            <p class="lead mb-4" id="hero-subtitle-text">
                {{ $website->hero_subtitle ?? 'Temukan produk terbaik dengan harga terjangkau.' }}
            </p>
            
            <a href="#products" class="btn btn-primary-custom btn-lg rounded-pill px-5" id="hero-btn-text">
                {{ $website->hero_btn_text ?? 'Belanja Sekarang' }}
            </a>
        </div>
    </header>

    <section id="products" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Produk Terbaru</h2>
                <div class="d-flex justify-content-center">
                    <div style="height: 4px; width: 60px; background-color: var(--primary-color);"></div>
                </div>
            </div>

            <div class="row g-4">
                @forelse($products as $product)
                <div class="col-6 col-md-3">
                    <div class="card product-card border-0 shadow-sm h-100">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" class="product-img card-img-top" alt="{{ $product->name }}">
                        @else
                            <div class="product-img bg-secondary-subtle d-flex align-items-center justify-content-center text-muted">
                                <i class="bi bi-image h1"></i>
                            </div>
                        @endif
                        
                        <div class="card-body d-flex flex-column">
                            <small class="text-secondary-custom fw-bold mb-1">{{ $product->category->name ?? 'Umum' }}</small>
                            <h5 class="card-title fw-bold text-dark" style="font-size: 1rem;">{{ $product->name }}</h5>
                            <div class="mt-auto">
                                <h5 class="text-primary-custom fw-bold mb-3">Rp {{ number_format($product->price, 0, ',', '.') }}</h5>
                                
                                <form action="{{ route('store.cart.add', [$website->subdomain, $product->id]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary-custom w-100 rounded-pill btn-sm">
                                        <i class="bi bi-bag-plus"></i> Tambah
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center py-5">
                    <p class="text-muted">Belum ada produk yang ditampilkan.</p>
                </div>
                @endforelse
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white pt-5 pb-4 mt-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h5 class="fw-bold text-primary-custom mb-3">{{ $website->site_name }}</h5>
                    <p class="small text-secondary">
                        {{ $website->hero_subtitle ?? 'Toko online terpercaya dengan produk berkualitas.' }}
                    </p>
                </div>
                
                <div class="col-md-4">
                    <h6 class="fw-bold mb-3">Hubungi Kami</h6>
                    <ul class="list-unstyled small text-secondary">
                        @if($website->address)
                            <li class="mb-2"><i class="bi bi-geo-alt me-2"></i> {{ $website->address }}</li>
                        @endif
                        @if($website->whatsapp_number)
                            <li class="mb-2"><i class="bi bi-whatsapp me-2"></i> +62 {{ $website->whatsapp_number }}</li>
                        @endif
                        @if($website->email_contact)
                            <li class="mb-2"><i class="bi bi-envelope me-2"></i> {{ $website->email_contact }}</li>
                        @endif
                    </ul>
                </div>

                <div class="col-md-4">
                    <h6 class="fw-bold mb-3">Menu</h6>
                    <ul class="list-unstyled small">
                        <li><a href="#" class="text-secondary text-decoration-none">Beranda</a></li>
                        <li><a href="#products" class="text-secondary text-decoration-none">Produk</a></li>
                        <li><a href="#" class="text-secondary text-decoration-none">Keranjang</a></li>
                    </ul>
                </div>
            </div>
            
            <hr class="border-secondary mt-4">
            
            <div class="text-center small text-secondary">
                &copy; {{ date('Y') }} {{ $website->site_name }}. Powered by WebCommerce.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>