<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $website->meta_title ? $website->meta_title : $website->site_name)</title>
    <meta name="description" content="@yield('meta_description', $website->meta_description ?? 'Selamat datang di ' . $website->site_name)">
    <meta name="keywords" content="{{ $website->meta_keywords ?? 'toko online, webcommerce' }}">
    <meta property="og:title" content="@yield('title', $website->meta_title ?? $website->site_name)">
    <meta property="og:description" content="@yield('meta_description', $website->meta_description)">
    <meta property="og:image" content="{{ $website->logo ? asset('storage/'.$website->logo) : asset('default-image.jpg') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @php
        $headingFont = $website->theme_config['typography']['heading'] ?? 'Playfair Display';
        $bodyFont = $website->theme_config['typography']['body'] ?? 'Inter';
        
        $headingUrl = str_replace(' ', '+', $headingFont);
        $bodyUrl = str_replace(' ', '+', $bodyFont);
    @endphp

    {{-- Memuat font dengan semua ketebalan (300, 400, 600, 700, 900) dan gaya (italic) --}}
{{-- 🚨 MENGGUNAKAN API V1: Lebih aman dan anti-error untuk Web Builder --}}
    <link id="google-font-link" href="https://fonts.googleapis.com/css?family={{ $headingUrl }}:300,400,600,700|{{ $bodyUrl }}:300,400,400i,600,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    @php
        // Ambil nama font dari JSON
        $fontName = $website->theme_config['typography']['main'] ?? 'Inter';
        // Ubah spasi menjadi + untuk URL Google Fonts (misal: "Playfair Display" jadi "Playfair+Display")
        $fontUrl = str_replace(' ', '+', $fontName);
    @endphp
    
    {{-- PERBAIKAN: Tambahkan ID dan gunakan wght@400;700 saja --}}
    <link id="google-font-link" href="https://fonts.googleapis.com/css2?family={{ $fontUrl }}:wght@400;600;700&display=swap" rel="stylesheet">

    {{-- FAVICON TOKO DENGAN CACHE BUSTER & FALLBACK --}}
    @if($website->favicon)
        {{-- Jika Klien upload favicon, gunakan timestamp agar browser selalu mengambil gambar terbaru --}}
        <link rel="icon" href="{{ asset('storage/'.$website->favicon) }}?v={{ $website->updated_at->timestamp }}">
    @else
        {{-- Jika Klien belum upload, gunakan Favicon platform sebagai cadangan --}}
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif

   <style>
        html { scroll-behavior: smooth; }
        
        :root {
            /* === REHAUL WARNA BARU === */
            /* 1. Latar Belakang Seluruh Halaman */
            --primary-color: {{ $website->theme_config['colors']['primary'] ?? '#f8f9fa' }}; 
            
            /* 2. Warna Blok Elemen (Card, Dropdown, Accordion) */
            --secondary-color: {{ $website->theme_config['colors']['secondary'] ?? '#ffffff' }};
            
            /* 3. Warna Aksen Brand (Tombol, Badge, Ikon) */
            --accent-color: {{ $website->theme_config['colors']['accent'] ?? '#0d6efd' }};
            
            /* 4. Warna Teks Utama */
            --text-base: {{ $website->theme_config['colors']['text_base'] ?? '#212529' }};
            
            /* Fallback untuk class lama / pencegahan error */
            --hero-bg-color: var(--primary-color);
            --bg-base: var(--primary-color);
            
            /* === TIPOGRAFI & BENTUK === */
            --font-heading: '{{ $headingFont ?? $fontHeading ?? "Playfair Display" }}', serif;
            --font-body: '{{ $bodyFont ?? "Inter" }}', sans-serif;
            
            --ratio-product: {{ $website->theme_config['shapes']['product_ratio'] ?? '1/1' }};
            --radius-base: {{ $website->theme_config['shapes']['radius'] ?? '0.5rem' }};
            --shadow-base: {{ $website->theme_config['shapes']['shadow'] ?? '0 0.125rem 0.25rem rgba(0,0,0,0.075)' }};
        }

        /* Mencegah styling inline dari TinyMCE/CKEditor merusak warna tema */
        /* [style*="color:"] h1, [style*="color:"] h2, [style*="color:"] h3, 
        [style*="color:"] h4, [style*="color:"] h5, [style*="color:"] h6, 
        [style*="color:"] p, [style*="color:"] span {
            color: inherit !important;
        } */

        /* =========================================================
           1. LOGIKA LATAR BELAKANG & TEKS UTAMA (PRIMARY)
           ========================================================= */
        body { 
            font-family: var(--font-body);
            background-color: var(--primary-color) !important; 
            color: var(--text-base);
        }
        .bg-light { background-color: var(--primary-color) !important; }
        .text-dark { color: var(--text-base) !important; }
        
        /* Semua Judul */
        h1, h2, h3, h4, h5, h6, .serif, .navbar-brand { 
            font-family: var(--font-heading);
            /* color: var(--text-base) !important; Judul mengikuti warna teks dasar agar kontras dengan background */
        }

        /* =========================================================
           2. LOGIKA ELEMEN CARD & BLOK (SECONDARY)
           ========================================================= */
        .bg-white, .card, .accordion-item, .accordion-button, .dropdown-menu, .section-box { 
            background-color: var(--secondary-color) !important; 
            color: var(--text-base) !important; 
        }

        /* =========================================================
           3. LOGIKA AKSEN BRAND & TOMBOL (ACCENT)
           ========================================================= */
        /* Tombol Utama */
        .btn-primary, .btn-custom, .btn-primary-custom, .btn-classic { 
            background-color: var(--accent-color) !important; 
            border-color: var(--accent-color) !important; 
            color: #ffffff !important; 
        }
        .btn-primary:hover, .btn-custom:hover, .btn-primary-custom:hover, .btn-classic:hover {
            opacity: 0.9;
        }
        
        /* Ikon & Teks Sorotan */
        .text-primary, .text-primary-custom, .nav-link:hover, .bi-cart, .bi-person, .bi-search {
            color: var(--accent-color) !important;
        }
        
        /* Tombol Outline */
        .btn-outline-primary {
            color: var(--accent-color) !important;
            border-color: var(--accent-color) !important;
        }
        .btn-outline-primary:hover {
            background-color: var(--accent-color) !important;
            color: #ffffff !important;
        }
        
        /* Badge / Subtitle khusus jika dibutuhkan */
        .text-muted, .small.text-muted { 
            opacity: 0.8; /* Memanfaatkan opacity agar warna mengikuti text-base namun lebih redup */
        }

        /* =========================================================
           4. SHAPE, BENTUK & KELAS UTILITAS (TIDAK DIHAPUS)
           ========================================================= */
        .card, .section-box, .feature-card, .accordion-item, .img-fluid.rounded, .btn:not(.rounded-pill), .form-control, .form-select {
            border-radius: var(--radius-base) !important;
        }
        .shadow-sm, .card, .feature-card {
            box-shadow: var(--shadow-base) !important;
            border: none !important;
        }
        .product-img-wrapper img { border-radius: var(--radius-base); }
        .card-img-top { width: 100%; aspect-ratio: var(--ratio-product); object-fit: cover; }
        
        /* Elemen Khusus Layout (Tetap Aman) */
        .hero-section { background-color: var(--primary-color); color: var(--text-base); padding: 80px 0; margin: 40px 0; background-size: cover; background-position: center; }
        .hero-section-simple { padding: 80px 0; margin: 40px 0; background-position: center; background-size: cover; background-repeat: no-repeat; }
        .no-arrow::-webkit-outer-spin-button, .no-arrow::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        .no-arrow { -moz-appearance: textfield; }
        .hover-white:hover { color: white !important; text-decoration: underline !important; }
        .search-results-dropdown { position: absolute; top: 100%; left: 0; right: 0; z-index: 1000; display: none; max-height: 400px; overflow-y: auto; }
        .search-results-dropdown.show { display: block; }

        /* =========================================================
           5. FIX GARIS PUTIH (BORDER BAWAAN BOOTSTRAP)
           ========================================================= */
        /* Menyamarkan seluruh garis pembatas kaku agar cocok di tema gelap maupun terang */
        .border, .border-top, .border-bottom, .border-start, .border-end, hr {
            border-color: rgba(128, 128, 128, 0.2) !important;
        }
        
        /* Khusus untuk Header, hapus total garisnya karena sudah menggunakan efek Shadow (bayangan) */
        header.border-bottom {
            border-bottom: none !important;
        }
    </style>
</head>
<body>
    {{-- BANNER TOKO TUTUP --}}
    @if(!$website->is_open)
    <div class="bg-danger text-white text-center py-2" style="z-index: 1050; position: relative;">
        <div class="container small fw-bold">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> 
            Mohon maaf, toko saat ini sedang TUTUP. Anda tidak dapat melakukan pemesanan untuk sementara waktu.
        </div>
    </div>
    @endif
@php
        if ($website->custom_domain) {
            // Gunakan custom domain murni (tambahkan port jika di local development)
            $port = str_contains(config('app.url'), ':8000') ? ':8000' : '';
            $storeUrl = 'http://' . $website->custom_domain . $port;
        } else {
            // Ambil domain utama dari config secara elegan tanpa replace manual
            $parsedUrl = parse_url(config('app.url'));
            $host = $parsedUrl['host'] ?? 'localhost';
            $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
            $scheme = $parsedUrl['scheme'] ?? 'http';
            
            $storeUrl = $scheme . '://' . $website->active_domain . '.' . $host . $port;
        }
    @endphp
@php
            $navItems = $website->navigation_menu ?? [];
            $menuCount = count($navItems);
            
            $expandClass = 'navbar-expand-lg'; // Standar (s/d 4 menu)
            
            if ($menuCount > 4 && $menuCount <= 6) {
                $expandClass = 'navbar-expand-xl'; // Hamburger di laptop kecil
            } elseif ($menuCount > 6) {
                // 🚨 KUNCI UTAMA: Kosongkan class!
                // Jika > 6, paksa jadi Hamburger selamanya (bahkan di TV 4K)
                $expandClass = ''; 
            }
        @endphp
    <nav class="navbar {{ $expandClass }} navbar-light bg-white shadow-sm sticky-top" style="box-shadow: var(--shadow-base)">
        <div class="container gap-lg-4">
            {{-- LOGO --}}
            <a class="navbar-brand fw-bold me-0" href="/">
                <img src="{{ $website->logo ? asset('storage/'.$website->logo) : '' }}" id="logo-img-preview" style="height: 40px; {{ $website->logo ? '' : 'display:none;' }}" alt="Logo">
                <span id="site-name-text" style="{{ $website->logo ? 'display:none;' : '' }}">{{ $website->site_name }}</span>
            </a>

            {{-- 
                === SEARCH BAR (DESKTOP VERSION) === 
                Muncul hanya di layar besar (d-lg-block).
                Bentuk: Input Group Rounded Pill.
                Hasil: Dropdown di bawahnya.
            --}}
            <div class="d-none d-lg-block flex-grow-1 position-relative mx-4" style="max-width: 600px;">
                <form action="{{ route('store.products') }}" method="GET" id="desktop-search-form">
                    <div class="input-group">
                        <input type="text" class="form-control rounded-start-pill border-end-0 ps-4 bg-light" 
                               name="search" id="desktop-search-input" 
                               placeholder="Cari produk di sini..." autocomplete="off">
                        <button class="btn btn-light border border-start-0 rounded-end-pill px-4 text-muted hover-bg-gray" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>

                {{-- Container Hasil Dropdown (Desktop) --}}
                <div class="dropdown-menu w-100 shadow border-0 mt-1 p-0 overflow-hidden search-results-dropdown" id="desktop-search-results">
                    {{-- Loader --}}
                    <div id="desktop-search-loading" class="text-center py-4 d-none">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    </div>
                    {{-- Content --}}
                    <div id="desktop-search-content"></div>
                </div>
            </div>

            {{-- TOGGLER MOBILE --}}
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse flex-grow-0" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    
                    {{-- 
                        === SEARCH ICON (MOBILE VERSION) === 
                        Muncul hanya di layar kecil (d-lg-none).
                        Bentuk: Icon -> Klik -> Dropdown Input.
                    --}}
                    <li class="nav-item dropdown d-lg-none">
                        <a class="nav-link" href="#" id="mobileSearchDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                            <i class="bi bi-search fs-5"></i> <span class="ms-2">Cari</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end p-3 shadow border-0 mt-2" style="width: 85vw;">
                            <div class="input-group mb-2">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" id="mobile-search-input" class="form-control border-start-0 ps-0" placeholder="Ketik produk..." autocomplete="off">
                            </div>
                            <div id="mobile-search-loading" class="text-center py-3 d-none">
                                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            </div>
                            <div id="mobile-search-results"></div>
                        </div>
                    </li>

                    {{-- MENU NAVIGASI --}}
                    @php
                            // Amankan data menu
                            $rawMenu = $website->navigation_menu;
                            $navMenus = is_string($rawMenu) ? json_decode($rawMenu, true) : $rawMenu;
                            
                            if (!is_array($navMenus) || empty($navMenus)) {
                                    $navMenus = [
                                        ['label' => 'Beranda', 'url' => '/'],
                                        ['label' => 'Produk', 'url' => '/products'], // <-- Ubah di sini
                                        ['label' => 'Blog', 'url' => '/blog'],       // <-- Ubah di sini
                                    ];
                                }
                                                @endphp
                                        @foreach($navMenus as $menu)
                            @php
                                $rawUrl = $menu['url'];
                                
                                // 🚨 PEMBERSIH URL OTOMATIS (Mencegah data usang dari database)
                                // Kita pecah URL-nya untuk membuang domain yang salah (shop.test)
                                $parsed = parse_url($rawUrl);
                                $pathOnly = $parsed['path'] ?? '';
                                $fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';
                                $cleanPath = $pathOnly . $fragment; // Hasilnya pasti bersih, contoh: "/products" atau "#shop"

                                // KASUS 1: Jika link eksternal murni (misal klien menaruh link ke Instagram)
                                if (isset($parsed['host']) && !str_contains($parsed['host'], 'shop.test') && !str_contains($parsed['host'], 'localhost') && !str_contains($parsed['host'], $website->active_domain)) {
                                    $href = $rawUrl;
                                }
                                // KASUS 2: Anchor Link murni (#)
                                elseif (str_starts_with($cleanPath, '#') && empty($pathOnly)) {
                                    if (!request()->routeIs('store.home')) {
                                        // Jika buka anchor tapi tidak di halaman home, lempar ke home dulu
                                        $href = rtrim($storeUrl, '/') . '/' . $cleanPath; 
                                    } else {
                                        $href = $cleanPath;
                                    }
                                } 
                                // KASUS 3: Internal Path (Ini akan memperbaiki link bocor secara instan!)
                                else {
                                    // Kita paksa path yang sudah bersih menempel ke URL Toko Klien
                                    $href = rtrim($storeUrl, '/') . '/' . ltrim($cleanPath, '/');
                                }
                            @endphp

                            {{-- Ganti class sesuai dengan desain layout Anda masing-masing --}}
                            <li class="nav-item text-center">
                                <a class="nav-link text-dark hover-dark  small  tracking-widest py-2 py-lg-1" href="{{ $href }}">
                                    {{ $menu['label'] }}
                                </a>
                            </li>
                        @endforeach
                       
                    {{-- Cek apakah pelanggan sudah login menggunakan guard 'customer' --}}
                        @if(Auth::guard('customer')->check())
                            
                            {{-- MENU JIKA SUDAH LOGIN (Berupa Dropdown) --}}
                            <div class="nav-item dropdown ms-3">
                                <a class="btn btn-outline-primary dropdown-toggle rounded-pill" href="#" id="customerMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-person-circle me-1"></i> Halo, {{ strtok(Auth::guard('customer')->user()->name, ' ') }}
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="customerMenu">
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('store.account') }}">
                                            <i class="bi bi-bag-check me-2 text-primary"></i> Riwayat Pesanan
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('store.logout') }}" method="POST" class="m-0">
                                            @csrf
                                            <button type="submit" class="dropdown-item py-2 text-danger">
                                                <i class="bi bi-box-arrow-right me-2"></i> Keluar
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>

                        @else
                            <a class="nav-link text-dark" href="{{ route('store.track') }}">
                                    Cek Pesanan
                                </a>
                            {{-- TOMBOL JIKA BELUM LOGIN --}}
                            <a href="{{ route('store.login') }}" class="btn btn-outline-secondary rounded-pill ms-3 shadow-sm">
                                <i class="bi bi-person me-1"></i> Masuk / Daftar
                            </a>
                        @endif
                    {{-- CART BUTTON --}}
                    @php
                        $cartKey = 'cart_' . $website->id;
                        $cartSession = session()->get($cartKey, []);
                        $cartCount = array_reduce($cartSession, fn($carry, $item) => $carry + ($item['quantity'] ?? $item['qty'] ?? 0), 0);
                    @endphp
                    <li class="nav-item ms-lg-2 mt-3 mt-lg-0">
                        <a href="{{ route('store.cart') }}" class="btn rounded-pill px-4 position-relative w-100" style="background-color: var(--primary-color); color: white;">
                            <i class="bi bi-cart"></i> 
                            <span class="d-lg-none ms-2">Keranjang</span>
                            @if($cartCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light">
                                {{ $cartCount }}
                            </span>
                            @endif
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    @if(session('success'))
        <div class="container mt-4 text-center"><div class="alert alert-success d-inline-block px-5 rounded-0 border-0" style="background-color: var(--secondary-color); color: white;"><i class="bi bi-check-circle me-2"></i> {{ session('success') }}</div></div>
    @endif
    @if(session('error'))
        <div class="container mt-4 text-center"><div class="alert alert-danger d-inline-block px-5 rounded-0 border-0"><i class="bi bi-exclamation-circle me-2"></i> {{ session('error') }}</div></div>
    @endif

    @yield('content')

    {{-- FOOTER SECTION (Gabungan Desain Lama dengan Sistem Tema Baru) --}}
    <footer class="pt-5 pb-4 mt-5 border-top" style="background-color: var(--bg-base); color: var(--text-base);">
        <div class="container">
            <div class="row g-4">
                
                {{-- Kolom 1: Brand & Tombol CTA --}}
                <div class="col-md-4">
                    <h5 class="fw-bold mb-3" style="color: var(--primary-color);">{{ $website->site_name }}</h5>
                    <p class="small" style="color: var(--secondary-color); line-height: 1.6;">
                        {{ $website->address ?: 'Platform toko online terpercaya.' }}
                    </p>
                    {{-- 🚨 LOGIKA GEMBOK BRANDING --}}
                            @php
                                // Cek apakah Klien berhak menghapus branding
                                // Menggunakan === true karena kita sudah cast kolom ini jadi boolean di Model
                                $canRemoveBranding = $website->subscription?->package?->remove_branding === true;
                            @endphp

                            {{-- Jika TIDAK BISA remove branding, maka tampilkan iklan SaaS Anda --}}
                            @if(!$canRemoveBranding)
                                <div class="mt-2 text-muted small">
                                    Powered by <a href="https://shop.ashop.asia" target="_blank" class="fw-bold text-decoration-none text-primary">ASHOP WebCommerce</a>
                                </div>
                            @endif
                    {{-- Tombol Sosial Media / Contact (Dari desain lama Anda) --}}
                    <div class="d-flex gap-2 mt-4">
                        @if($website->whatsapp_number)
                            <a href="https://wa.me/62{{ $website->whatsapp_number }}?text=Halo%20{{ urlencode($website->site_name) }},%20saya%20tertarik%20dengan%20produk%20Anda." 
                               target="_blank" class="btn btn-sm text-white rounded-pill px-3 shadow-sm" style="background-color: #25D366; border: none;">
                                <i class="bi bi-whatsapp me-1"></i> Chat WA
                            </a>
                        @endif
                        @if($website->email_contact)
                            <a href="mailto:{{ $website->email_contact }}" class="btn btn-sm btn-outline-secondary-custom rounded-pill px-3 shadow-sm">
                                <i class="bi bi-envelope me-1"></i> Email
                            </a>
                        @endif
                    </div>
                </div>
                
                {{-- Kolom 2: Kontak --}}
                <div class="col-md-4">
                    <h6 class="fw-bold mb-3">Hubungi Kami</h6>
                    <ul class="list-unstyled small" style="color: var(--secondary-color);">
                        @if($website->address)
                            <li class="mb-3 d-flex">
                                <i class="bi bi-geo-alt me-2 mt-1" style="color: var(--primary-color);"></i> 
                                <span>{{ $website->address }}</span>
                            </li>
                        @endif
                        
                        @if($website->whatsapp_number)
                            <li class="mb-2">
                                <i class="bi bi-telephone me-2" style="color: var(--primary-color);"></i> 
                                +62 {{ $website->whatsapp_number }}
                            </li>
                        @endif
                        
                        @if($website->email_contact)
                            <li class="mb-2">
                                <i class="bi bi-envelope-at me-2" style="color: var(--primary-color);"></i> 
                                {{ $website->email_contact }}
                            </li>
                        @endif
                    </ul>
                </div>

                {{-- Kolom 3: Menu Cepat --}}
                <div class="col-md-4">
                    <h6 class="fw-bold mb-3">Menu</h6>
                    <ul class="list-unstyled small">
                        @php
                            $footerMenus = $website->navigation_menu ?? [
                                ['label' => 'Beranda', 'url' => '/'],
                                ['label' => 'Katalog Produk', 'url' => route('store.products')],
                                
                            ];
                        @endphp

                        @foreach($footerMenus as $menu)
                            <li class="mb-2">
                                <a href="{{ $menu['url'] }}" class="text-decoration-none hover-primary" style="color: var(--secondary-color);">
                                    {{ $menu['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                
            </div>
            
            <hr class="mt-4" style="border-color: var(--secondary-color); opacity: 0.3;">
            
            <div class="text-center small" style="color: var(--secondary-color);">
                &copy; {{ date('Y') }} {{ $website->site_name }}. Powered by WebCommerce.
            </div>
        </div>
    </footer>
    
    <style>
        .hover-primary { transition: color 0.2s ease; }
        .hover-primary:hover { color: var(--primary-color) !important; }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    {{-- SCRIPT LIVE SEARCH (REUSABLE UNTUK MOBILE & DESKTOP) --}}
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        
        // Fungsi Generic untuk Live Search
        function setupLiveSearch(inputId, resultsId, loadingId, dropdownContainerId = null) {
            const input = document.getElementById(inputId);
            const results = document.getElementById(resultsId);
            const loading = document.getElementById(loadingId);
            const dropdown = dropdownContainerId ? document.getElementById(dropdownContainerId) : null;
            
            let debounceTimer;

            if(!input) return;

            // Helper untuk membangun URL
            const getSearchUrl = (query) => {
                let baseUrl = "{{ route('store.products', $website->active_domain) }}";
                let urlObj = new URL(baseUrl);
                urlObj.searchParams.set('search', query);
                urlObj.searchParams.set('type', 'dropdown');
                return urlObj.toString();
            };

            // Event Listener Input
            input.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                const query = this.value.trim();

                // 1. FIX: Jika input kosong/pendek, SEGERA sembunyikan loading & hasil
                if (query.length < 2) {
                    loading.classList.add('d-none');
                    if(dropdown) dropdown.classList.remove('show'); // Tutup dropdown desktop
                    results.innerHTML = ''; 
                    // Khusus mobile, kita biarkan text helper "mulai mengetik..."
                    if(!dropdown) results.innerHTML = '<div class="text-center text-muted small py-3 opacity-50">Ketik minimal 2 huruf...</div>';
                    return;
                }

                // 2. Tampilkan Loading
                loading.classList.remove('d-none');
                if(dropdown) dropdown.classList.add('show'); // Buka dropdown desktop
                if(!dropdown) results.classList.add('d-none'); // Sembunyikan hasil lama di mobile

                // 3. Debounce AJAX
                debounceTimer = setTimeout(() => {
                    fetch(getSearchUrl(query), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(response => response.text())
                    .then(html => {
                        results.innerHTML = html; // Masukkan konten ke div hasil (bukan timpa loader di desktop)
                        loading.classList.add('d-none');
                        
                        if(!dropdown) results.classList.remove('d-none'); // Show hasil mobile
                    })
                    .catch(err => {
                        console.error(err);
                        loading.classList.add('d-none');
                    });
                }, 400);
            });

            // Logic Klik di Luar untuk Desktop (Tutup Dropdown)
            if (dropdown) {
                document.addEventListener('click', function(e) {
                    if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                        dropdown.classList.remove('show');
                    }
                });
                
                // Buka lagi jika input diklik dan ada isinya
                input.addEventListener('focus', function() {
                    if (this.value.trim().length >= 2) {
                        dropdown.classList.add('show');
                    }
                });
            }
        }

        // --- INISIALISASI ---
        
        // 1. Setup Desktop Search (Pill Bar)
        // inputId, contentId (tempat HTML hasil), loadingId, containerId (dropdown yg di-toggle)
        setupLiveSearch('desktop-search-input', 'desktop-search-content', 'desktop-search-loading', 'desktop-search-results');

        // 2. Setup Mobile Search (Icon Popup)
        // Mobile tidak pakai manual toggle class 'show' karena sudah dihandle Bootstrap Dropdown
        setupLiveSearch('mobile-search-input', 'mobile-search-results', 'mobile-search-loading');
        
        // Auto focus input mobile saat icon diklik
        const mobileDropdownTrigger = document.getElementById('mobileSearchDropdown');
        if(mobileDropdownTrigger) {
            mobileDropdownTrigger.addEventListener('shown.bs.dropdown', () => {
                document.getElementById('mobile-search-input').focus();
            });
        }
    });
</script>
{{-- SCRIPT LIVE PREVIEW BUILDER (SUDAH DIGABUNGKAN MENJADI SATU) --}}
    <script>
        // Hanya jalankan listener jika berada di dalam iframe (Mode Preview)
        const isPreview = (window.self !== window.top);

        if (isPreview) {
            console.log("Mode Preview Aktif: Link dan Form dimatikan.");

            // 1. Blokir Link & Form HANYA jika di preview
            // 1. Blokir Link & Form HANYA jika di preview
            document.querySelectorAll('a, form').forEach(el => {
                if (el.tagName === 'A' && el.getAttribute('href') && el.getAttribute('href').startsWith('#')) return; 
                
                // 🚨 FIX: Gunakan 'click' untuk link <a>, dan 'submit' untuk form!
                const eventType = el.tagName === 'A' ? 'click' : 'submit';
                
                el.addEventListener(eventType, e => {
                    e.preventDefault();
                    console.log('Navigasi dinonaktifkan sementara di mode Preview.');
                });
            });

            // 2. SATU EVENT LISTENER UNTUK SEMUA FITUR (Teks, Gambar, Style, Layout, Warna, Padding)
            window.addEventListener('message', function(event) {
                const data = event.data;
                if (!data || !data.type) return;

                // A. UPDATE STYLE KESELURUHAN (Warna Tema & Font)
                    if (data.type === 'updateStyle') {
                        // 1. Terapkan perubahan variabel CSS segera agar tampilan berubah
                        document.documentElement.style.setProperty(data.variable, data.value);

                        // 2. Jika yang dirubah adalah font, perbarui link Google Fonts
                        if (data.variable === '--font-heading' || data.variable === '--font-body') {
                            const root = document.documentElement;
                            
                            // Ambil nilai font saat ini langsung dari CSS Variables di :root
                            // getComputedStyle memastikan kita mendapatkan nilai terbaru setelah setProperty
                            let hFont = getComputedStyle(root).getPropertyValue('--font-heading').trim().replace(/['"]/g, '');
                            let bFont = getComputedStyle(root).getPropertyValue('--font-body').trim().replace(/['"]/g, '');

                            const fontLink = document.getElementById('google-font-link');
                            if (fontLink) {
                                // Format nama font untuk URL (spasi jadi +)
                                const hUrl = hFont.replace(/ /g, '+');
                                const bUrl = bFont.replace(/ /g, '+');

                                // Muat kedua font sekaligus dalam satu request
                                fontLink.href = `https://fonts.googleapis.com/css?family=${hUrl}:300,400,600,700|${bUrl}:300,400,400i,600,700&display=swap`;    
                            }
                        }
                        // 🚨 TAMBAHAN UNTUK RADIUS & SHADOW
                        if (data.variable === '--radius-base' || data.variable === '--shadow-base') {
                            document.documentElement.style.setProperty(data.variable, data.value);
                        }
                    }

                // B. UPDATE TEXT & LOGIKA KONTEN SECTION
                else if (data.type === 'updateSection') {
                    if (data.key === 'limit') {
                        const newLimit = parseInt(data.value);
                        document.querySelectorAll('.product-item').forEach((item, index) => {
                            item.style.setProperty('display', (index < newLimit) ? 'block' : 'none', 'important');
                        });
                    } else if (data.key.includes('icon')) {
                        const el = document.querySelector(`[data-section-id="${data.sectionId}"][data-key="${data.key}"]`);
                        if (el) el.className = `bi ${data.value} live-editable`;
                    } else if (data.key === 'layout') {
                        const el = document.querySelector(`[data-section-id="${data.sectionId}"][data-key="layout"]`);
                        if (el) data.value === 'image_right' ? el.classList.add('flex-row-reverse') : el.classList.remove('flex-row-reverse');
                    } else if (data.key === 'button_link') {
                        const el = document.querySelector(`[data-section-id="${data.sectionId}"][data-link-key="button_link"]`);
                        if (el) el.href = data.value;
                    } else {
                        // Teks & Gambar Biasa
                        const elements = document.querySelectorAll(`[data-section-id="${data.sectionId}"][data-key="${data.key}"]`);
                        elements.forEach(el => {
                            if (el.tagName === 'IMG') el.src = data.value;
                            else if (el.tagName !== 'DIV' && el.tagName !== 'SECTION') {
                                el.innerHTML = data.value.replace(/\n/g, '<br>');
                                
                                // 👇 LOGIKA LIVE PREVIEW FAQ, TESTIMONIAL & FEATURES 👇
                                const wrapper = el.closest('.live-item-wrapper');
                                if (wrapper) {
                                    let hasText = false;
                                    wrapper.querySelectorAll('[data-key]').forEach(child => {
                                        if (child.tagName === 'I') {
                                            // Jika ini ikon, cek apakah class-nya masih punya 'bi-*'
                                            const classes = Array.from(child.classList);
                                            if (classes.some(c => c.startsWith('bi-'))) hasText = true;
                                        } else {
                                            // Bersihkan tanda kutip (") lalu cek apakah masih ada teksnya
                                            let text = child.textContent.replace(/[""]/g, '').trim();
                                            if (text !== '') hasText = true;
                                        }
                                    });
                                    // Munculkan jika ada teks/icon, sembunyikan jika benar-benar kosong
                                    wrapper.style.setProperty('display', hasText ? 'block' : 'none', 'important');
                                }
                            }
                        });
                    }
                }

               // C. UPDATE GAMBAR LOGO / HERO
                else if (data.type === 'updateImage') {
                    if (data.target === 'logo') {
                        const img = document.getElementById('logo-img-preview');
                        const txt = document.getElementById('site-name-text');
                        if (data.action === 'remove') {
                            if(img) img.style.display = 'none';
                            if(txt) txt.style.display = 'inline';
                        } else {
                            if(img) { img.src = data.src; img.style.display = 'inline'; }
                            if(txt) txt.style.display = 'none';
                        }
                    } else if (data.target === 'hero') {
                        // 🚨 FIX 1: Cari section hero berdasarkan tipe datanya (bukan class lama)
                        const heroSection = document.querySelector('h1[data-key="title"]').closest('section');
                        
                        if (heroSection) {
                            // Cari div pembungkus gambar (yang punya position-absolute)
                            let imgDiv = heroSection.querySelector('.position-absolute.top-0.start-0');
                            
                            if (data.action === 'remove') {
                                if (imgDiv) imgDiv.remove(); // Hapus div gambar jika dicentang "Hapus"
                                // Kembalikan warna tombol & teks ke tema awal
                                heroSection.querySelectorAll('h1, p').forEach(el => { el.style.color = 'var(--text-base)'; });
                                const btn = heroSection.querySelector('.btn');
                                if (btn) { btn.style.borderColor = 'var(--text-base)'; btn.style.color = 'var(--text-base)'; }
                                
                            } else {
                                // Jika div gambar belum ada (misal web baru), kita buatkan elemennya
                                if (!imgDiv) {
                                    imgDiv = document.createElement('div');
                                    imgDiv.className = 'position-absolute top-0 start-0 w-100 h-100';
                                    imgDiv.style.zIndex = '0';
                                    imgDiv.style.backgroundSize = 'cover';
                                    imgDiv.style.backgroundPosition = 'center';
                                    
                                    const overlay = document.createElement('div');
                                    overlay.className = 'position-absolute top-0 start-0 w-100 h-100 bg-dark';
                                    overlay.style.opacity = '0.6';
                                    imgDiv.appendChild(overlay);
                                    
                                    heroSection.prepend(imgDiv);
                                }
                                
                                // Setel gambar baru (Base64 dari Builder)
                                imgDiv.style.backgroundImage = `url('${data.src}')`;
                                
                                // Paksa warna teks & tombol jadi putih agar kontras dengan gambar gelap
                                heroSection.querySelectorAll('h1, p').forEach(el => { el.style.color = '#ffffff'; });
                                const btn = heroSection.querySelector('.btn');
                                if (btn) { btn.style.borderColor = '#ffffff'; btn.style.color = '#ffffff'; }
                            }
                        }
                    }
                }

                // D. TOGGLE VISIBILITY & REORDER
                else if (data.type === 'toggleSection') {
                    const sectionEl = document.getElementById(data.sectionId);
                    if (sectionEl) sectionEl.style.display = data.visible ? 'block' : 'none';
                } 
                else if (data.type === 'moveSection') {
                    const sectionEl = document.getElementById(data.sectionId);
                    if (!sectionEl) return;
                    
                    const allSections = Array.from(document.querySelectorAll('.live-section'));
                    const currentIndex = allSections.indexOf(sectionEl);

                    if (data.direction === 'up' && currentIndex > 0) {
                        const prevSection = allSections[currentIndex - 1];
                        // 🚨 FIX: Gunakan fitur modern .before()
                        // Sisipkan seksi ini TEPAT SEBELUM prevSection
                        prevSection.before(sectionEl);
                    } 
                    else if (data.direction === 'down' && currentIndex < allSections.length - 1) {
                        const nextSection = allSections[currentIndex + 1];
                        // 🚨 FIX: Gunakan fitur modern .after()
                        // Sisipkan seksi ini TEPAT SETELAH nextSection
                        nextSection.after(sectionEl);
                    }
                    
                    // Gulir layar dengan halus mengikuti elemen yang berpindah
                    sectionEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                
                // E. UPDATE SETTING (WARNA CUSTOM & PADDING) 
                else if (data.type === 'updateSetting') {
                    const section = document.getElementById(data.sectionId);
                    if (!section) return;

                    // Logika Live Warna
                    if (data.key === 'color_mode' || data.key === 'bg_color' || data.key === 'text_color') {
                        const mode = (data.key === 'color_mode') ? data.value : 'custom';
                        let targetBg = (mode === 'global') ? 'var(--bg-base)' : (data.key === 'bg_color' ? data.value : (data.customBg || section.style.backgroundColor));
                        let targetText = (mode === 'global') ? 'var(--text-base)' : (data.key === 'text_color' ? data.value : (data.customText || ''));

                        section.style.backgroundColor = targetBg;
                        if (targetText) {
                            section.querySelectorAll('h1, h2, h3, h4, h5, h6, p, span, small, a:not(.btn)').forEach(el => { el.style.setProperty('color', targetText, 'important'); });
                            section.querySelectorAll('.btn, .classic-accordion-button').forEach(btn => {
                                btn.style.borderColor = targetText; btn.style.color = targetText;
                            });
                        }
                    } 
                    // Logika Live Padding
                    else if (data.key === 'padding') {
                        ['py-3', 'py-5', 'py-md-5', 'pt-lg-7', 'pb-lg-7'].forEach(cls => section.classList.remove(cls));
                        data.value.split(' ').forEach(cls => { if(cls) section.classList.add(cls); });
                    }
                }
            });
        }
    </script>
    <script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Cari SEMUA formulir di halaman ini
    // (Kita kecualikan form dengan class 'no-loader' jika sewaktu-waktu Anda tidak ingin form tertentu dikunci)
    const forms = document.querySelectorAll('form:not(.no-loader)');

    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            
            // 2. Cek apakah form sudah lolos validasi HTML bawaan (misal: atribut 'required')
            // Jika belum valid, jangan kunci tombolnya agar browser bisa memunculkan peringatan merah.
            if (!form.checkValidity()) {
                return; 
            }

            // 3. Cari tombol Submit di dalam form yang sedang diklik ini
            const submitBtn = form.querySelector('button[type="submit"]');
            
            if (submitBtn && !submitBtn.disabled) {
                // 4. Kunci tombol agar tidak bisa di-klik ganda (Double Submit Prevention)
                submitBtn.disabled = true;

                // 5. Ubah visual tombol menjadi efek Loading (Spinner Bootstrap)
                // Simpan tinggi tombol agar tidak "berkedut" saat teksnya diganti
                const btnHeight = submitBtn.offsetHeight; 
                submitBtn.style.height = btnHeight + 'px'; 
                
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';
            }
        });
    });
});
</script>
</body>

</html>