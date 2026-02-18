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
        .btn-primary-custom { background-color: var(--primary-color); border-color: var(--primary-color); color: white; }
        .btn-primary-custom:hover { opacity: 0.9; color: white; }
        .btn-outline-secondary-custom { color: var(--secondary-color); border-color: var(--secondary-color); }
        .btn-outline-secondary-custom:hover { background-color: var(--secondary-color); color: white; }
        .hero-section { background-color: var(--hero-bg-color); color: white; padding: 80px 0; margin: 40px 0; background-size: cover; background-position: center; }
        .card-img-top { width: 100%; aspect-ratio: var(--ratio-product); object-fit: cover; }
        .no-arrow::-webkit-outer-spin-button, .no-arrow::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        .no-arrow { -moz-appearance: textfield; }
        .hover-white:hover { color: white !important; text-decoration: underline !important; }
        
        /* Style Khusus Search Result Dropdown */
        .search-results-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1000;
            display: none; /* Hidden by default */
            max-height: 400px;
            overflow-y: auto;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
        .search-results-dropdown.show { display: block; }
    </style>
</head>
<body>
@php
    if ($website->custom_domain) {
        $storeUrl = 'http://' . $website->custom_domain . ':8000';
    } else {
        $appUrl = env('APP_URL');
        $cleanAppUrl = str_replace(['http://', 'https://'], '', $appUrl);
        $storeUrl = 'http://' . $website->subdomain . '.' . $cleanAppUrl;
        if (str_contains($appUrl, ':')) { $storeUrl = 'http://' . $website->subdomain . '.localhost:8000'; }
    }
@endphp

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container gap-lg-4">
            {{-- LOGO --}}
            <a class="navbar-brand fw-bold me-0" href="#">
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
                <form action="{{ route('store.products', $website->subdomain) }}" method="GET" id="desktop-search-form">
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
                        $navMenus = $website->navigation_menu ?? [['label' => 'Beranda', 'url' => '#'], ['label' => 'Produk', 'url' => '#products']];
                    @endphp
                   @foreach($navMenus as $menu)
                        <li class="nav-item">
                            @php
                                $url = $menu['url'];
                                $href = $url; // Default untuk link eksternal (https://...)

                                // KASUS 1: Anchor Link (#) - Scroll di halaman Home
                                if (str_starts_with($url, '#')) {
                                    if (!request()->routeIs('store.home')) {
                                        // Jika sedang tidak di home, arahkan ke home dulu + anchor
                                        $href = route('store.home', $website->subdomain) . $url; 
                                    }
                                } 
                                // KASUS 2: Internal Path (/) - Halaman seperti /blog, /products
                                elseif (str_starts_with($url, '/')) {
                                    // FIX: Gunakan helper 'url' manual agar path-nya bersih
                                    // Hasil: http://domain.com/s/elecjos/blog
                                    $href = url('/s/' . $website->subdomain . $url);
                                }
                            @endphp

                            <a class="nav-link text-dark" href="{{ $href }}">
                                {{ $menu['label'] }}
                            </a>
                        </li>
                        @endforeach

                    {{-- CART BUTTON --}}
                    @php
                        $cartKey = 'cart_' . $website->id;
                        $cartSession = session()->get($cartKey, []);
                        $cartCount = array_reduce($cartSession, fn($carry, $item) => $carry + ($item['quantity'] ?? $item['qty'] ?? 0), 0);
                    @endphp
                    <li class="nav-item ms-lg-2 mt-3 mt-lg-0">
                        <a href="{{ route('store.cart', $website->subdomain) }}" class="btn btn-primary rounded-pill px-4 position-relative w-100">
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

    <footer class="bg-dark text-white pt-5 pb-4 mt-5">
        {{-- ... Footer Content Sama Seperti Sebelumnya ... --}}
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h5 class="fw-bold text-primary-custom mb-3">{{ $website->site_name }}</h5>
                    <p class="small text-secondary">{{ $website->hero_subtitle ?? 'Platform toko online terpercaya.' }}</p>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold mb-3">Hubungi Kami</h6>
                    <ul class="list-unstyled small text-secondary">
                        @if($website->address) <li class="mb-2"><i class="bi bi-geo-alt me-2"></i> {{ $website->address }}</li> @endif
                        @if($website->whatsapp_number) <li class="mb-2"><i class="bi bi-telephone me-2"></i> +62 {{ $website->whatsapp_number }}</li> @endif
                        @if($website->email_contact) <li class="mb-2"><i class="bi bi-envelope me-2"></i> {{ $website->email_contact }}</li> @endif
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold mb-3">Menu</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="#" class="text-secondary text-decoration-none hover-white">Beranda</a></li>
                    </ul>
                </div>
            </div>
            <hr class="border-secondary mt-4">
            <div class="text-center small text-secondary">&copy; {{ date('Y') }} {{ $website->site_name }}.</div>
        </div>
    </footer>

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
                let baseUrl = "{{ route('store.products', $website->subdomain) }}";
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

    // ... (Kode Preview Mode Editor Anda tetap di sini) ...
    const isPreview = (window.self !== window.top);
    // ...
    </script>
    @stack('scripts')
</body>
</html>