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
    
    @php
        $fontName = $website->theme_config['typography']['main'] ?? 'Inter';
        $fontUrl = str_replace(' ', '+', $fontName);
    @endphp

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    
    <link id="google-font-link" href="https://fonts.googleapis.com/css2?family={{ $fontUrl }}:wght@400;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

    @if($website->favicon)
        <link rel="icon" href="{{ asset('storage/'.$website->favicon) }}">
    @endif
    
    <style>
        html { scroll-behavior: smooth; }
        
        :root {
            --primary-color: {{ $website->theme_config['colors']['primary'] ?? '#000000' }}; 
            --secondary-color: {{ $website->theme_config['colors']['secondary'] ?? '#6c757d' }};
            --hero-bg-color: {{ $website->theme_config['colors']['bg_hero'] ?? '#f9fafb' }};
            --font-main: '{{ $website->theme_config['typography']['main'] ?? 'Inter' }}', sans-serif;
            --ratio-product: {{ $website->theme_config['shapes']['product_ratio'] ?? '1/1' }};
            --radius-base: 0px !important; /* DIPAKSA KOTAK UNTUK TEMA CLASSIC */
            --shadow-base: none !important; /* MENGHILANGKAN SHADOW BERLEBIH */
            --bg-base: {{ $website->theme_config['colors']['bg_base'] ?? '#ffffff' }};
            --text-base: {{ $website->theme_config['colors']['text_base'] ?? '#212529' }};
        }

        body { 
            font-family: var(--font-main); 
            background-color: var(--bg-base);
            color: var(--text-base);
        }

        /* Tipografi Classic */
        h1, h2, h3, h4, h5, .serif { font-family: 'Playfair Display', serif !important; }
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

    {{-- HEADER CLASSIC BERBASIS BOOTSTRAP --}}
    <header class="bg-white border-bottom sticky-top py-4">
        <div class="container d-flex align-items-center justify-content-between">
            
            <nav class="d-none d-lg-flex gap-5">
                <a href="/" class="text-decoration-none text-secondary hover-dark text-uppercase small fw-bold tracking-widest">Home</a>
                <a href="{{ route('store.products' ) }}" class="text-decoration-none text-secondary hover-dark text-uppercase small fw-bold tracking-widest">Shop</a>
                <a href="{{ route('store.track') }}" class="text-decoration-none text-secondary hover-dark text-uppercase small fw-bold tracking-widest">Tracking</a>
            </nav>

            <div class="text-center">
                <a href="/" class="text-decoration-none text-dark">
                    @if($website->logo)
                        <img src="{{ Storage::url($website->logo) }}" alt="Logo" class="site-logo">
                    @else
                        <span class="fs-2 fw-bold text-uppercase serif tracking-widest">{{ $website->site_name }}</span>
                    @endif
                </a>
            </div>

            <div class="d-flex align-items-center gap-4">
                <form action="{{ route('store.products') }}" method="GET" class="d-none d-md-flex align-items-center border-bottom border-dark pb-1 position-relative">
                    <input type="text" name="search" id="desktop-search-input" placeholder="CARI..." class="border-0 bg-transparent outline-none small text-uppercase" style="width: 120px; box-shadow: none;" autocomplete="off">
                    <button type="submit" class="btn btn-link text-dark p-0 border-0"><i class="bi bi-search"></i></button>
                    <div id="desktop-search-results" class="search-results-dropdown bg-white mt-2">
                        <div id="desktop-search-loading" class="text-center py-3 d-none"><div class="spinner-border spinner-border-sm text-dark"></div></div>
                        <div id="desktop-search-content"></div>
                    </div>
                </form>
                @php
                        $cartKey = 'cart_' . $website->id;
                        $cartSession = session()->get($cartKey, []);
                        $cartCount = array_reduce($cartSession, fn($carry, $item) => $carry + ($item['quantity'] ?? $item['qty'] ?? 0), 0);
                    @endphp
                <a href="{{ route('store.cart') }}" id="cart-icon-btn" class="btn btn-link text-decoration-none text-dark fw-bold text-uppercase p-0 tracking-wider" style="font-size: 13px;">
                    Cart ({{ count(session('cart', [])) }})
                </a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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

    const isPreview = (window.self !== window.top);
    if (isPreview) {
        console.log("Mode Preview Aktif: Link dan Form dimatikan.");
        document.querySelectorAll('a, form').forEach(el => {
            if (el.tagName === 'A' && el.getAttribute('href').startsWith('#')) { return; }
            el.addEventListener('submit', e => {
                e.preventDefault();
                alert('Link dimatikan di mode editor');
            });
        });

        window.addEventListener('message', function(event) {
            const data = event.data;
            if (data.type === 'updateStyle') {
                if (data.variable === '--font-main') {
                    const fontName = data.value.replace(/ /g, '+');
                    const fontLink = document.getElementById('google-font-link');
                    if (fontLink) {
                        fontLink.href = `https://fonts.googleapis.com/css2?family=${fontName}:wght@400;600;700&display=swap`;
                    }
                    document.documentElement.style.setProperty(data.variable, `'${data.value}', sans-serif`);
                } else {
                    document.documentElement.style.setProperty(data.variable, data.value);
                }
            } else if (data.type === 'updateSection') {
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
                } else if (data.key.includes('icon')) {
                    const selector = `[data-section-id="${data.sectionId}"][data-key="${data.key}"]`;
                    const element = document.querySelector(selector);
                    if (element) { element.className = `bi ${data.value} live-editable`; }
                } else if (data.key === 'layout') {
                    const selector = `[data-section-id="${data.sectionId}"][data-key="layout"]`;
                    const rowElement = document.querySelector(selector);
                    if (rowElement) {
                        if (data.value === 'image_right') { rowElement.classList.add('flex-row-reverse'); } 
                        else { rowElement.classList.remove('flex-row-reverse'); }
                    }
                } else if (data.key === 'button_link') {
                    const selector = `[data-section-id="${data.sectionId}"][data-link-key="button_link"]`;
                    const btnElement = document.querySelector(selector);
                    if (btnElement) { btnElement.href = data.value; }
                } else {
                    const selector = `[data-section-id="${data.sectionId}"][data-key="${data.key}"]`;
                    const element = document.querySelector(selector);
                    if (element) {
                        if (element.tagName === 'IMG') { element.src = data.value; } 
                        else if (element.tagName !== 'DIV' && element.tagName !== 'SECTION') { element.innerText = data.value; }
                    }
                }
            } else if (data.type === 'updateImage') {
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
                    const noImageStyle = "background-color: var(--hero-bg-color); background-image: none; color: var(--primary-color); text-shadow: none;";
                    if (data.action === 'remove') {
                        if(heroSimple) {
                            heroSimple.style = noImageStyle;
                            heroSimple.style.color = 'var(--primary-color)'; 
                            const p = heroSimple.querySelector('p');
                            if(p) { p.classList.remove('text-white'); p.classList.add('text-secondary'); }
                        }
                    } else {
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
            } else if (data.type === 'toggleSection') {
                const sectionEl = document.getElementById(data.sectionId);
                if (sectionEl) { sectionEl.style.display = data.visible ? 'block' : 'none'; }
            } else if (data.type === 'moveSection') {
                const sectionEl = document.getElementById(data.sectionId);
                if (sectionEl) {
                    const parent = sectionEl.parentNode;
                    if (data.direction === 'up') {
                        if (sectionEl.previousElementSibling) { parent.insertBefore(sectionEl, sectionEl.previousElementSibling); }
                    } else {
                        if (sectionEl.nextElementSibling) { parent.insertBefore(sectionEl, sectionEl.nextElementSibling.nextElementSibling); }
                    }
                }
            }
        });
    }
    </script>
    <script>
    // Pendengar Pesan dari Parent (Builder)
    window.addEventListener('message', function(event) {
        const data = event.data;
        if (!data || !data.type) return;

        // --- Logika Live Preview untuk Teks (Jika sudah ada, biarkan saja) ---
        if (data.type === 'updateSection') {
            const elements = document.querySelectorAll(`[data-section-id="${data.sectionId}"][data-key="${data.key}"]`);
            elements.forEach(el => {
                if (el.tagName === 'IMG') el.src = data.value;
                else if (el.tagName === 'A' && data.key.includes('link')) el.href = data.value;
                else el.innerHTML = data.value.replace(/\n/g, '<br>');
            });
        }

        // --- TAMBAHAN BARU: Logika Live Preview untuk Warna/Settings ---
       if (data.type === 'updateSetting') {
            const section = document.getElementById(data.sectionId);
            if (!section) return;

            // --- A. LOGIKA LIVE PREVIEW WARNA ---
            if (data.key === 'color_mode' || data.key === 'bg_color' || data.key === 'text_color') {
                // Tentukan sumber warna
                const mode = (data.key === 'color_mode') ? data.value : 'custom';
                
                let targetBg, targetText;
                if (mode === 'global') {
                    targetBg = 'var(--bg-base)';
                    targetText = 'var(--text-base)';
                } else {
                    // Jika sedang geser picker, gunakan nilainya langsung. 
                    // Jika baru ganti mode ke manual, gunakan nilai dari pesan.
                    targetBg = (data.key === 'bg_color') ? data.value : (data.customBg || section.style.backgroundColor);
                    targetText = (data.key === 'text_color') ? data.value : (data.customText || '');
                }

                // Terapkan Warna Background
                section.style.backgroundColor = targetBg;

                // Terapkan Warna Teks ke elemen-elemen di dalamnya
                if (targetText) {
                    const textElements = section.querySelectorAll('h1, h2, h3, h4, h5, h6, p, span, small, a:not(.btn)');
                    textElements.forEach(el => { el.style.color = targetText; });

                    // Update border dan teks pada tombol agar tetap selaras
                    const buttons = section.querySelectorAll('.btn, .classic-accordion-button');
                    buttons.forEach(btn => {
                        btn.style.borderColor = targetText;
                        btn.style.color = targetText;
                    });
                }
            }

            // --- B. LOGIKA LIVE PREVIEW PADDING (Bootstrap Class Swapper) ---
            if (data.key === 'padding') {
                // 1. List semua kemungkinan class padding yang kita gunakan
                const allPaddingClasses = ['py-3', 'py-5', 'py-md-5', 'pt-lg-7', 'pb-lg-7'];
                
                // 2. Hapus semua class padding yang lama
                allPaddingClasses.forEach(cls => section.classList.remove(cls));
                
                // 3. Tambahkan class padding yang baru (data.value adalah string seperti "py-3")
                const newClasses = data.value.split(' ');
                newClasses.forEach(cls => {
                    if(cls) section.classList.add(cls);
                });
            }
        }
    });
</script>
    @stack('scripts')
</body>
</html>