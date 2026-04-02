@php
    $title = $data['title'] ?? 'Cerita Toko Kami';
    $description = $data['description'] ?? 'Tuliskan deskripsi atau cerita singkat tentang toko/produk Anda di sini.';
    $btnText = $data['button_text'] ?? '';
    
    $rawLink = $data['button_link'] ?? '#products';
    if ($rawLink === '/blog') {
        $btnLink = route('storefront.blog.index');
    } else {
        $btnLink = $rawLink;
    }
    
    $layout = $data['layout'] ?? 'image_left';
    $sectionId = $data['id'] ?? uniqid();

    // SVG Anti-Blokir
    $svgPlaceholder = "data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='600' height='400' viewBox='0 0 600 400'%3E%3Crect fill='%23eeeeee' width='600' height='400'/%3E%3Ctext fill='%23999999' x='50%25' y='50%25' text-anchor='middle' dy='.3em' font-family='Arial, sans-serif' font-size='24'%3EGambar Teks %26 Gambar%3C/text%3E%3C/svg%3E";
    
    $imagePath = $data['image'] ?? null;
    $imageUrl = $imagePath ? asset('storage/' . $imagePath) : $svgPlaceholder;
@endphp

<section class="py-5 bg-white text-image-section">
    <div class="container py-4">
        <div class="row align-items-center {{ $layout === 'image_right' ? 'flex-row-reverse' : '' }} live-editable" 
             data-section-id="{{ $sectionId }}" 
             data-key="layout">
            
            <div class="col-md-6 mb-4 mb-md-0 text-center">
                <img src="{{ $imageUrl }}" alt="Section Image" 
                     class="img-fluid rounded shadow-sm w-100 live-editable" 
                     data-section-id="{{ $sectionId }}" 
                     data-key="image"
                     style="object-fit: cover; max-height: 450px;">
            </div>
            
            <div class="col-md-6 px-md-5">
                <h2 class="fw-bold mb-3 live-editable" data-section-id="{{ $sectionId }}" data-key="title">{{ $title }}</h2>
                <p class="text-muted mb-4 live-editable" data-section-id="{{ $sectionId }}" data-key="description" style="line-height: 1.8;">{!! nl2br(e($description)) !!}</p>
                @if($btnText)
                    <a href="{{ $btnLink }}" class="btn  px-4 py-2 rounded-pill live-editable" data-section-id="{{ $sectionId }}" data-key="button_text"  style="background-color: var(--primary-color); color: white;">{{ $btnText }}</a>
                @endif
            </div>
        </div>
    </div>
</section>
</section>