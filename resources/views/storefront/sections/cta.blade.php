@php
    // 1. AMBIL DATA KONTEN DARI JSON BUILDER
    $title = $data['title'] ?? 'Dapatkan Diskon 20% Hari Ini!';
    $subtitle = $data['subtitle'] ?? 'Gunakan kode promo: DEUS20 saat checkout.';
    $btnText = $data['button_text'] ?? 'Belanja Sekarang';
    
    // --- FIX LINK BUTTON ---
    $rawLink = $data['button_link'] ?? '#products';
    if ($rawLink === '/blog') {
        $btnLink = route('storefront.blog.index', ['subdomain' => $website->active_domain ?? '']);
    } else {
        $btnLink = $rawLink;
    }
    
    $sectionId = $data['id'] ?? 'cta-' . uniqid();

    // 2. AMBIL PENGATURAN GAYA / SETTINGS (Gaya Klasik via JSON)
    $settings = $settings ?? []; 
    $bgColor = $settings['bg_color'] ?? '#000000'; // Default hitam pekat untuk kontras CTA
    $textColor = $settings['text_color'] ?? '#ffffff'; // Default teks putih
    $paddingY = $settings['padding'] ?? 'py-5 py-md-5';
@endphp

<section id="{{ $sectionId }}" class="{{ $paddingY }} live-section" style="background-color: {{ $bgColor }};">
    <div class="container py-5 text-center">
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                {{-- Judul CTA (Serif, Uppercase, Tanpa Text-Shadow) --}}
                <h2 class="display-5 fw-bold mb-4 live-editable serif text-uppercase" 
                    data-section-id="{{ $sectionId }}" 
                    data-key="title"
                    style="color: {{ $textColor }}; letter-spacing: 2px;">
                    {{ $title }}
                </h2>
                
                {{-- Subjudul --}}
                <p class="fs-5 mb-5 live-editable" 
                   data-section-id="{{ $sectionId }}" 
                   data-key="subtitle"
                   style="color: {{ $textColor }}; opacity: 0.8; letter-spacing: 1px;">
                    {{ $subtitle }}
                </p>
                
                {{-- Tombol CTA (Kotak, Border Tegas, Invert Hover Effect) --}}
                @if($btnText)
                    <a href="{{ $btnLink }}" 
                       class="btn rounded-0 px-5 py-3 fw-bold text-uppercase live-editable"
                       data-section-id="{{ $sectionId }}" 
                       data-key="button_text"
                       data-link-key="button_link"
                       style="border: 2px solid {{ $textColor }}; color: {{ $bgColor }}; background-color: {{ $textColor }}; font-size: 0.9rem; letter-spacing: 2px; transition: all 0.3s ease;"
                       onmouseover="this.style.backgroundColor='transparent'; this.style.color='{{ $textColor }}';"
                       onmouseout="this.style.backgroundColor='{{ $textColor }}'; this.style.color='{{ $bgColor }}';">
                        {{ $btnText }}
                    </a>
                @endif
                
            </div>
        </div>
        
    </div>
</section>