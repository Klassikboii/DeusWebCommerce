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
    
    if ($colorMode === 'global') {
        $bgColor = 'var(--primary-color)'; // <-- Sesuaikan dengan rehaul baru
        $textColor = 'var(--text-base)';
        
        // Mode Global: Paksa teks putih jika ada gambar agar kontras
        $finalTextColor = $website->hero_image ? '#ffffff' : $textColor;
    } else {
        $bgColor = $settings['bg_color'] ?? '#ffffff';
        $textColor = $settings['text_color'] ?? '#000000';
        
        // Mode Custom: Bebaskan Klien menggunakan warna pilihannya sendiri!
        // (Sistem tidak lagi ikut campur memaksa warna putih)
        $finalTextColor = $textColor;
    }

    $paddingY = $settings['padding'] ?? 'py-5';
    
  

    // AMBIL VARIABEL TIPOGRAFI
    $textTransform = $settings['text_transform'] ?? 'none';
    $fontWeight = $settings['font_weight'] ?? 'bold'; 
    $fontStyle = $settings['font_style'] ?? 'normal';
    $headingSize = $settings['heading_size'] ?? 'display-3';

    // Logika Perataan Bootstrap
    $alignmentClass = 'justify-content-center text-center';
    if ($layout === 'left') $alignmentClass = 'justify-content-start text-start';
    if ($layout === 'right') $alignmentClass = 'justify-content-end text-end';
@endphp

{{-- 3. STRUKTUR HTML BOOTSTRAP YANG FLEKSIBEL --}}
<section id="{{ $sectionId }}" 
         class="position-relative {{ $paddingY }} live-section"
         style="background-color: {{ $bgColor }}; 
         text-transform: {{ $textTransform }}; 
                font-style: {{ $fontStyle }};overflow: hidden; min-height: 60vh; display: flex; align-items: center;">
    
    {{-- Gambar Latar Belakang --}}
    @if($website->hero_image)
        <div class="position-absolute top-0 start-0 w-100 h-100" 
             style="background-image: url('{{ asset('storage/'.$website->hero_image) }}'); background-size: cover; background-position: center; z-index: 0; ">
            {{-- Overlay Hitam agar teks putih terbaca --}}
            <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark" style="opacity: 0.6;"></div> 
        </div>
    @endif

    {{-- Konten Utama --}}
    <div class="container position-relative" style="z-index: 1;">
        <div class="row {{ $alignmentClass }}">
            <div class="col-12 col-lg-8">
                
                <h1 class="{{ $headingSize }} fw-bold mb-3 live-editable serif" 
                    data-section-id="{{ $sectionId }}" 
                    data-key="title"
                    style="color: {{ $finalTextColor }}; letter-spacing: 1px;font-family: var(--font-heading); text-transform: {{ $textTransform }}; font-weight: {{ $fontWeight }};">
                    {{ $title }}
                </h1>
                
                <p class="lead mb-5 live-editable" 
                   data-section-id="{{ $sectionId }}" 
                   data-key="subtitle"
                   style="color: {{ $finalTextColor }}; font-weight: 400;font-family: var(--font-body); line-height: 1.6;">
                    {{ $subtitle }}
                </p>
                
                @if($btnText)
                {{-- 🚨 PERBAIKAN 3: Warna border, text, dan hover tombol dibuat otomatis! --}}
                <a href="{{ $btnLink }}" 
                   class="btn rounded-0 px-5 py-3 fw-bold text-uppercase live-editable shadow-sm"
                   style="border: 2px solid {{ $finalTextColor }}; 
                          color: {{ $finalTextColor }}; 
                          background-color: transparent; 
                          font-size: 0.85rem; letter-spacing: 2px; transition: all 0.3s ease;"
                   data-section-id="{{ $sectionId }}" 
                   data-key="button_text"
                   onmouseover="this.style.backgroundColor='{{ $finalTextColor }}'; this.style.color='{{ $bgColor }}';"
                   onmouseout="this.style.backgroundColor='transparent'; this.style.color='{{ $finalTextColor }}';">
                    {{ $btnText }}
                </a>
                @endif
                
            </div>
        </div>
    </div>
</section>