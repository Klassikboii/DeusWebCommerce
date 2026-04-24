@php
    // 1. AMBIL DATA KONTEN
    $title = $data['title'] ?? 'Cerita Toko Kami';
    $description = $data['description'] ?? 'Tuliskan deskripsi atau cerita singkat tentang toko/produk Anda di sini.';
    $btnText = $data['button_text'] ?? '';
    
    // --- FIX LINK BUTTON ---
    $rawLink = $data['button_link'] ?? '#products';
    if ($rawLink === '/blog') {
        $btnLink = route('storefront.blog.index', ['subdomain' => $website->active_domain ?? '']);
    } else {
        $btnLink = $rawLink;
    }
    
    // Karena di struktur lama layout disimpan di $data, kita dukung keduanya
    $layout = $data['layout'] ?? ($settings['layout'] ?? 'image_left');
    $sectionId = $data['id'] ?? 'text-image-' . uniqid();

    // SVG Anti-Blokir
    $svgPlaceholder = "data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='600' height='400' viewBox='0 0 600 400'%3E%3Crect fill='%23eeeeee' width='600' height='400'/%3E%3Ctext fill='%23999999' x='50%25' y='50%25' text-anchor='middle' dy='.3em' font-family='Arial, sans-serif' font-size='24'%3EGambar Teks %26 Gambar%3C/text%3E%3C/svg%3E";
    
    $imagePath = $data['image'] ?? null;
    $imageUrl = $imagePath ? asset('storage/' . $imagePath) : $svgPlaceholder;

    // 2. AMBIL PENGATURAN GAYA / SETTINGS (Gaya Klasik via JSON)
    $settings = $settings ?? []; 
    $bgColor = $settings['bg_color'] ?? '#ffffff'; // Default putih
    $textColor = $settings['text_color'] ?? '#000000'; // Default teks hitam
    $paddingY = $settings['padding'] ?? 'py-5 py-md-5';
    // 🚨 TAMBAHAN BARU: AMBIL VARIABEL TIPOGRAFI
    $textTransform = $settings['text_transform'] ?? 'none';
    $fontWeight = $settings['font_weight'] ?? 'bold'; // Default hero biasanya bold
    $fontStyle = $settings['font_style'] ?? 'normal';
    $headingSize = $settings['heading_size'] ?? 'display-3'; // Default ukuran judul
    // ----------------------------------------------------
@endphp

<section id="{{ $sectionId }}" class="{{ $paddingY }} text-image-section live-section" style="background-color: {{ $bgColor }};text-transform: {{ $textTransform }}; 
                font-style: {{ $fontStyle }};">
    <div class="container py-4">
        
        {{-- Logika Layout: Gambar Kiri atau Kanan --}}
        <div class="row align-items-center {{ $layout === 'image_right' ? 'flex-row-reverse' : '' }} live-editable" 
             data-section-id="{{ $sectionId }}" 
             data-key="layout">
            
            {{-- KOLOM GAMBAR --}}
            <div class="col-md-6 mb-5 mb-md-0 text-center">
                {{-- Dibuat kotak tegas (rounded-0) dan border tipis pengganti shadow --}}
                <img src="{{ $imageUrl }}" alt="Section Image" 
                     class="img-fluid rounded-0 w-100 live-editable" 
                     data-section-id="{{ $sectionId }}" 
                     data-key="image"
                     style="object-fit: cover; max-height: 500px; border: 1px solid #e5e7eb;">
            </div>
            
            {{-- KOLOM TEKS --}}
            <div class="col-md-6 px-md-5">
                
                {{-- Judul dengan font Serif --}}
                <h2 class="display-6 fw-bold mb-4 live-editable serif text-uppercase" 
                    data-section-id="{{ $sectionId }}" 
                    data-key="title"
                    style="color: {{ $textColor }}; letter-spacing: 1px;font-family: var(--font-heading); text-transform: {{ $textTransform }}; font-weight: {{ $fontWeight }};">
                    {{ $title }}
                </h2>
                
                {{-- Deskripsi dibuat lebih tipis (opacity) agar elegan --}}
                <p class="mb-5 live-editable" 
                   data-section-id="{{ $sectionId }}" 
                   data-key="description" 
                   style="color: {{ $textColor }}; line-height: 1.8; opacity: 0.8; font-size: 1.05rem;">
                    {!! nl2br(e($description)) !!}
                </p>
                
                @if($btnText)
                    {{-- Tombol Kotak Klasik (Menyesuaikan warna teks secara dinamis) --}}
                    <a href="{{ $btnLink }}" 
                       class="btn rounded-0 px-5 py-3 fw-bold text-uppercase live-editable" 
                       data-section-id="{{ $sectionId }}" 
                       data-key="button_text" 
                       style="border: 2px solid {{ $textColor }}; color: {{ $textColor }}; background-color: transparent; font-size: 0.85rem; letter-spacing: 2px;"
                       onmouseover="this.style.backgroundColor='{{ $textColor }}'; this.style.color='{{ $bgColor }}';"
                       onmouseout="this.style.backgroundColor='transparent'; this.style.color='{{ $textColor }}';">
                        {{ $btnText }}
                    </a>
                @endif
                
            </div>
        </div>
    </div>
</section>