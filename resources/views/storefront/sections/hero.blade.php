@php
    // Ambil Data dari JSON
    $title = $data['title'] ?? 'Koleksi Terbaru';
    $subtitle = $data['subtitle'] ?? 'Temukan gaya terbaik Anda.';
    $btnText = $data['button_text'] ?? 'Shop Now';
    $btnLink = $data['button_link'] ?? '#products';
    
    // ID Unik (Default ke 'hero-1' agar cocok dengan input di Editor)
    $sectionId = $data['id'] ?? 'hero-1';

    // Deteksi Template Aktif
    $isSimple = ($website->active_template == 'simple');
@endphp

@if($isSimple)
    <div class="hero-section-simple text-center" 
         style="background-color: var(--hero-bg-color); 
                {{ $website->hero_image ? 'background-image: url('.asset('storage/'.$website->hero_image).'); color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.5);' : 'color: var(--primary-color);' }}">
         
        <div class="container">
            <h2 class="display-6 fst-italic mb-3 live-editable" 
                data-section-id="{{ $sectionId }}" 
                data-key="title">
                {{ $title }}
            </h2>
            
            <p class="{{ $website->hero_image ? 'text-white' : 'text-secondary' }} mb-4 live-editable"
               data-section-id="{{ $sectionId }}" 
               data-key="subtitle">
                {{ $subtitle }}
            </p>
            
            @if($btnText)
            <a href="{{ $btnLink }}" 
               class="btn btn-custom px-5 py-2 text-uppercase live-editable" 
               style="font-size: 12px; letter-spacing: 1px;"
               data-section-id="{{ $sectionId }}" 
               data-key="button_text">
                {{ $btnText }}
            </a>
            @endif
        </div>
    </div>

@else
    <header class="bg-primary-custom text-white py-20 text-center hero-section"
            style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('{{ $website->hero_image ? asset('storage/'.$website->hero_image) : '' }}'); background-size: cover;">
        <div class="container mx-auto px-4">
            
            <h1 class="display-4 fw-bold mb-3 live-editable" 
                data-section-id="{{ $sectionId }}" 
                data-key="title">
                {{ $title }}
            </h1>
            
            <p class="lead mb-4 opacity-90 live-editable" 
               data-section-id="{{ $sectionId }}" 
               data-key="subtitle">
                {{ $subtitle }}
            </p>
            
            @if($btnText)
            <a href="{{ $btnLink }}" 
               class="btn btn-light rounded-pill px-5 fw-bold text-primary-custom live-editable"
               data-section-id="{{ $sectionId }}" 
               data-key="button_text">
                {{ $btnText }}
            </a>
            @endif
        </div>
    </header>
@endif