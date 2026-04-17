@php
    // 1. AMBIL DATA KONTEN
    $title = $data['title'] ?? 'Koleksi Terbaru';
    $subtitle = $data['subtitle'] ?? 'Temukan gaya terbaik Anda.';
    $btnText = $data['button_text'] ?? 'Shop Now';
    
    // --- FIX LINK BUTTON ---
    $rawLink = $data['button_link'] ?? '#products';
    if ($rawLink === '/blog') {
        $btnLink = route('storefront.blog.index', ['subdomain' => $website->active_domain ?? '']);
    } else {
        $btnLink = $rawLink;
    }
    // -----------------------
    
    $sectionId = $data['id'] ?? 'hero-' . uniqid();
    $layout = $settings['layout'] ?? 'center'; // Opsi: 'center', 'left', 'right'
   // 2. AMBIL PENGATURAN GAYA / SETTINGS
    $settings = $settings ?? []; 
    $colorMode = $settings['color_mode'] ?? 'global';
    
    // Logika Warna: Jika Global, ambil dari CSS Variable. Jika Custom, ambil dari Hex.
    if ($colorMode === 'global') {
        $bgColor = 'var(--bg-base)';
        $textColor = 'var(--text-base)';
    } else {
        $bgColor = $settings['bg_color'] ?? '#ffffff';
        $textColor = $settings['text_color'] ?? '#000000';
    }

    // Logika Jarak (Padding)
    $paddingY = $settings['padding'] ?? 'py-5';
    
    // Logika warna teks jika ada gambar latar belakang
    $finalTextColor = $website->hero_image ? $textColor: '#ffffff' ;

    // Logika Perataan Bootstrap
    $alignmentClass = 'justify-content-center text-center';
    if ($layout === 'left') $alignmentClass = 'justify-content-start text-start';
    if ($layout === 'right') $alignmentClass = 'justify-content-end text-end';
@endphp

{{-- 3. STRUKTUR HTML BOOTSTRAP YANG FLEKSIBEL --}}
<section id="{{ $sectionId }}" 
         class="position-relative {{ $paddingY }} live-section"
         style="background-color: {{ $bgColor }}; overflow: hidden; min-height: 60vh; display: flex; align-items: center;">
    
    {{-- Gambar Latar Belakang --}}
    @if($website->hero_image)
        <div class="position-absolute top-0 start-0 w-100 h-100" 
             style="background-image: url('{{ asset('storage/'.$website->hero_image) }}'); background-size: cover; background-position: center; z-index: 0;">
            {{-- Overlay Hitam agar teks putih terbaca --}}
            <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark" style="opacity: 0.6;"></div> 
        </div>
    @endif

    {{-- Konten Utama --}}
    <div class="container position-relative" style="z-index: 1;">
        <div class="row {{ $alignmentClass }}">
            <div class="col-12 col-lg-8">
                
                {{-- Judul menggunakan gaya Serif (Playfair Display) dari classic.blade.php --}}
                <h1 class="display-3 fw-bold mb-3 live-editable serif" 
                    data-section-id="{{ $sectionId }}" 
                    data-key="title"
                    style="color: {{ $finalTextColor }}; letter-spacing: 1px;">
                    {{ $title }}
                </h1>
                
                <p class="lead mb-5 live-editable" 
                   data-section-id="{{ $sectionId }}" 
                   data-key="subtitle"
                   style="color: {{ $finalTextColor }}; font-weight: 400;">
                    {{ $subtitle }}
                </p>
                
                @if($btnText)
                {{-- Tombol dibuat kotak (rounded-0) dan transparan agar berkesan 'Classic' --}}
                <a href="{{ $btnLink }}" 
                   class="btn rounded-0 px-5 py-3 fw-bold text-uppercase live-editable shadow-sm"
                   style="border: 2px solid {{ $website->hero_image ? '#fff' : '#000' }}; 
                          color: {{ $website->hero_image ? '#fff' : '#000' }}; 
                          background-color: transparent; 
                          font-size: 0.85rem; letter-spacing: 2px;"
                   data-section-id="{{ $sectionId }}" 
                   data-key="button_text"
                   onmouseover="this.style.backgroundColor='{{ $website->hero_image ? '#fff' : '#000' }}'; this.style.color='{{ $website->hero_image ? '#000' : '#fff' }}';"
                   onmouseout="this.style.backgroundColor='transparent'; this.style.color='{{ $website->hero_image ? '#fff' : '#000' }}';">
                    {{ $btnText }}
                </a>
                @endif
                
            </div>
        </div>
    </div>
</section>