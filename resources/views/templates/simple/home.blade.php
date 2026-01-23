<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $website->site_name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        /* 1. KITA PASANG VARIABEL WARNA AGAR BISA DIEDIT */
        :root {
            --primary-color: {{ $website->primary_color ?? '#000000' }}; 
            --secondary-color: {{ $website->secondary_color ?? '#6c757d' }};
        }

        body { font-family: 'Georgia', serif; background-color: #fcfcfc; }
        
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
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg bg-white border-bottom py-4">
        <div class="container flex-column">
            <a class="navbar-brand fw-bold fs-3 mb-3" href="#">{{ $website->site_name }}</a>
            <ul class="nav justify-content-center small text-uppercase gap-4">
                <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#shop">Shop</a></li>
                <li class="nav-item">
                    <a href="{{ route('store.blog', $website->subdomain) }}" class="nav-link">Blog</a>
                </li>
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

    <div class="container text-center py-5 my-5">
        <h2 class="display-6 fst-italic mb-3" id="hero-title-text">
            {{ $website->hero_title ?? 'Koleksi Terbaru' }}
        </h2>
        <p class="text-secondary mb-4" id="hero-subtitle-text">
            {{ $website->hero_subtitle ?? 'Temukan gaya terbaik Anda hari ini.' }}
        </p>
        <a href="#shop" class="btn btn-custom px-5 py-2 text-uppercase" id="hero-btn-text" style="font-size: 12px; letter-spacing: 1px;">
            {{ $website->hero_btn_text ?? 'Shop Now' }}
        </a>
    </div>

    <div id="shop" class="container pb-5">
        <div class="row g-4">
            @foreach($products as $product)
            <div class="col-6 col-md-3">
                <div class="card h-100 border-0">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" class="card-img-top" style="height: 250px; object-fit: cover;">
                    @else
                        <div class="bg-light" style="height: 250px;"></div>
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