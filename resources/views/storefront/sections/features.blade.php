@php
    // 1. AMBIL DATA KONTEN (Sama seperti sebelumnya)
    $title = $data['title'] ?? 'Keunggulan Kami';
    
    // Fitur 1
    $f1_title = $data['f1_title'] ?? 'Produk Asli';
    $f1_desc  = $data['f1_desc'] ?? 'Jaminan produk 100% original.';
    $f1_icon  = $data['f1_icon'] ?? 'bi-patch-check';

    // Fitur 2
    $f2_title = $data['f2_title'] ?? 'Pengiriman Cepat';
    $f2_desc  = $data['f2_desc'] ?? 'Pesanan dikirim di hari yang sama.';
    $f2_icon  = $data['f2_icon'] ?? 'bi-lightning';
    
    // Fitur 3
    $f3_title = $data['f3_title'] ?? 'Garansi Resmi';
    $f3_desc  = $data['f3_desc'] ?? 'Garansi uang kembali jika rusak.';
    $f3_icon  = $data['f3_icon'] ?? 'bi-shield-check';

    $sectionId = $data['id'] ?? 'features-' . uniqid();

    // 2. AMBIL PENGATURAN GAYA / SETTINGS (Fleksibilitas Warna)
    $settings = $settings ?? []; 
    $bgColor = $settings['bg_color'] ?? '#ffffff'; // Latar belakang default
    $textColor = $settings['text_color'] ?? '#000000'; // Warna teks default
    
    // Opsional: Warna khusus ikon jika klien ingin ikonnya berbeda dari warna teks
    $iconColor = $settings['icon_color'] ?? $textColor; 
    
    $paddingY = $settings['padding'] ?? 'py-5 py-md-5';
@endphp

<section id="{{ $sectionId }}" class="{{ $paddingY }} live-section" style="background-color: {{ $bgColor }}; border-top: 1px solid rgba(0,0,0,0.05); border-bottom: 1px solid rgba(0,0,0,0.05);">
    <div class="container py-4">
        
        {{-- HEADER SECTION --}}
        <div class="text-center mb-5 pb-4">
            <h2 class="display-6 fw-bold section-title live-editable serif text-uppercase" 
                data-section-id="{{ $sectionId }}" 
                data-key="title"
                style="color: {{ $textColor }}; letter-spacing: 2px;">
                {{ $title }}
            </h2>
        </div>

        {{-- GRID FITUR --}}
        <div class="row g-5 text-center">
            
            {{-- FITUR 1 --}}
            <div class="col-md-4">
                {{-- Dibuat transparan dan tanpa border (border-0 bg-transparent) --}}
                <div class="p-4 h-100 bg-transparent border-0 transition-all classic-feature-card">
                    <div class="display-4 mb-4">
                        <i class="bi {{ $f1_icon }} live-editable" 
                           data-section-id="{{ $sectionId }}" 
                           data-key="f1_icon"
                           style="color: {{ $iconColor }};"></i>
                    </div>
                    <h5 class="fw-bold live-editable serif text-uppercase" 
                        data-section-id="{{ $sectionId }}" 
                        data-key="f1_title"
                        style="color: {{ $textColor }}; letter-spacing: 1px;">
                        {{ $f1_title }}
                    </h5>
                    <p class="small live-editable mt-3" 
                       data-section-id="{{ $sectionId }}" 
                       data-key="f1_desc"
                       style="color: {{ $textColor }}; opacity: 0.7; line-height: 1.8;">
                        {{ $f1_desc }}
                    </p>
                </div>
            </div>

            {{-- FITUR 2 --}}
            <div class="col-md-4">
                <div class="p-4 h-100 bg-transparent border-0 transition-all classic-feature-card">
                    <div class="display-4 mb-4">
                        <i class="bi {{ $f2_icon }} live-editable" 
                           data-section-id="{{ $sectionId }}" 
                           data-key="f2_icon"
                           style="color: {{ $iconColor }};"></i>
                    </div>
                    <h5 class="fw-bold live-editable serif text-uppercase" 
                        data-section-id="{{ $sectionId }}" 
                        data-key="f2_title"
                        style="color: {{ $textColor }}; letter-spacing: 1px;">
                        {{ $f2_title }}
                    </h5>
                    <p class="small live-editable mt-3" 
                       data-section-id="{{ $sectionId }}" 
                       data-key="f2_desc"
                       style="color: {{ $textColor }}; opacity: 0.7; line-height: 1.8;">
                        {{ $f2_desc }}
                    </p>
                </div>
            </div>

            {{-- FITUR 3 --}}
            <div class="col-md-4">
                <div class="p-4 h-100 bg-transparent border-0 transition-all classic-feature-card">
                    <div class="display-4 mb-4">
                        <i class="bi {{ $f3_icon }} live-editable" 
                           data-section-id="{{ $sectionId }}" 
                           data-key="f3_icon"
                           style="color: {{ $iconColor }};"></i>
                    </div>
                    <h5 class="fw-bold live-editable serif text-uppercase" 
                        data-section-id="{{ $sectionId }}" 
                        data-key="f3_title"
                        style="color: {{ $textColor }}; letter-spacing: 1px;">
                        {{ $f3_title }}
                    </h5>
                    <p class="small live-editable mt-3" 
                       data-section-id="{{ $sectionId }}" 
                       data-key="f3_desc"
                       style="color: {{ $textColor }}; opacity: 0.7; line-height: 1.8;">
                        {{ $f3_desc }}
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- CSS Tambahan Opsional untuk Animasi Lembut --}}
<style>
    .classic-feature-card { transition: transform 0.3s ease; }
    .classic-feature-card:hover { transform: translateY(-5px); }
</style>