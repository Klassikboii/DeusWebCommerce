<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- <title>{{ $website->site_name }}</title> --}}

    {{-- 1. LOGIKA JUDUL DINAMIS --}}
    {{-- Jika halaman (child) punya title khusus, pakai itu. Jika tidak, pakai Meta Title global. Jika tidak ada juga, pakai Nama Toko. --}}
    <title>@yield('title', $website->meta_title ? $website->meta_title : $website->site_name)</title>

    {{-- 2. LOGIKA META DESCRIPTION --}}
    <meta name="description" content="@yield('meta_description', $website->meta_description ?? 'Selamat datang di ' . $website->site_name)">
    <meta name="keywords" content="{{ $website->meta_keywords ?? 'toko online, webcommerce' }}">

    {{-- 3. OPEN GRAPH (Agar saat share link di WA/FB ada gambarnya) --}}
    <meta property="og:title" content="@yield('title', $website->meta_title ?? $website->site_name)">
    <meta property="og:description" content="@yield('meta_description', $website->meta_description)">
    <meta property="og:image" content="{{ $website->logo ? asset('storage/'.$website->logo) : asset('default-image.jpg') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=Playfair+Display:wght@400;700&family=Roboto:wght@400;700&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    {{-- Favicon (Opsional) --}}
    @if($website->logo)
        <link rel="icon" type="image/png" href="{{ asset('storage/'.$website->logo) }}">
    @endif
    <style>
        html { scroll-behavior: smooth; }
        /* === 1. VARIABEL CSS (Bisa diubah Real-time oleh JS) === */
        :root {
            --primary-color: {{ $website->primary_color ?? '#0d6efd' }}; 
            --secondary-color: {{ $website->secondary_color ?? '#6c757d' }};
            --font-main: '{{ $website->font_family ?? 'Inter' }}', sans-serif;
            --ratio-product: {{ $website->product_image_ratio ?? '1/1' }};
            --hero-bg-color: {{ $website->hero_bg_color ?? '#333333' }};
        }
        

        /* === 2. STYLE GLOBAL KHAS SIMPLE === */
        body { 
            font-family: var(--font-main), 'Georgia', serif;
            background-color: #fcfcfc; 
            color: #333;
        }

        /* Navbar: Logo Tengah */
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

        /* Helper Warna */
        .text-primary-custom { color: var(--primary-color) !important; }
        .bg-primary-custom { background-color: var(--hero-bg-color) !important; }

        /* Tombol Kotak */
        .btn-custom { 
            background-color: var(--primary-color); 
            border-color: var(--primary-color); 
            color: white;
            border-radius: 0; 
        }
        .btn-custom:hover { opacity: 0.9; color: white; }

        /* Hero Section Inherited Style */
        .hero-section-simple {
            padding: 80px 0;
            margin: 40px 0;
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
        }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-white border-bottom py-4">
        <div class="container flex-column">
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
            <a class="navbar-brand fw-bold fs-3 mb-3" href="{{ $storeUrl }}">
                
                <img src="{{ asset('storage/'.$website->logo) }}" 
                     id="logo-image"
                     style="height: 50px; {{ $website->logo ? '' : 'display:none;' }}">
                
                <span id="logo-text" style="{{ $website->logo ? 'display:none;' : '' }}">
                    {{ $website->site_name }}
                </span>
            </a>

            <ul class="nav justify-content-center small text-uppercase gap-4">
                @php
                    // FIX: Jangan di-json_decode lagi karena sudah array dari Model
                    $navMenus = $website->navigation_menu ?? [
                        ['label' => 'Home', 'url' => '#'],
                        ['label' => 'Shop', 'url' => '#shop']
                    ];
                @endphp

                @foreach($navMenus as $menu)
                <li class="nav-item">
                    @php
                        $url = $menu['url'];
                        $href = $url;
                        
                        // LOGIKA SMART LINK
                        // 1. Jika Anchor Link (#)
                        if (str_starts_with($url, '#')) {
                            // Jika bukan di halaman home, tambahkan base URL toko di depannya
                            if (!request()->routeIs('store.home')) {
                                $href = route('store.home') . $url; 
                            }
                        } 
                        // 2. Jika Internal Link (/)
                        elseif (str_starts_with($url, '/')) {
                            // Pastikan mengarah ke root toko ini, bukan root localhost admin
                            $storeUrl = rtrim(route('store.home'), '/');
                            $href = $storeUrl . $url; 
                        }
                    @endphp

                    <a class="nav-link text-dark" href="{{ $href }}">{{ $menu['label'] }}</a>
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
    <main>
        @yield('content')
    </main>

   <footer class="bg-light text-dark pt-5 pb-4 mt-5 border-top">
        <div class="container">
            <div class="row g-4">
                
                <div class="col-md-5">
                    <h5 class="fw-bold text-uppercase mb-3" style="letter-spacing: 1px;">{{ $website->site_name }}</h5>
                    @if($website->address)
                        <p class="small text-muted mb-3">
                            <i class="bi bi-geo-alt-fill me-1"></i> {{ $website->address }}
                        </p>
                    @endif
                    
                    <div class="d-flex gap-2">
                        @if($website->whatsapp_number)
                            <a href="https://wa.me/62{{ $website->whatsapp_number }}" target="_blank" class="text-dark text-decoration-none border px-3 py-1 small">
                                <i class="bi bi-whatsapp"></i> WhatsApp
                            </a>
                        @endif
                        @if($website->email_contact)
                            <a href="mailto:{{ $website->email_contact }}" class="text-dark text-decoration-none border px-3 py-1 small">
                                <i class="bi bi-envelope"></i> Hubungi
                            </a>
                        @endif
                    </div>
                </div>

                <div class="col-md-3 offset-md-1">
                    <h6 class="fw-bold text-uppercase mb-3 small">Eksplorasi</h6>
                    <ul class="list-unstyled small">
                        @foreach($website->navigation_menu ?? [] as $menu)
                            <li class="mb-2">
                                <a href="{{ $menu['url'] }}" class="text-muted text-decoration-none">{{ $menu['label'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="col-md-3 text-md-end">
                    <p class="small text-muted mb-0">
                        &copy; {{ date('Y') }} {{ $website->site_name }}<br>
                        All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </footer>

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
                        const img = document.getElementById('logo-image');
                        const txt = document.getElementById('logo-text');

                        if (data.action === 'remove') {
                            // Sembunyikan Logo, Tampilkan Teks
                            if(img) img.style.display = 'none';
                            if(txt) txt.style.display = 'inline-block';
                        } 
                        else {
                            // Tampilkan Logo, Sembunyikan Teks
                            if(img) {
                                img.src = data.src; // Update Source
                                img.style.display = 'inline-block';
                            }
                            if(txt) txt.style.display = 'none';
                        }
                    } 
                    
                    // === LOGIK HERO BANNER ===
                    else if (data.target === 'hero') {
                        const heroSimple = document.querySelector('.hero-section-simple');
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