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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
   @php
        // Ambil nama font dari JSON
        $fontName = $website->theme_config['typography']['main'] ?? 'Inter';
        // Ubah spasi menjadi + untuk URL Google Fonts (misal: "Playfair Display" jadi "Playfair+Display")
        $fontUrl = str_replace(' ', '+', $fontName);
    @endphp
    {{-- 🚨 MENGGUNAKAN API V1: Lebih aman dan anti-error untuk Web Builder --}}
    <link id="google-font-link" href="https://fonts.googleapis.com/css?family={{ $headingUrl }}:300,400,600,700|{{ $bodyUrl }}:300,400,400i,600,700&display=swap" rel="stylesheet">
    
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
   @php
        $rawMenu = $website->navigation_menu;
        $navMenus = is_string($rawMenu) ? json_decode($rawMenu, true) : $rawMenu;
        // 🚨 KUNCI PERBAIKAN: Jika menu kosong, beri default yang seragam dan PASTI MUNCUL!
        if (!is_array($navMenus) || empty($navMenus)) {
            $navMenus = [
                ['label' => 'Beranda', 'url' => '/'],
                ['label' => 'Produk', 'url' => route('store.products')],
                ['label' => 'Blog', 'url' => route('storefront.blog.index')],
            ];
        }
        $menuCount = count($navMenus);
        
        // Logika Breakpoint: Jika menu > 5, paksa jadi hamburger di semua layar.
        $expandClass = 'navbar-expand-lg'; 
        if ($menuCount > 5) { $expandClass = ''; } 
    @endphp

    <header class="bg-white border-bottom sticky-top" style="box-shadow: var(--shadow-base)">
        
        <div class="container py-3 d-flex align-items-center justify-content-between">
            
            <div class="header-left d-none d-md-flex" style="flex: 1;">
                <form action="{{ route('store.products') }}" method="GET" class="d-flex align-items-center border-bottom border-dark pb-1 position-relative" style="max-width: 180px;">
                    <input type="text" name="search" id="desktop-search-input" placeholder="CARI..." class="border-0 bg-transparent outline-none small text-uppercase w-100" style="font-size: 11px; box-shadow: none;" autocomplete="off">
                    <button type="submit" class="btn btn-link text-dark p-0 border-0"><i class="bi bi-search"></i></button>
                    <div id="desktop-search-results" class="search-results-dropdown bg-white mt-2 shadow-sm">
                        <div id="desktop-search-loading" class="text-center py-3 d-none"><div class="spinner-border spinner-border-sm text-dark"></div></div>
                        <div id="desktop-search-content"></div>
                    </div>
                </form>
            </div>

            <div class="header-center text-center" style="flex: 1;">
                <a href="/" class="text-decoration-none text-dark">
                   
                         <img src="{{ $website->logo ? asset('storage/'.$website->logo) : '' }}" id="logo-img-preview" style="height: 40px; {{ $website->logo ? '' : 'display:none;' }}" alt="Logo">
                   
                        <span class="fs-3 fw-bold text-uppercase serif tracking-widest" id="site-name-text" style="{{ $website->logo ? 'display:none;' : '' }}">{{ $website->site_name }}</span>
                   
                </a>
            </div>

            <div class="header-right d-flex justify-content-end align-items-center gap-2 gap-md-3" style="flex: 1;">
                
                @if(Auth::guard('customer')->check())
                    <a href="{{ route('store.account') }}" class="text-dark d-none d-md-block small fw-bold text-uppercase text-decoration-none" style="font-size: 11px;">
                        {{ strtok(Auth::guard('customer')->user()->name, ' ') }}
                    </a>
                @else
                    <a href="{{ route('store.login') }}" class="text-dark"><i class="bi bi-person fs-4"></i></a>
                @endif

                @php
                    $cartKey = 'cart_' . $website->id;
                    $cartCount = array_reduce(session()->get($cartKey, []), fn($carry, $item) => $carry + ($item['quantity'] ?? 0), 0);
                @endphp
                <a href="{{ route('store.cart') }}" class="text-dark position-relative text-decoration-none me-2">
                    <i class="bi bi-bag fs-4"></i>
                    @if($cartCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.55rem;">{{ $cartCount }}</span>
                    @endif
                </a>

                <button class="navbar-toggler border-0 shadow-none p-0 {{ $expandClass ? 'd-lg-none' : 'd-block' }}" 
                        type="button" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#classicNavContent" 
                        aria-expanded="false">
                    <i class="bi bi-list fs-2 text-dark"></i>
                </button>
            </div>
        </div>

        <nav class="navbar {{ $expandClass }} p-0">
            <div class="collapse navbar-collapse border-top" id="classicNavContent">
                <div class="container">
                    <ul class="navbar-nav w-100 justify-content-center gap-lg-5 py-3 py-lg-2">
                        
                        @foreach($navMenus as $menu)
                         @php
                                $url = $menu['url'];
                                $href = $url; // Default untuk link eksternal (https://...)

                                // KASUS 1: Anchor Link (#) - Scroll di halaman Home
                                if (str_starts_with($url, '#')) {
                                    if (!request()->routeIs('store.home')) {
                                        // Jika sedang tidak di home, arahkan ke home dulu + anchor
                                        $href = route('store.home', $website->active_domain) . $url; 
                                    }
                                } 
                                // KASUS 2: Internal Path (/) - Halaman seperti /blog, /products
                                elseif (str_starts_with($url, '/')) {
                                    // FIX: Gunakan helper 'url' manual agar path-nya bersih
                                    // Hasil: http://domain.com/s/elecjos/blog
                                    $href = url($url);
                                }
                            @endphp
                            <li class="nav-item text-center">
                                <a class="nav-link text-dark hover-dark text-uppercase small fw-bold tracking-widest py-2 py-lg-1" href="{{ url($menu['url']) }}">
                                    {{ $menu['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </nav>
    </header>

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
                                    Powered by <a href="https://shop.ashop.asia" target="_blank" class="fw-bold text-decoration-none text-primary">ASHOP WebCommerce</a>
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
                    }else {
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
</html>