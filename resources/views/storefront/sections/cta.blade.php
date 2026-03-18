@php
    $title = $data['title'] ?? 'Dapatkan Diskon 20% Hari Ini!';
    $subtitle = $data['subtitle'] ?? 'Gunakan kode promo: DEUS20 saat checkout.';
    $btnText = $data['button_text'] ?? 'Belanja Sekarang';
    
    // Fix Link untuk Halaman Blog
    $rawLink = $data['button_link'] ?? '#products';
    if ($rawLink === '/blog') {
        $btnLink = route('store.storefront.blog.index', ['subdomain' => $website->active_domain]);
    } else {
        $btnLink = $rawLink;
    }
    
    $sectionId = $data['id'] ?? uniqid();
@endphp

{{-- Gunakan in-line style untuk menarik Primary Color secara dinamis --}}
<section class="py-5" id="{{ $sectionId }}" style="background-color: var(--primary-color);">
    <div class="container py-4 text-center">
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                {{-- Judul --}}
                <h2 class="fw-bold text-white mb-3 live-editable" 
                    data-section-id="{{ $sectionId }}" 
                    data-key="title"
                    style="text-shadow: 0 2px 4px rgba(0,0,0,0.1);">{{ $title }}</h2>
                
                {{-- Subjudul --}}
                <p class="fs-5 text-white opacity-75 mb-4 live-editable" 
                   data-section-id="{{ $sectionId }}" 
                   data-key="subtitle">{{ $subtitle }}</p>
                
                {{-- Tombol --}}
                @if($btnText)
                    <a href="{{ $btnLink }}" 
                       class="btn btn-light btn-lg px-5 py-3 rounded-pill fw-bold live-editable shadow-sm"
                       data-section-id="{{ $sectionId }}" 
                       data-key="button_text"
                       data-link-key="button_link"
                       style="color: var(--primary-color) !important;">{{ $btnText }}</a>
                @endif
            </div>
        </div>
        
    </div>
</section>