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
    <link id="google-font-link" href="https://fonts.googleapis.com/css2?family={{ $fontUrl }}:ital,wght@0,300;0,400;0,600;0,700;0,900;1,400;1,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
   @php
        // Ambil nama font dari JSON
        $fontName = $website->theme_config['typography']['main'] ?? 'Inter';
        // Ubah spasi menjadi + untuk URL Google Fonts (misal: "Playfair Display" jadi "Playfair+Display")
        $fontUrl = str_replace(' ', '+', $fontName);
    @endphp
    <link href="https://fonts.googleapis.com/css2?family={{ $fontUrl }}:wght@400;600;700&display=swap" rel="stylesheet">
    
    @if($website->logo)
        <link rel="icon" type="image/png" href="{{ asset('storage/'.$website->logo) }}">
    @endif

    <style>
        html { scroll-behavior: smooth; }
       :root {
            /* Panggil dari JSON, jika kosong gunakan warna bawaan */
            --primary-color: {{ $website->theme_config['colors']['primary'] ?? '#0d6efd' }}; 
            --secondary-color: {{ $website->theme_config['colors']['secondary'] ?? '#6c757d' }};
            --hero-bg-color: {{ $website->theme_config['colors']['bg_hero'] ?? '#333333' }};
            --font-heading: '{{ $headingFont }}', serif;
        --font-body: '{{ $bodyFont }}', sans-serif;
            --ratio-product: {{ $website->theme_config['shapes']['product_ratio'] ?? '1/1' }};
            /* 👇 TAMBAHAN BARU: Variabel Radius & Shadow */
            --radius-base: {{ $website->theme_config['shapes']['radius'] ?? '0.5rem' }};
            --shadow-base: {{ $website->theme_config['shapes']['shadow'] ?? '0 0.125rem 0.25rem rgba(0,0,0,0.075)' }};
            --bg-base: {{ $website->theme_config['colors']['bg_base'] ?? '#ffffff' }};
            --text-base: {{ $website->theme_config['colors']['text_base'] ?? '#212529' }};
        }
        
        body { 
           font-family: var(--font-body);
            background-color: var(--bg-base);
            color: var(--text-base);
        }
        h1, h2, h3, h4, h5, h6, .serif { font-family: var(--font-heading); }
        /* 👇 TRIK AJAIB: Timpa class Bootstrap agar mengikuti tema warna pilihan klien */
        .bg-white, .bg-light { background-color: var(--bg-base) !important; }
        .text-dark { color: var(--text-base) !important; }
        .text-muted { color: var(--secondary-color) !important; opacity: 0.9; }
        
        /* Pastikan elemen dalam kartu juga mengikuti warna tema */
        .card, .accordion-item, .accordion-button, .dropdown-menu { 
            background-color: var(--bg-base) !important; 
            color: var(--text-base) !important; 
        }
        /* 👇 TAMBAHAN BARU: Paksa Bootstrap menggunakan Radius dan Shadow kustom kita */
        .card, .accordion-item, .img-fluid.rounded, .btn:not(.rounded-pill) {
            border-radius: var(--radius-base) !important;
        }
        .shadow-sm, .card {
            box-shadow: var(--shadow-base) !important;
            border: none !important; /* Hilangkan garis pinggir agar shadow lebih menonjol */
        }
        .navbar-brand { 
            font-family: 'Helvetica', sans-serif; 
            letter-spacing: 2px; 
            text-transform: uppercase; 
            color: var(--primary-color);
        }
        .nav-link { 
            color: #333; 
            font-size: 0.85rem; 
            letter-spacing: 1px;
        }
        .nav-link:hover { color: var(--primary-color); }

        .text-primary-custom { color: var(--primary-color) !important; }
        .bg-primary-custom { background-color: var(--hero-bg-color) !important; }

        .btn-custom { 
            background-color: var(--primary-color); 
            border-color: var(--primary-color); 
            color: white;
            border-radius: 0; 
        }
        .btn-custom:hover { opacity: 0.9; color: white; }

        .hero-section-simple {
            padding: 80px 0;
            margin: 40px 0;
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
        }
        .no-arrow::-webkit-outer-spin-button, .no-arrow::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        .no-arrow { -moz-appearance: textfield; }

        /* Search Dropdown Styling */
        .search-results-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1000;
            display: none;
            max-height: 400px;
            overflow-y: auto;
            border-radius: 0 !important; /* Kotak ala Simple Theme */
            margin-top: 5px;
        }
        .search-results-dropdown.show { display: block; }
    </style>
    @stack('styles')
</head>
<body>
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

    <nav class="navbar navbar-expand-lg bg-white border-bottom py-4 sticky-top">
        <div class="container flex-column position-relative">
            
            {{-- LOGO --}}
            <a class="navbar-brand fw-bold fs-3 mb-3" href="{{ $storeUrl }}">
                <img src="{{ asset('storage/'.$website->logo) }}" id="logo-image" style="height: 50px; {{ $website->logo ? '' : 'display:none;' }}">
                <span id="logo-text" style="{{ $website->logo ? 'display:none;' : '' }}">{{ $website->site_name }}</span>
            </a>

            {{-- 
                SEARCH BAR DESKTOP (POSISI DI KANAN ATAS ABSOLUTE) 
                Agar tidak mengganggu layout logo tengah
            --}}
            <div class="d-none d-lg-block position-absolute end-0 top-50 translate-middle-y me-3" style="width: 250px;">
                <div class="position-relative">
                    <input type="text" id="desktop-search-input" class="form-control form-control-sm border-0 border-bottom rounded-0 ps-0" placeholder="Search..." autocomplete="off">
                    <i class="bi bi-search position-absolute end-0 top-50 translate-middle-y text-muted small"></i>
                    
                    {{-- Dropdown Hasil --}}
                    <div class="dropdown-menu w-100 shadow border-0 p-0 overflow-hidden search-results-dropdown" id="desktop-search-results">
                        <div id="desktop-search-loading" class="text-center py-3 d-none"><div class="spinner-border spinner-border-sm text-secondary" role="status"></div></div>
                        <div id="desktop-search-content"></div>
                    </div>
                </div>
            </div>

            <button class="navbar-toggler mb-3 border-0" type="button" data-bs-toggle="collapse" data-bs-target="#simpleNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="simpleNav">
                <ul class="nav justify-content-center small text-uppercase gap-4 align-items-center">
                    
                    {{-- SEARCH MOBILE (Di dalam menu collapse) --}}
                    <li class="nav-item d-lg-none w-100 mb-3">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0 rounded-0"><i class="bi bi-search"></i></span>
                            <input type="text" id="mobile-search-input" class="form-control bg-light border-0 rounded-0" placeholder="CARI PRODUK..." autocomplete="off">
                        </div>
                        <div id="mobile-search-loading" class="text-center py-2 d-none"><div class="spinner-border spinner-border-sm text-secondary" role="status"></div></div>
                        <div id="mobile-search-results" class="bg-white border mt-1 shadow-sm" style="display:none;"></div>
                    </li>

                    @php $navMenus = $website->navigation_menu ?? [['label' => 'Home', 'url' => '#'], ['label' => 'Shop', 'url' => '#shop']]; @endphp
                   @foreach($navMenus as $menu)
                        <li class="nav-item">
                            @php
                                $url = $menu['url'];
                                $href = $url; // Default untuk link eksternal (https://...)

                                // KASUS 1: Anchor Link (#) - Scroll di halaman Home
                                if (str_starts_with($url, '#')) {
                                    if (!request()->routeIs('store.home')) {
                                        // Jika sedang tidak di home, arahkan ke home dulu + anchor
                                        $href = route('store.home') . $url; 
                                    }
                                } 
                                // KASUS 2: Internal Path (/) - Halaman seperti /blog, /products
                                elseif (str_starts_with($url, '/')) {
                                    // FIX: Gunakan helper 'url' manual agar path-nya bersih
                                    // Hasil: http://domain.com/s/elecjos/blog
                                    $href = url($url);
                                }
                            @endphp

                            <a class="nav-link text-dark" href="{{ $href }}">
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
                    <li class="nav-item ms-lg-3 mt-3 mt-lg-0">
                        <a href="{{ route('store.cart') }}" class="btn btn-primary rounded-pill px-4 btn-sm">
                            <i class="bi bi-cart"></i> Cart 
                            @if($cartCount > 0)
                            <span class="badge bg-white text-primary ms-1 rounded-pill">{{ $cartCount }}</span>
                            @endif
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    @if(session('success')) <div class="container mt-4 text-center"><div class="alert alert-success d-inline-block px-5 rounded-0 border-0" style="background-color: var(--secondary-color); color: white;">{{ session('success') }}</div></div> @endif
    @if(session('error')) <div class="container mt-4 text-center"><div class="alert alert-danger d-inline-block px-5 rounded-0 border-0">{{ session('error') }}</div></div> @endif

    <main>@yield('content')</main>

    <footer class="bg-light text-dark pt-5 pb-4 mt-5 border-top">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-5">
                    <h5 class="fw-bold text-uppercase mb-3" style="letter-spacing: 1px;">{{ $website->site_name }}</h5>
                    {{-- 🚨 LOGIKA GEMBOK BRANDING --}}
                            @php
                                // Cek apakah Klien berhak menghapus branding
                                // Menggunakan === true karena kita sudah cast kolom ini jadi boolean di Model
                                $canRemoveBranding = $website->subscription?->package?->remove_branding === true;
                            @endphp

                            {{-- Jika TIDAK BISA remove branding, maka tampilkan iklan SaaS Anda --}}
                            @if(!$canRemoveBranding)
                                <div class="mt-2 text-muted small">
                                    Powered by <a href="https://shopadmin.ashop.asia" target="_blank" class="fw-bold text-decoration-none text-primary">Elecios WebCommerce</a>
                                </div>
                            @endif
                    @if($website->address) <p class="small text-muted mb-3"><i class="bi bi-geo-alt-fill me-1"></i> {{ $website->address }}</p> @endif
                    <div class="d-flex gap-2">
                        @if($website->whatsapp_number) <a href="https://wa.me/62{{ $website->whatsapp_number }}" target="_blank" class="text-dark text-decoration-none border px-3 py-1 small"><i class="bi bi-whatsapp"></i> WhatsApp</a> @endif
                        @if($website->email_contact) <a href="mailto:{{ $website->email_contact }}" class="text-dark text-decoration-none border px-3 py-1 small"><i class="bi bi-envelope"></i> Hubungi</a> @endif
                    </div>
                </div>
                <div class="col-md-3 offset-md-1">
                    <h6 class="fw-bold text-uppercase mb-3 small">Eksplorasi</h6>
                    <ul class="list-unstyled small">
                        @foreach($website->navigation_menu ?? [] as $menu)
                            <li class="mb-2"><a href="{{ $menu['url'] }}" class="text-muted text-decoration-none">{{ $menu['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-md-3 text-md-end">
                    <p class="small text-muted mb-0">&copy; {{ date('Y') }} {{ $website->site_name }}<br>All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- SCRIPT LIVE SEARCH (SAMA SEPERTI MODERN, TAPI DISESUAIKAN ID NYA) --}}
    <script>
    document.addEventListener("DOMContentLoaded", function() {
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
                    if(!dropdown) results.style.display = 'none';
                    return;
                }

                loading.classList.remove('d-none');
                if(dropdown) dropdown.classList.add('show');
                if(!dropdown) results.style.display = 'none';

                debounceTimer = setTimeout(() => {
                    fetch(getSearchUrl(query), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(response => response.text())
                    .then(html => {
                        results.innerHTML = html;
                        loading.classList.add('d-none');
                        if(!dropdown) results.style.display = 'block';
                    })
                    .catch(err => { console.error(err); loading.classList.add('d-none'); });
                }, 400);
            });

            if (dropdown) {
                document.addEventListener('click', function(e) {
                    if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                        dropdown.classList.remove('show');
                    }
                });
                input.addEventListener('focus', function() {
                    if (this.value.trim().length >= 2) dropdown.classList.add('show');
                });
            }
        }

        // Setup Desktop (Input Garis Bawah di Kanan Atas)
        setupLiveSearch('desktop-search-input', 'desktop-search-content', 'desktop-search-loading', 'desktop-search-results');

        // Setup Mobile (Input Blok di dalam Menu)
        setupLiveSearch('mobile-search-input', 'mobile-search-results', 'mobile-search-loading');
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
                                    fontLink.href = `https://fonts.googleapis.com/css2?family=${hUrl}:wght@700;900&family=${bUrl}:ital,wght@0,400;0,700;1,400&display=swap`;
                                }
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
                }
            });
        }
    </script>
</html>