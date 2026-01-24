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
    @if($website->favicon)
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $website->favicon) }}">
    @endif
    <style>
        
        /* 1. KITA PASANG VARIABEL WARNA AGAR BISA DIEDIT */
        :root {
            --primary-color: {{ $website->primary_color ?? '#0d6efd' }}; 
            --secondary-color: {{ $website->secondary_color ?? '#6c757d' }};
            --font-main: '{{ $website->font_family ?? 'Inter' }}', sans-serif;
            --ratio-product: {{ $website->product_image_ratio ?? '1/1' }};
            --hero-bg-color: {{ $website->hero_bg_color ?? '#333333' }};
        }

        body { font-family: 'Georgia', serif; background-color: #fcfcfc; }

        body { 
            font-family: var(--font-main); 
            font-size: {{ $website->base_font_size }}px; /* Ukuran dasar dari DB */
        }

        :root {
            --primary-color: {{ $website->primary_color ?? '#0d6efd' }}; 
            --secondary-color: {{ $website->secondary_color ?? '#6c757d' }};
            --font-main: '{{ $website->font_family ?? 'Inter' }}', sans-serif;
            --ratio-product: {{ $website->product_image_ratio ?? '1/1' }};
        }
        
        /* Ganti warna statis menjadi dinamis (var) */
        .text-primary-custom { color: var(--primary-color) !important; }
        .bg-primary-custom { background-color: var(--primary-color) !important; }
        
        .btn-custom { 
            background-color: var(--primary-color); 
            border-color: var(--primary-color); 
            color: white;
            border-radius: 0;
        }
        .btn-custom:hover { opacity: 0.9; color: white; }

        .card { border: 1px solid #eee; border-radius: 0; }
        .navbar-brand { font-family: 'Helvetica', sans-serif; letter-spacing: 2px; text-transform: uppercase; color: var(--primary-color); }
        .nav-link { color: #333; }
        .nav-link:hover { color: var(--primary-color); }

        .hero-section {
    /* Jika ada gambar di DB, pakai itu. Jika tidak, pakai default */
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), 
                        url('{{ $website->hero_image ? asset("storage/".$website->hero_image) : "https://source.unsplash.com/1600x900/?store" }}');
            background-size: cover;
            background-position: center;
        }

        /* Tambahkan class Hero Section khusus Simple */
        .hero-section-simple {
            padding: 80px 0;
            margin: 40px 0;
            /* Default background transparan/putih */
            background-color: transparent; 
            
            /* Persiapan jika ada gambar */
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
        }
        .card-img-top {
            width: 100%;
            /* Hapus height statis, gunakan aspect-ratio */
            aspect-ratio: var(--ratio-product); 
            object-fit: cover;
        }

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

   <nav class="navbar navbar-expand-lg bg-white border-bottom py-4">
        <div class="container flex-column">
            
            <a class="navbar-brand fw-bold fs-3 mb-3" href="#">
                <img src="{{ $website->logo ? asset('storage/'.$website->logo) : '' }}" 
                     id="logo-img-preview" 
                     style="height: 50px; {{ $website->logo ? '' : 'display:none;' }}">
                
                <span id="site-name-text" style="{{ $website->logo ? 'display:none;' : '' }}">
                    {{ $website->site_name }}
                </span>
            </a>

            <ul class="nav justify-content-center small text-uppercase gap-4">
                @php
                    $navMenus = $website->navigation_menu 
                                ? json_decode($website->navigation_menu, true) 
                                : [
                                    ['label' => 'Home', 'url' => '#'],
                                    ['label' => 'Shop', 'url' => '#shop']
                                  ];
                @endphp

                @foreach($navMenus as $menu)
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ $menu['url'] }}">{{ $menu['label'] }}</a>
                </li>
                @endforeach

                <li class="nav-item">
                    <a href="{{ route('store.cart', $website->subdomain) }}" class="nav-link fw-bold" style="color: var(--secondary-color)">
                        Cart ({{ count(session('cart', [])) }})
                    </a>
                </li>
            </ul>
        </div>
    </nav>


    @if(session('success'))
        <div class="container mt-4 text-center">
            <div class="alert alert-success d-inline-block px-5 rounded-0 border-0" style="background-color: var(--secondary-color); color: white;">
                <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            </div>
        </div>
    @endif
    <header class="hero-section text-center" id="hero-section-bg"></header>
   <div class="hero-section-simple text-center" id="hero-section-bg"
         style="background-color: var(--hero-bg-color); 
                {{ $website->hero_image ? 'background-image: url('.asset('storage/'.$website->hero_image).'); color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.5);' : 'color: var(--primary-color);' }}">
         
        <div class="container">
            <h2 class="display-6 fst-italic mb-3" id="hero-title-text">
                {{ $website->hero_title ?? 'Koleksi Terbaru' }}
            </h2>
            <p class="{{ $website->hero_image ? 'text-white' : 'text-secondary' }} mb-4" id="hero-subtitle-text">
                {{ $website->hero_subtitle ?? 'Temukan gaya terbaik Anda hari ini.' }}
            </p>
            <a href="#shop" class="btn btn-custom px-5 py-2 text-uppercase" id="hero-btn-text" style="font-size: 12px; letter-spacing: 1px;">
                {{ $website->hero_btn_text ?? 'Shop Now' }}
            </a>
        </div>
    </div>

    <div id="shop" class="container pb-5">
        <div class="row g-4">
            @foreach($products as $product)
            <div class="col-6 col-md-3">
                <div class="card h-100 border-0">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" class="card-img-top">
                    @else
                        <div class="bg-light card-img-top d-flex align-items-center justify-content-center">
                            <i class="bi bi-image text-muted"></i>
                        </div>
                    @endif
                    <div class="card-body text-center p-3">
                        <h6 class="card-title mb-1 small text-uppercase">{{ $product->name }}</h6>
                        <p class="card-text fw-bold small text-primary-custom">Rp {{ number_format($product->price) }}</p>
                        
                        <form action="{{ route('store.cart.add', [$website->subdomain, $product->id]) }}" method="POST">
                            @csrf
                            <button class="btn btn-outline-dark btn-sm w-100 rounded-0">Add to Cart</button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

     <footer class="bg-light text-black pt-5 pb-4 mt-5">
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

    <script>
        if (window.self !== window.top) {
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    alert('Mode Preview: Aksi ini dimatikan di editor.');
                });
            });
        }
    </script>
</body>
</html>