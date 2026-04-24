@php
    // 1. AMBIL DATA KONTEN DARI JSON BUILDER
    $title = $data['title'] ?? 'Apa Kata Mereka?';
    $subtitle = $data['subtitle'] ?? 'Ulasan asli dari pelanggan setia kami.';
    $sectionId = $data['id'] ?? 'testimonial-' . uniqid();

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
    // 🚨 TAMBAHAN BARU: AMBIL VARIABEL TIPOGRAFI
    $textTransform = $settings['text_transform'] ?? 'none';
    $fontWeight = $settings['font_weight'] ?? 'bold'; // Default hero biasanya bold
    $fontStyle = $settings['font_style'] ?? 'normal';
    $headingSize = $settings['heading_size'] ?? 'display-3'; // Default ukuran judul
    // ----------------------------------------------------
@endphp

<section class="{{ $paddingY }} live-section" id="{{ $sectionId }}" style="background-color: {{ $bgColor }};text-transform: {{ $textTransform }}; 
                font-style: {{ $fontStyle }};">
    <div class="container py-4">
        
        {{-- HEADER SECTION --}}
        <div class="text-center mb-5 pb-3">
            <h2 class="display-6 fw-bold live-editable serif text-uppercase" 
                data-section-id="{{ $sectionId }}" 
                data-key="title"
                style="color: {{ $textColor }}; letter-spacing: 2px;font-family: var(--font-heading); text-transform: {{ $textTransform }}; font-weight: {{ $fontWeight }};">
                {{ $title }}
            </h2>
            <p class="live-editable mt-3" 
               data-section-id="{{ $sectionId }}" 
               data-key="subtitle"
               style="color: {{ $textColor }}; opacity: 0.7; letter-spacing: 0.5px;font-family: var(--font-body);">
                {{ $subtitle }}
            </p>
        </div>
        
        {{-- GRID TESTIMONIAL --}}
        <div class="row g-4 justify-content-center">
            
            {{-- Loop 3 kali sesuai jumlah ulasan kita --}}
            @for($i = 1; $i <= 3; $i++)
                @php
                    $name = $data["t{$i}_name"] ?? '';
                    $role = $data["t{$i}_role"] ?? '';
                    $review = $data["t{$i}_review"] ?? '';
                    
                    // Lewati jika kosong semua
                    if(empty($name) && empty($review)) continue;
                @endphp
                
                <div class="col-md-6 col-lg-4">
                    {{-- KARTU KLASIK: Tanpa shadow, tanpa rounded, transparan, dengan border tipis --}}
                    <div class="card h-100 rounded-0 p-4 p-md-5 text-center classic-testimonial-card"
                         style="background-color: transparent; border: 1px solid rgba(0,0,0,0.08);">
                        
                        {{-- Icon Kutipan (Dibuat Monokrom) --}}
                        <div class="mb-4" style="color: {{ $textColor }}; opacity: 0.15;">
                            <i class="bi bi-quote" style="font-size: 3.5rem; line-height: 0;"></i>
                        </div>
                        
                        {{-- Bintang (Monokrom elegan, bukan kuning) --}}
                        <div class="mb-4" style="color: {{ $textColor }}; opacity: 0.8; font-size: 0.8rem;">
                            <i class="bi bi-star-fill mx-1"></i>
                            <i class="bi bi-star-fill mx-1"></i>
                            <i class="bi bi-star-fill mx-1"></i>
                            <i class="bi bi-star-fill mx-1"></i>
                            <i class="bi bi-star-fill mx-1"></i>
                        </div>

                        {{-- Teks Ulasan --}}
                        <p class="card-text fst-italic flex-grow-1 live-editable" 
                           data-section-id="{{ $sectionId }}" 
                           data-key="t{{ $i }}_review"
                           style="line-height: 1.8; color: {{ $textColor }}; opacity: 0.85; font-size: 0.95rem;">
                            "{!! nl2br(e($review)) !!}"
                        </p>
                        
                        {{-- Profil Penulis --}}
                        <div class="mt-4 pt-4" style="border-top: 1px solid rgba(0,0,0,0.05);">
                            {{-- Nama: Serif dan Uppercase --}}
                            <h6 class="fw-bold mb-1 live-editable serif text-uppercase" 
                                data-section-id="{{ $sectionId }}" 
                                data-key="t{{ $i }}_name"
                                style="color: {{ $textColor }}; letter-spacing: 1px;">
                                {{ $name }}
                            </h6>
                            {{-- Role: Kecil, berjarak lebar --}}
                            <small class="live-editable text-uppercase" 
                                   data-section-id="{{ $sectionId }}" 
                                   data-key="t{{ $i }}_role"
                                   style="color: {{ $textColor }}; opacity: 0.5; font-size: 0.65rem; letter-spacing: 1.5px;">
                                {{ $role }}
                            </small>
                        </div>
                    </div>
                </div>
            @endfor
            
        </div>
        
    </div>
</section>