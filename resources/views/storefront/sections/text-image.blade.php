@php
    // Ambil data dari JSON
    $title = $data['title'] ?? 'Cerita Toko Kami';
    $description = $data['description'] ?? 'Tuliskan deskripsi atau cerita singkat tentang toko/produk Anda di sini.';
    $btnText = $data['button_text'] ?? '';
    // --- FIX LINK BUTTON ---
    $rawLink = $data['button_link'] ?? '#products';
    if ($rawLink === '/blog') {
        // Jika minta ke blog, kita buatkan rute dinamis lengkap dengan subdomain
        $btnLink = route('storefront.blog.index', ['subdomain' => $website->subdomain]);
    } else {
        // Jika #anchor atau URL luar, biarkan apa adanya
        $btnLink = $rawLink;
    }
    // -----------------------
    
    // Layout: 'image_left' (default) atau 'image_right'
    $layout = $data['layout'] ?? 'image_left';
    
    $sectionId = $data['id'] ?? uniqid();

    // --- LOGIKA GAMBAR BARU ---
    // Cek apakah ada data image di JSON, jika ada tampilkan, jika tidak pakai placeholder
    $imagePath = $data['image'] ?? null;
    $imageUrl = $imagePath ? asset('storage/' . $imagePath) : 'https://via.placeholder.com/600x400/eeeeee/999999?text=Gambar+Teks+dan+Gambar';
@endphp

<section class="py-5 bg-white text-image-section">
    <div class="container py-4">
        {{-- Gunakan flex-row-reverse jika layoutnya 'image_right' agar gambarnya pindah ke kanan --}}
        <div class="row align-items-center {{ $layout === 'image_right' ? 'flex-row-reverse' : '' }}">
            
            <div class="col-md-6 mb-4 mb-md-0 text-center">
                <img src="{{ $imageUrl }}" alt="Section Image" class="img-fluid rounded shadow-sm w-100" style="object-fit: cover; max-height: 450px;">
            </div>
            
            <div class="col-md-6 px-md-5">
                <h2 class="fw-bold mb-3 live-editable" 
                    data-section-id="{{ $sectionId }}" 
                    data-key="title">{{ $title }}</h2>
                
                {{-- Gunakan nl2br agar format Enter (baris baru) dari textarea tetap terbaca --}}
                <p class="text-muted mb-4 live-editable" 
                   data-section-id="{{ $sectionId }}" 
                   data-key="description"
                   style="line-height: 1.8;">{!! nl2br(e($description)) !!}</p>
                
                @if($btnText)
                    <a href="{{ $btnLink }}" 
                       class="btn btn-primary px-4 py-2 rounded-pill live-editable"
                       data-section-id="{{ $sectionId }}" 
                       data-key="button_text">{{ $btnText }}</a>
                @endif
            </div>
            
        </div>
    </div>
</section>