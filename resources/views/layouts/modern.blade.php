<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- 1. LOGIKA JUDUL DINAMIS --}}
    <title>@yield('title', $website->meta_title ? $website->meta_title : $website->site_name)</title>

    {{-- 2. LOGIKA META DESCRIPTION --}}
    <meta name="description" content="@yield('meta_description', $website->meta_description ?? 'Selamat datang di ' . $website->site_name)">
    <meta name="keywords" content="{{ $website->meta_keywords ?? 'toko online, webcommerce' }}">

    {{-- 3. OPEN GRAPH --}}
    <meta property="og:title" content="@yield('title', $website->meta_title ?? $website->site_name)">
    <meta property="og:description" content="@yield('meta_description', $website->meta_description)">
    <meta property="og:image" content="{{ $website->logo ? asset('storage/'.$website->logo) : asset('default-image.jpg') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family={{ $website->font_family ?? 'Inter' }}:wght@400;700&display=swap" rel="stylesheet">

    @if($website->favicon)
        <link rel="icon" href="{{ asset('storage/'.$website->favicon) }}">
    @endif

    <style>
        html { scroll-behavior: smooth; }
        :root {
            --primary-color: {{ $website->primary_color ?? '#0d6efd' }}; 
            --secondary-color: {{ $website->secondary_color ?? '#6c757d' }};
            --font-main: '{{ $website->font_family ?? 'Inter' }}', sans-serif;
            --ratio-product: {{ $website->product_image_ratio ?? '1/1' }};
            --hero-bg-color: {{ $website->hero_bg_color ?? '#333333' }};
        }

        body { font-family: var(--font-main); }

        .text-primary-custom { color: var(--primary-color) !important; }
        .bg-primary-custom { background-color: var(--primary-color) !important; color: white; }
        .btn-primary-custom { 
            background-color: var(--primary-color); 
            border-color: var(--primary-color); 
            color: white; 
        }
        .btn-primary-custom:hover { opacity: 0.9; color: white; }
        
        .btn-outline-secondary-custom {
            color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        .btn-outline-secondary-custom:hover {
            background-color: var(--secondary-color);
            color: white;
        }

        .hero-section {
            background-color: var(--hero-bg-color);
            color: white;
            padding: 80px 0;
            margin: 40px 0;
            background-size: cover;
            background-position: center;
        }
        .card-img-top {
            width: 100%;
            aspect-ratio: var(--ratio-product); 
            object-fit: cover;
        }
    </style>
</head>
<body>
@php
    // LOGIKA PEMBUATAN LINK TOKO
    // 1. Cek apakah punya Custom Domain?
    if ($website->custom_domain) {
        $storeUrl = 'http://' . $website->custom_domain . ':8000'; // Tambah port jika di localhost
    } 
    // 2. Jika tidak, pakai Subdomain
    else {
        $appUrl = env('APP_URL');
        // Bersihkan http:// dari APP_URL untuk digabung
        $cleanAppUrl = str_replace(['http://', 'https://'], '', $appUrl);
        
        $storeUrl = 'http://' . $website->subdomain . '.' . $cleanAppUrl;
        
        // Fix untuk localhost yang pakai port (misal localhost:8000)
        // Hasilnya jadi: http://elecjos.localhost:8000
        if (str_contains($appUrl, ':')) {
             $storeUrl = 'http://' . $website->subdomain . '.localhost:8000';
        }
    }
@endphp
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ $storeUrl }}">
                @if($website->logo)
                    <img src="{{ asset('storage/'.$website->logo) }}" height="40" alt="Logo">
                @else
                    {{ $website->site_name }}
                @endif
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    
                    @php
                        $navMenus = $website->navigation_menu 
                                    ? json_decode($website->navigation_menu, true) 
                                    : [['label' => 'Beranda', 'url' => '#'], ['label' => 'Produk', 'url' => '#products']];
                    @endphp

            @foreach($navMenus as $menu)
                <li class="nav-item">
                    @php
                        $url = $menu['url'];
                        $href = $url; 
                        
                        if (str_starts_with($url, '#')) {
                            if (request()->routeIs('store.home')) {
                                $href = $url;
                            } else {
                                $href = route('store.home', $website->subdomain) . $url;
                            }
                        } 
                        elseif (str_starts_with($url, '/')) {
                            $storeUrl = rtrim(route('store.home', $website->subdomain), '/');
                            $href = $storeUrl . $url; 
                        }
                    @endphp

                    <a class="nav-link text-dark" href="{{ $href }}">
                        {{ $menu['label'] }}
                    </a>
                </li>
            @endforeach

            {{-- LOGIC CART COUNT YANG DIPERBAIKI --}}
            @php
                $cartKey = 'cart_' . $website->id;
                $cartSession = session()->get($cartKey, []);
                
                $cartCount = 0;
                if(is_array($cartSession)) {
                    foreach($cartSession as $item) {
                         if(is_array($item)) {
                             // Cek 'quantity', fallback ke 'qty' (jika data lama), fallback ke 0
                             $qty = $item['quantity'] ?? $item['qty'] ?? 0;
                             $cartCount += $qty;
                         }
                    }
                }
            @endphp
        
            {{-- Tombol Cart --}}
             <li class="nav-item ms-3">
                    <a href="{{ route('store.cart', $website->subdomain) }}" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-cart"></i> Cart 
                        <span class="badge bg-white text-primary ms-1 rounded-pill">{{ $cartCount }}</span>
                    </a>
                </li>
                </ul>
            </div>
        </div>
    </nav>

    @if(session('success'))
        <div class="container mt-4 text-center">
            <div class="alert alert-success d-inline-block px-5 rounded-0 border-0" 
                 style="background-color: var(--secondary-color); color: white;">
                <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="container mt-4 text-center">
            <div class="alert alert-danger d-inline-block px-5 rounded-0 border-0">
                <i class="bi bi-exclamation-circle me-2"></i> {{ session('error') }}
            </div>
        </div>
    @endif

    @yield('content')

    <footer class="bg-dark text-white pt-5 pb-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="fw-bold text-primary-custom">{{ $website->site_name }}</h5>
                    <p class="small text-secondary">{{ $website->hero_subtitle ?? 'Toko Terpercaya.' }}</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h6 class="fw-bold">Kontak</h6>
                    <ul class="list-unstyled small text-secondary">
                        @if($website->whatsapp_number)
                            @php
                                // Format nomor Admin
                                $adminWa = $website->whatsapp_number;
                                if(str_starts_with($adminWa, '0')) {
                                    $adminWa = '62' . substr($adminWa, 1);
                                }
                                $msgCustomer = "Halo Admin {$website->site_name}, saya mau tanya tentang produk...";
                            @endphp
                            
                            <li>
                                <a href="https://wa.me/{{ $adminWa }}?text={{ urlencode($msgCustomer) }}" target="_blank" class="text-decoration-none text-secondary">
                                    <i class="bi bi-whatsapp"></i> +62 {{ $website->whatsapp_number }}
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
            <hr class="border-secondary">
            <div class="text-center small text-secondary">
                &copy; {{ date('Y') }} {{ $website->site_name }}. Powered by WebCommerce.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

      <script>
        if (window.self !== window.top) {
            document.querySelectorAll('a, form').forEach(el => {
                el.addEventListener('click', e => {
                    if(el.getAttribute('href') !== '#' && !el.getAttribute('href').startsWith('#')) {
                        e.preventDefault(); 
                    }
                });
                el.addEventListener('submit', e => e.preventDefault());
            });

            window.addEventListener('message', function(event) {
                const data = event.data;
                
                if (data.type === 'updateStyle') {
                    document.documentElement.style.setProperty(data.variable, data.value);
                }
                else if (data.type === 'updateSection') {
                    if (data.key === 'limit') {
                        const newLimit = parseInt(data.value);
                        const items = document.querySelectorAll('.product-item');
                        items.forEach((item, index) => {
                            if (index < newLimit) {
                                item.style.setProperty('display', 'block', 'important');
                            } else {
                                item.style.setProperty('display', 'none', 'important');
                            }
                        });
                    }
                    else if (data.key.includes('icon')) {
                        const selector = `[data-section-id="${data.sectionId}"][data-key="${data.key}"]`;
                        const element = document.querySelector(selector);
                        if (element) {
                            element.className = `bi ${data.value} live-editable`;
                        }
                    }
                    else {
                        const selector = `[data-section-id="${data.sectionId}"][data-key="${data.key}"]`;
                        const element = document.querySelector(selector);
                        if (element) element.innerText = data.value;
                    }
                }
                else if (data.type === 'updateImage') {
                    if (data.target === 'logo') {
                        const img = document.getElementById('logo-image');
                        const txt = document.getElementById('logo-text');

                        if (data.action === 'remove') {
                            if(img) img.style.display = 'none';
                            if(txt) txt.style.display = 'inline-block';
                        } 
                        else {
                            if(img) {
                                img.src = data.src;
                                img.style.display = 'inline-block';
                            }
                            if(txt) txt.style.display = 'none';
                        }
                    } 
                    else if (data.target === 'hero') {
                        const heroSimple = document.querySelector('.hero-section');
                        const noImageStyle = "background-color: var(--hero-bg-color); background-image: none; color: var(--primary-color); text-shadow: none;";
                        
                        if (data.action === 'remove') {
                            if(heroSimple) {
                                heroSimple.style = noImageStyle;
                                heroSimple.style.color = 'var(--primary-color)'; 
                                const p = heroSimple.querySelector('p');
                                if(p) { p.classList.remove('text-white'); p.classList.add('text-secondary'); }
                            }
                        } 
                        else {
                            const bgStyle = `url('${data.src}')`;
                            if(heroSimple) {
                                heroSimple.style.backgroundImage = bgStyle;
                                heroSimple.style.backgroundColor = 'transparent';
                                heroSimple.style.color = 'white'; 
                                heroSimple.style.textShadow = '0 2px 4px rgba(0,0,0,0.5)';
                                const p = heroSimple.querySelector('p');
                                if(p) { p.classList.remove('text-secondary'); p.classList.add('text-white'); }
                            }
                        }
                    }
                }
                else if (data.type === 'toggleSection') {
                    const sectionEl = document.getElementById(data.sectionId);
                    if (sectionEl) {
                        sectionEl.style.display = data.visible ? 'block' : 'none';
                    }
                }
                else if (data.type === 'moveSection') {
                    const sectionEl = document.getElementById(data.sectionId);
                    if (sectionEl) {
                        const parent = sectionEl.parentNode;
                        if (data.direction === 'up') {
                            if (sectionEl.previousElementSibling) {
                                parent.insertBefore(sectionEl, sectionEl.previousElementSibling);
                            }
                        } 
                        else {
                            if (sectionEl.nextElementSibling) {
                                parent.insertBefore(sectionEl, sectionEl.nextElementSibling.nextElementSibling);
                            }
                        }
                    }
                }
            });
        }
    </script>
    @stack('scripts')
</body>
</html>