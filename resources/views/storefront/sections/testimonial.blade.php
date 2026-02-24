@php
    $title = $data['title'] ?? 'Apa Kata Mereka?';
    $subtitle = $data['subtitle'] ?? 'Ulasan asli dari pelanggan setia kami.';
    $sectionId = $data['id'] ?? uniqid();
@endphp

<section class="py-5 bg-light" id="{{ $sectionId }}">
    <div class="container py-4">
        
        {{-- HEADER --}}
        <div class="text-center mb-5">
            <h2 class="fw-bold live-editable" data-section-id="{{ $sectionId }}" data-key="title">{{ $title }}</h2>
            <p class="text-muted live-editable" data-section-id="{{ $sectionId }}" data-key="subtitle">{{ $subtitle }}</p>
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
                    <div class="card h-100 border-0 shadow-sm p-4 text-center rounded-4 position-relative">
                        
                        {{-- Icon Kutipan --}}
                        <div class="mb-3 text-primary" style="opacity: 0.2;">
                            <i class="bi bi-quote" style="font-size: 3rem; position: absolute; top: 10px; left: 20px;"></i>
                        </div>
                        
                        {{-- Bintang Statis --}}
                        <div class="text-warning mb-3">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>

                        {{-- Teks Ulasan --}}
                        <p class="card-text fst-italic text-muted flex-grow-1 live-editable" 
                           data-section-id="{{ $sectionId }}" 
                           data-key="t{{ $i }}_review"
                           style="line-height: 1.6;">"{!! nl2br(e($review)) !!}"</p>
                        
                        {{-- Profil Penulis --}}
                        <div class="mt-4 pt-3 border-top">
                            <h6 class="fw-bold mb-1 live-editable" data-section-id="{{ $sectionId }}" data-key="t{{ $i }}_name">{{ $name }}</h6>
                            <small class="text-muted live-editable" data-section-id="{{ $sectionId }}" data-key="t{{ $i }}_role">{{ $role }}</small>
                        </div>
                    </div>
                </div>
            @endfor
            
        </div>
        
    </div>
</section>