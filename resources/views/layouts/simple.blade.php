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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=Playfair+Display:wght@400;700&family=Roboto:wght@400;700&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    
    @if($website->logo)
        <link rel="icon" type="image/png" href="{{ asset('storage/'.$website->logo) }}">
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
        
        body { 
            font-family: var(--font-main), 'Georgia', serif;
            background-color: #fcfcfc; 
            color: #333;
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
            $storeUrl = 'http://' . $website->subdomain . '.' . $cleanAppUrl;
            if (str_contains($appUrl, ':')) { $storeUrl = 'http://' . $website->subdomain . '.localhost:8000'; }
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
                        {{-- ITEM TAMBAHAN: CEK PESANAN --}}
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="{{ route('store.track', $website->subdomain) }}">
                            Cek Pesanan
                        </a>
                    </li>
                    {{-- CART BUTTON --}}
                    @php
                        $cartKey = 'cart_' . $website->id;
                        $cartSession = session()->get($cartKey, []);
                        $cartCount = array_reduce($cartSession, fn($carry, $item) => $carry + ($item['quantity'] ?? $item['qty'] ?? 0), 0);
                    @endphp
                    <li class="nav-item ms-lg-3 mt-3 mt-lg-0">
                        <a href="{{ route('store.cart', $website->subdomain) }}" class="btn btn-primary rounded-pill px-4 btn-sm">
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
                let baseUrl = "{{ route('store.products', $website->subdomain) }}";
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