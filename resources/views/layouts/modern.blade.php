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
        .bg-primary-custom { background-color: var(--hero-bg-color) !important; color: white; }
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
        /* Hilangkan panah up/down default di input number */
        .no-arrow::-webkit-outer-spin-button,
        .no-arrow::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .no-arrow {
            -moz-appearance: textfield; /* Firefox */
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
            <a class="navbar-brand fw-bold" href="#">
                <img src="{{ $website->logo ? asset('storage/'.$website->logo) : '' }}" 
                    id="logo-img-preview" 
                    style="height: 40px; {{ $website->logo ? '' : 'display:none;' }}" 
                    alt="Logo">
                
                <span id="site-name-text" style="{{ $website->logo ? 'display:none;' : '' }}">
                    {{ $website->site_name }}
                </span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    
                   @php
                        // FIX: Langsung pakai array, hapus json_decode
                        $navMenus = $website->navigation_menu ?? [
                            ['label' => 'Beranda', 'url' => '#'],
                            ['label' => 'Produk', 'url' => '#products']
                        ];
                    @endphp

                    @foreach($navMenus as $menu)
                    <li class="nav-item">
                        @php
                            $url = $menu['url'];
                            $href = $url;

                            // LOGIKA SMART LINK MODERN
                            if (str_starts_with($url, '#')) {
                                if (!request()->routeIs('store.home')) {
                                    $href = route('store.home') . $url; 
                                }
                            } 
                            elseif (str_starts_with($url, '/')) {
                                $storeUrl = rtrim(route('store.home'), '/');
                                $href = $storeUrl . $url; 
                            }
                        @endphp
                        
                        <a class="nav-link" href="{{ $href }}">{{ $menu['label'] }}</a>
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
            <div class="row g-4">
                <div class="col-md-4">
                    <h5 class="fw-bold text-primary-custom mb-3">{{ $website->site_name }}</h5>
                    <p class="small text-secondary">
                        {{ $website->hero_subtitle ?? 'Platform toko online terpercaya.' }}
                    </p>
                    
                    {{-- Social Media / Contact Buttons --}}
                    <div class="d-flex gap-2 mt-3">
                        @if($website->whatsapp_number)
                            <a href="https://wa.me/62{{ $website->whatsapp_number }}?text=Halo%20{{ $website->site_name }},%20saya%20tertarik%20dengan%20produk%20Anda." 
                               target="_blank" class="btn btn-sm btn-success rounded-pill">
                                <i class="bi bi-whatsapp"></i> Chat WA
                            </a>
                        @endif
                        @if($website->email_contact)
                            <a href="mailto:{{ $website->email_contact }}" class="btn btn-sm btn-outline-light rounded-pill">
                                <i class="bi bi-envelope"></i> Email
                            </a>
                        @endif
                    </div>
                </div>
                
                <div class="col-md-4">
                    <h6 class="fw-bold mb-3">Hubungi Kami</h6>
                    <ul class="list-unstyled small text-secondary">
                        @if($website->address)
                            <li class="mb-3 d-flex">
                                <i class="bi bi-geo-alt me-2 mt-1 text-primary-custom"></i> 
                                <span>{{ $website->address }}</span>
                            </li>
                        @endif
                        
                        @if($website->whatsapp_number)
                            <li class="mb-2">
                                <i class="bi bi-telephone me-2 text-primary-custom"></i> 
                                +62 {{ $website->whatsapp_number }}
                            </li>
                        @endif
                        
                        @if($website->email_contact)
                            <li class="mb-2">
                                <i class="bi bi-envelope-at me-2 text-primary-custom"></i> 
                                {{ $website->email_contact }}
                            </li>
                        @endif
                    </ul>
                </div>

                <div class="col-md-4">
                    <h6 class="fw-bold mb-3">Menu</h6>
                    <ul class="list-unstyled small">
                        @php
                            $footerMenus = $website->navigation_menu ?? [
                                ['label' => 'Beranda', 'url' => '/'],
                                ['label' => 'Produk', 'url' => '#products']
                            ];
                        @endphp

                        @foreach($footerMenus as $menu)
                            <li class="mb-2">
                                <a href="{{ $menu['url'] }}" class="text-secondary text-decoration-none hover-white">
                                    {{ $menu['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            
            <hr class="border-secondary mt-4">
            
            <div class="text-center small text-secondary">
                &copy; {{ date('Y') }} {{ $website->site_name }}. Powered by WebCommerce.
            </div>
        </div>
    </footer>

    {{-- CSS Tambahan Sedikit --}}
    <style>
        .hover-white:hover { color: white !important; text-decoration: underline !important; }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

      <script>
        // Hanya jalankan listener jika berada di dalam iframe (Mode Preview)
        const isPreview = (window.self !== window.top);

        if (isPreview) {
            console.log("Mode Preview Aktif: Link dan Form dimatikan.");

            // 1. Blokir Link & Form HANYA jika di preview
            document.querySelectorAll('a, form').forEach(el => {
                // Jangan blokir anchor link (#) agar smooth scroll tetap bisa dicek di preview
                if (el.tagName === 'A' && el.getAttribute('href').startsWith('#')) {
                    return; 
                }

                // Blokir link pindah halaman
                el.addEventListener('click', e => {
                    e.preventDefault();
                    // Opsional: alert('Link dimatikan di mode editor');
                });
                
                // Blokir submit form
                el.addEventListener('submit', e => {
                    e.preventDefault();
                });
            });

            // 2. Event Listener Utama
            window.addEventListener('message', function(event) {
                const data = event.data;
                
                // A. UPDATE STYLE (Warna/Font)
                if (data.type === 'updateStyle') {
                    document.documentElement.style.setProperty(data.variable, data.value);
                }

                // B. UPDATE TEXT (Konten Section)
                else if (data.type === 'updateSection') {
                    // --- LOGIKA BARU UNTUK LIMIT PRODUK ---
                    if (data.key === 'limit') {
                        const newLimit = parseInt(data.value);
                        // Cari semua elemen produk
                        const items = document.querySelectorAll('.product-item');
                        
                        items.forEach((item, index) => {
                            // Jika urutan < limit baru -> TAMPILKAN
                            // Jika urutan >= limit baru -> SEMBUNYIKAN
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
                            // Reset semua class, lalu isi class standar + class baru
                            // "bi" dan "live-editable" adalah class wajib
                            element.className = `bi ${data.value} live-editable`;
                        }
                    }
                    
                    // --- Logika Text Biasa (Judul/Subtitle) ---
                    else {
                        const selector = `[data-section-id="${data.sectionId}"][data-key="${data.key}"]`;
                        const element = document.querySelector(selector);
                        if (element) element.innerText = data.value;
                    }
                }

               // C. UPDATE GAMBAR (Logo/Hero/Hapus)
                else if (data.type === 'updateImage') {
                    
                    // === LOGIK LOGO ===
                    if (data.target === 'logo') {
            const img = document.getElementById('logo-img-preview');
            const txt = document.getElementById('site-name-text');
            
            if (data.action === 'remove') {
                if(img) img.style.display = 'none';
                if(txt) txt.style.display = 'inline';
            } else {
                if(img) {
                    img.src = data.src;
                    img.style.display = 'inline';
                }
                if(txt) txt.style.display = 'none';
            }
        }
                    
                    // === LOGIK HERO BANNER ===
                    else if (data.target === 'hero') {
                        const heroSimple = document.querySelector('.hero-section');
                        const heroModern = document.querySelector('header'); 
                        
                        // Default Style (Tanpa Gambar)
                        const noImageStyle = "background-color: var(--hero-bg-color); background-image: none; color: var(--primary-color); text-shadow: none;";
                        
                        if (data.action === 'remove') {
                            if(heroSimple) {
                                heroSimple.style = noImageStyle;
                                // Reset warna teks kembali ke primary (hitam/biru) karena background putih
                                heroSimple.style.color = 'var(--primary-color)'; 
                                // Reset juga class text-white di p
                                const p = heroSimple.querySelector('p');
                                if(p) { p.classList.remove('text-white'); p.classList.add('text-secondary'); }
                            }
                        } 
                        else {
                            // Ada Gambar
                            const bgStyle = `url('${data.src}')`;
                            if(heroSimple) {
                                heroSimple.style.backgroundImage = bgStyle;
                                heroSimple.style.backgroundColor = 'transparent';
                                heroSimple.style.color = 'white'; 
                                heroSimple.style.textShadow = '0 2px 4px rgba(0,0,0,0.5)';
                                
                                // Ubah teks deskripsi jadi putih biar terbaca
                                const p = heroSimple.querySelector('p');
                                if(p) { p.classList.remove('text-secondary'); p.classList.add('text-white'); }
                            }
                        }
                    }
                }
                // D. TOGGLE VISIBILITY (Show/Hide Section)
                else if (data.type === 'toggleSection') {
                    const sectionEl = document.getElementById(data.sectionId);
                    if (sectionEl) {
                        // Jika visible=true -> display: block
                        // Jika visible=false -> display: none
                        sectionEl.style.display = data.visible ? 'block' : 'none';
                    }
                }
                // E. MOVE SECTION (Reorder)
                else if (data.type === 'moveSection') {
                    const sectionEl = document.getElementById(data.sectionId);
                    
                    if (sectionEl) {
                        const parent = sectionEl.parentNode;
                        
                        if (data.direction === 'up') {
                            // Pindahkan SEBELUM elemen di atasnya (previousSibling)
                            if (sectionEl.previousElementSibling) {
                                parent.insertBefore(sectionEl, sectionEl.previousElementSibling);
                            }
                        } 
                        else {
                            // Pindahkan SETELAH elemen di bawahnya (nextSibling)
                            // insertBefore tidak punya "insertAfter", jadi kita insert sebelum "depannya si tetangga"
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