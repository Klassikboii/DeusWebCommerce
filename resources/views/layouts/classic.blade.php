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
        $fontHeading = $website->theme_config['typography']['heading'] ?? 'Playfair Display';
        $fontBody = $website->theme_config['typography']['body'] ?? 'Inter';
        
        // Gabungkan nama font untuk URL Google Fonts
        $urlHeading = str_replace(' ', '+', $fontHeading);
        $urlBody = str_replace(' ', '+', $fontBody);
    @endphp

    {{-- Memuat font dengan semua ketebalan (300, 400, 600, 700, 900) dan gaya (italic) --}}
{{-- 🚨 MENGGUNAKAN API V1: Lebih aman dan anti-error untuk Web Builder --}}
    <link id="google-font-link" href="https://fonts.googleapis.com/css?family={{ $urlHeading }}:300,400,600,700|{{ $urlBody }}:300,400,400i,600,700&display=swap" rel="stylesheet">    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    @php
    $headingFont = $website->theme_config['typography']['heading'] ?? 'Playfair Display';
    $bodyFont = $website->theme_config['typography']['body'] ?? 'Inter';
    
    $headingUrl = str_replace(' ', '+', $headingFont);
    $bodyUrl = str_replace(' ', '+', $bodyFont);
@endphp

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    

    @if($website->favicon)
        <link rel="icon" href="{{ asset('storage/'.$website->favicon) }}">
    @endif
    {{-- Memuat kedua keluarga font dalam satu request (lebih efisien) --}}
<link id="google-font-link" href="https://fonts.googleapis.com/css2?family={{ $headingUrl }}:wght@400;700&family={{ $bodyUrl }}:wght@400;700&display=swap" rel="stylesheet">
    <style>
        html { scroll-behavior: smooth; }
        
        :root {
            --primary-color: {{ $website->theme_config['colors']['primary'] ?? '#000000' }}; 
            --secondary-color: {{ $website->theme_config['colors']['secondary'] ?? '#6c757d' }};
            --hero-bg-color: {{ $website->theme_config['colors']['bg_hero'] ?? '#f9fafb' }};
            --font-heading: '{{ $fontHeading }}', serif;
            --font-body: '{{ $fontBody }}', sans-serif;
            --ratio-product: {{ $website->theme_config['shapes']['product_ratio'] ?? '1/1' }};
            /* 👇 BIARKAN DINAMIS, TAPI BERI DEFAULT KLASIK JIKA KOSONG 👇 */
            --radius-base: {{ $website->theme_config['shapes']['radius'] ?? '0px' }};
            --shadow-base: {{ $website->theme_config['shapes']['shadow'] ?? 'none' }};
            --bg-base: {{ $website->theme_config['colors']['bg_base'] ?? '#ffffff' }};
            --text-base: {{ $website->theme_config['colors']['text_base'] ?? '#212529' }};
        }
        /* 👇 Terapkan secara paksa di komponennya, BUKAN di variable root-nya 👇 */
        * { border-radius: var(--radius-base) !important; }

        body { 
            font-family: var(--font-body); 
            background-color: var(--bg-base);
            color: var(--text-base);
        }

        /* Tipografi Classic */
        h1, h2, h3, h4, h5, .serif { font-family: var(--font-heading) }
        .tracking-widest { letter-spacing: 0.15em; }
        .tracking-wider { letter-spacing: 0.1em; }

        /* Paksa semua elemen Bootstrap menjadi kaku/kotak */
        * { border-radius: 0px !important; }

        /* Kustomisasi Logo & Jarak */
        .site-logo { height: 40px; width: auto; object-fit: contain; }
        /* .template-section { padding-top: 80px; padding-bottom: 80px; } */
        
        /* Navigasi Hover */
        .hover-dark { transition: color 0.3s ease; }
        .hover-dark:hover { color: #000 !important; }

        /* Trik Overrides Bootstrap */
        .bg-white, .bg-light { background-color: var(--bg-base) !important; }
        .text-dark { color: var(--text-base) !important; }
        .text-muted { color: var(--secondary-color) !important; opacity: 0.9; }
        .card, .dropdown-menu { 
            background-color: var(--bg-base) !important; 
            color: var(--text-base) !important; 
            border: 1px solid #eee !important;
            box-shadow: none !important;
        }
        /* .hero-section { background-color: var(--hero-bg-color); color: white; padding: 80px 0; margin: 40px 0; background-size: cover; background-position: center; } */
        /* Terapkan secara agresif ke elemen-elemen penting */
    
        /* 1. Semua Card/Kotak */
        .card, .section-box, .feature-card {
            border-radius: var(--radius-base) !important;
            box-shadow: var(--shadow-base) !important;
            border: none !important; /* Opsional: hilangkan border agar shadow lebih cantik */
        }

        /* 2. Semua Tombol */
        .btn {
            border-radius: var(--radius-base) !important;
        }

        /* 3. Input Form */
        .form-control, .form-select {
            border-radius: var(--radius-base) !important;
        }

        /* 4. Gambar Produk */
        .product-img-wrapper img {
            border-radius: var(--radius-base);
        }
        /* Tombol Classic */
        .btn-classic {
            border: 1px solid #000;
            padding: 12px 30px;
            text-transform: uppercase;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 2px;
            background-color: #000;
            color: #fff;
            transition: all 0.3s;
        }
        .btn-classic:hover { background-color: #fff; color: #000; }

        /* Search Results Dropdown */
        .search-results-dropdown {
            position: absolute; top: 100%; left: 0; right: 0; z-index: 1000;
            display: none; max-height: 400px; overflow-y: auto; border: 1px solid #000;
        }
        .search-results-dropdown.show { display: block; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    {{-- BANNER TOKO TUTUP (Dipindah ke dalam body agar layout tidak error) --}}
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
            $storeUrl = 'http://' . $website->custom_domain . ':8000';
        } else {
            $appUrl = env('APP_URL');
            $cleanAppUrl = str_replace(['http://', 'https://'], '', $appUrl);
            $storeUrl = 'http://' . $website->active_domain . '.' . $cleanAppUrl;
            if (str_contains($appUrl, ':')) { $storeUrl = 'http://' . $website->active_domain . '.localhost:8000'; }
        }
    @endphp
    @php
        // Amankan data menu
        $rawMenu = $website->navigation_menu;
        $navMenus = is_string($rawMenu) ? json_decode($rawMenu, true) : $rawMenu;
        if (!is_array($navMenus) || empty($navMenus)) {
            $navMenus = [['label' => 'Home', 'url' => '#']];
        }
    @endphp

    <header class="bg-white border-bottom sticky-top py-3" style="box-shadow: var(--shadow-base); z-index: 1050;">
        <div class="container">
            <div class="row align-items-center">
                
                <div class="col-3 col-lg-5 d-flex align-items-center justify-content-start">
                    
                    {{-- Tombol Hamburger untuk Mobile/Tablet --}}
                    <button class="navbar-toggler d-lg-none border-0 shadow-none p-0 me-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#classicSidebar">
                        <i class="bi bi-list fs-2 text-dark"></i>
                    </button>

                    {{-- Menu Desktop (Hanya muncul di layar besar) --}}
                    <nav class="d-none d-lg-flex gap-4 flex-wrap">
                        @foreach($navMenus as $menu)
                            <a class="text-decoration-none text-dark hover-dark text-uppercase small fw-bold tracking-widest" href="{{ url('/' . ltrim($menu['url'], '/')) }}">
                                {{ $menu['label'] }}
                            </a>
                        @endforeach
                    </nav>
                </div>

                <div class="col-6 col-lg-2 text-center">
                    <a href="/" class="text-decoration-none text-dark d-inline-block">
                        @if($website->logo)
                            <img src="{{ Storage::url($website->logo) }}" alt="Logo" class="site-logo" style="max-height: 45px; width: auto; object-fit: contain;">
                        @else
                            <span class="fs-4 fw-bold text-uppercase serif tracking-widest">{{ $website->site_name }}</span>
                        @endif
                    </a>
                </div>

                <div class="col-3 col-lg-5 d-flex align-items-center justify-content-end gap-3 gap-xl-4">
                    
                    {{-- Form Search Desktop --}}
                    <form action="{{ route('store.products') }}" method="GET" class="d-none d-xl-flex align-items-center border-bottom border-dark pb-1 position-relative" style="max-width: 150px;">
                        <input type="text" name="search" id="desktop-search-input" placeholder="CARI..." class="border-0 bg-transparent outline-none small text-uppercase w-100" style="font-size: 11px; box-shadow: none;" autocomplete="off">
                        <button type="submit" class="btn btn-link text-dark p-0 border-0"><i class="bi bi-search"></i></button>
                        
                        <div id="desktop-search-results" class="search-results-dropdown bg-white mt-2 shadow-sm position-absolute top-100 start-0 w-100">
                            <div id="desktop-search-loading" class="text-center py-3 d-none"><div class="spinner-border spinner-border-sm text-dark"></div></div>
                            <div id="desktop-search-content"></div>
                        </div>
                    </form>

                    {{-- Search Icon Mobile/Tablet --}}
                    <a href="#" class="d-xl-none text-dark"><i class="bi bi-search fs-5"></i></a> 

                    {{-- Akun Pelanggan --}}
                    @if(Auth::guard('customer')->check())
                        <div class="dropdown d-none d-md-block">
                            <a class="text-decoration-none text-dark fw-bold text-uppercase small tracking-wider dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                {{ strtok(Auth::guard('customer')->user()->name, ' ') }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-0 border-dark mt-3">
                                <li><a class="dropdown-item py-2 small fw-bold text-uppercase" href="{{ route('store.account') }}">Riwayat</a></li>
                                <li><hr class="dropdown-divider border-dark opacity-25"></li>
                                <li>
                                    <form action="{{ route('store.logout') }}" method="POST" class="m-0">@csrf
                                        <button type="submit" class="dropdown-item py-2 small fw-bold text-uppercase text-danger">Keluar</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('store.login') }}" class="d-none d-md-block text-dark text-uppercase small fw-bold tracking-wider text-decoration-none">Login</a>
                    @endif
                    
                    {{-- Akun Icon Mobile --}}
                    <a href="{{ route('store.login') }}" class="d-md-none text-dark"><i class="bi bi-person fs-5"></i></a>

                    {{-- Keranjang (Cart) --}}
                    @php
                        $cartKey = 'cart_' . $website->id;
                        $cartCount = array_reduce(session()->get($cartKey, []), fn($carry, $item) => $carry + ($item['quantity'] ?? 0), 0);
                    @endphp
                    <a href="{{ route('store.cart') }}" class="text-dark position-relative text-decoration-none d-flex align-items-center gap-1">
                        <i class="bi bi-bag fs-5"></i>
                        <span class="d-none d-md-inline small fw-bold tracking-wider">({{ $cartCount }})</span>
                        @if($cartCount > 0)
                            <span class="d-md-none position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.55rem;">{{ $cartCount }}</span>
                        @endif
                    </a>
                </div>

            </div>
        </div>

        <div class="offcanvas offcanvas-start bg-white" data-bs-scroll="true" tabindex="-1" id="classicSidebar" style="width: 300px; z-index: 1060;">
            <div class="offcanvas-header border-bottom py-4">
                <h5 class="offcanvas-title serif tracking-widest text-uppercase fs-6">Menu Navigasi</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="navbar-nav w-100 gap-2">
                    @foreach($navMenus as $menu)
                        <li class="nav-item border-bottom border-light">
                            <a class="nav-link text-dark hover-dark text-uppercase small fw-bold tracking-widest py-3"href="{{ url('/' . ltrim($menu['url'], '/')) }}">
                                {{ $menu['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </header>

    @if(session('success'))
        <div class="container mt-4 text-center"><div class="alert alert-success d-inline-block px-5 border-0" style="background-color: #000; color: white;"><i class="bi bi-check-circle me-2"></i> {{ session('success') }}</div></div>
    @endif
    @if(session('error'))
        <div class="container mt-4 text-center"><div class="alert alert-danger d-inline-block px-5 border-0"><i class="bi bi-exclamation-circle me-2"></i> {{ session('error') }}</div></div>
    @endif

    <main class="flex-grow-1">
        @yield('content')
    </main>

    {{-- FOOTER CLASSIC BERBASIS BOOTSTRAP --}}
    <footer class="pt-5 pb-4 mt-5 border-top" style="background-color: #f9f9f9;">
        <div class="container mt-4">
            <div class="row gy-5 border-bottom border-light pb-5">
                <div class="col-md-4 text-center text-md-start">
                    <h4 class="serif mb-4 text-uppercase tracking-widest">{{ $website->site_name }}</h4>
                    <p class="text-muted small lh-lg" style="max-width: 90%;">
                        {{ $website->description ?? 'Menyediakan pengalaman belanja elektronik terbaik dengan standar kualitas tinggi.' }}
                    </p>
                </div>

                <div class="col-md-4 text-center">
                    <h6 class="serif mb-4 fs-5 text-uppercase tracking-widest">Menu Utama</h6>
                    <div class="d-flex flex-column gap-3">
                        <a href="/" class="text-decoration-none text-muted hover-dark text-uppercase small tracking-wider">Beranda</a>
                        <a href="/products" class="text-decoration-none text-muted hover-dark text-uppercase small tracking-wider">Katalog Produk</a>
                        <a href="/track" class="text-decoration-none text-muted hover-dark text-uppercase small tracking-wider">Lacak Pesanan</a>
                    </div>
                </div>

                <div class="col-md-4 text-center text-md-end">
                    <h6 class="serif mb-4 fs-5 text-uppercase tracking-widest">Hubungi Kami</h6>
                    @if($website->whatsapp_number)
                        <a href="https://wa.me/{{ $website->whatsapp_number }}" target="_blank" class="text-decoration-none btn-classic d-inline-block mb-4">
                            Chat WhatsApp
                        </a>
                    @endif
                    <p class="text-muted small text-uppercase tracking-widest mt-2">{{ $website->address }}</p>
                </div>
            </div>

            <div class="text-center mt-5">
                <p class="text-muted" style="font-size: 10px; letter-spacing: 3px; text-transform: uppercase;">
                    &copy; {{ date('Y') }} {{ $website->site_name }}. All rights reserved. <br>
                    <span class="opacity-50">Powered by DeusWebCommerce</span>
                </p>
            </div>
        </div>
    </footer>

    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->

    {{-- SCRIPT LIVE SEARCH & LIVE EDITOR ANDA (TIDAK ADA YANG DIUBAH) --}}
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

            const getSearchUrl = (query) => {
                let baseUrl = "{{ route('store.products', $website->active_domain) }}";
                let urlObj = new URL(baseUrl);
                urlObj.searchParams.set('search', query);
                urlObj.searchParams.set('type', 'dropdown');
                return urlObj.toString();
            };

            input.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                const query = this.value.trim();

                if (query.length < 2) {
                    loading.classList.add('d-none');
                    if(dropdown) dropdown.classList.remove('show');
                    results.innerHTML = ''; 
                    if(!dropdown) results.innerHTML = '<div class="text-center text-muted small py-3 opacity-50">Ketik minimal 2 huruf...</div>';
                    return;
                }

                loading.classList.remove('d-none');
                if(dropdown) dropdown.classList.add('show');
                if(!dropdown) results.classList.add('d-none');

                debounceTimer = setTimeout(() => {
                    fetch(getSearchUrl(query), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(response => response.text())
                    .then(html => {
                        results.innerHTML = html;
                        loading.classList.add('d-none');
                        if(!dropdown) results.classList.remove('d-none');
                    })
                    .catch(err => {
                        console.error(err);
                        loading.classList.add('d-none');
                    });
                }, 400);
            });

            if (dropdown) {
                document.addEventListener('click', function(e) {
                    if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                        dropdown.classList.remove('show');
                    }
                });
                input.addEventListener('focus', function() {
                    if (this.value.trim().length >= 2) {
                        dropdown.classList.add('show');
                    }
                });
            }
        }

        setupLiveSearch('desktop-search-input', 'desktop-search-content', 'desktop-search-loading', 'desktop-search-results');
        setupLiveSearch('mobile-search-input', 'mobile-search-results', 'mobile-search-loading');
        
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
            document.querySelectorAll('a, form').forEach(el => {
                if (el.tagName === 'A' && el.getAttribute('href').startsWith('#')) return; 
                el.addEventListener('submit', e => {
                    e.preventDefault();
                });
            });

            // 2. SATU EVENT LISTENER UNTUK SEMUA FITUR (Teks, Gambar, Style, Layout, Warna, Padding)
            window.addEventListener('message', function(event) {
                const data = event.data;
                if (!data || !data.type) return;

                // A. UPDATE STYLE KESELURUHAN (Warna Tema & Font)
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
                            else if (el.tagName !== 'DIV' && el.tagName !== 'SECTION') el.innerHTML = data.value.replace(/\n/g, '<br>');
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
                        const heroSimple = document.querySelector('.hero-section');
                        if (data.action === 'remove') {
                            if(heroSimple) {
                                heroSimple.style = "background-color: var(--hero-bg-color); background-image: none; color: var(--primary-color); text-shadow: none;";
                                const p = heroSimple.querySelector('p');
                                if(p) { p.classList.remove('text-white'); p.classList.add('text-secondary'); }
                            }
                        } else {
                            if(heroSimple) {
                                heroSimple.style.backgroundImage = `url('${data.src}')`;
                                heroSimple.style.backgroundColor = 'transparent';
                                heroSimple.style.color = 'white'; 
                                heroSimple.style.textShadow = '0 2px 4px rgba(0,0,0,0.5)';
                                const p = heroSimple.querySelector('p');
                                if(p) { p.classList.remove('text-secondary'); p.classList.add('text-white'); }
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
                    if (sectionEl) {
                        const parent = sectionEl.parentNode;
                        if (data.direction === 'up' && sectionEl.previousElementSibling) parent.insertBefore(sectionEl, sectionEl.previousElementSibling);
                        else if (data.direction === 'down' && sectionEl.nextElementSibling) parent.insertBefore(sectionEl, sectionEl.nextElementSibling.nextElementSibling);
                    }
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
                            section.querySelectorAll('h1, h2, h3, h4, h5, h6, p, span, small, a:not(.btn)').forEach(el => { el.style.color = targetText; });
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
                    // --- TAMBAHAN: Logika Live Tipografi ---
                    if (data.key === 'text_transform') {
                        section.style.textTransform = data.value;
                    } 
                    else if (data.key === 'font_weight') {
                        section.style.fontWeight = data.value;
                    } 
                    else if (data.key === 'font_style') {
                        section.style.fontStyle = data.value;
                    } 
                    else if (data.key === 'heading_size') {
                        // Hapus semua class ukuran (fs-1 sampai fs-6) pada judul
                        ['fs-1', 'fs-2', 'fs-3', 'fs-4', 'fs-5', 'fs-6'].forEach(cls => {
                            section.querySelectorAll('h1, h2, h3, h4, h5, h6').forEach(h => h.classList.remove(cls));
                        });
                        // Tambahkan class ukuran yang baru
                        section.querySelectorAll('h1, h2, h3, h4, h5, h6').forEach(h => h.classList.add(data.value));
                    }
                    // --- TAMBAHAN: Logika Live Tipografi ---
                    if (data.key === 'text_transform') {
                        section.style.textTransform = data.value;
                    } 
                    else if (data.key === 'font_weight') {
                        section.style.fontWeight = data.value;
                    } 
                    else if (data.key === 'font_style') {
                        section.style.fontStyle = data.value;
                    } 
                    else if (data.key === 'heading_size') {
                        // Hapus semua class ukuran (fs-1 sampai fs-6) pada judul
                        ['fs-1', 'fs-2', 'fs-3', 'fs-4', 'fs-5', 'fs-6'].forEach(cls => {
                            section.querySelectorAll('h1, h2, h3, h4, h5, h6').forEach(h => h.classList.remove(cls));
                        });
                        // Tambahkan class ukuran yang baru
                        section.querySelectorAll('h1, h2, h3, h4, h5, h6').forEach(h => h.classList.add(data.value));
                    }
                }
            });
        }
    </script>
</body>
</html>