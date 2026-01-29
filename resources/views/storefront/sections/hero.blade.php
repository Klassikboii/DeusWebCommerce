@php
    // Set default value jika data kosong (untuk jaga-jaga)
    $title = $data['title'] ?? 'Selamat Datang';
    $subtitle = $data['subtitle'] ?? '';
    $btnText = $data['button_text'] ?? 'Belanja Sekarang';
    $btnLink = $data['button_link'] ?? '#products';
    
    // Cek apakah ada gambar background custom?
    // Asumsi: Kita nanti simpan path gambar di $data['image']
    $bgImage = $data['image'] ? asset('storage/' . $data['image']) : null;
@endphp

<section class="py-5 text-center container-fluid" 
style="{{ $bgImage ? "background: url('$bgImage') center/cover no-repeat;" : 'background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);' }} color: white; min-height: 400px; display: flex; align-items: center;">
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3">{{ $title }}</h1>
                <p class="lead mb-4 opacity-75">{{ $subtitle }}</p>
                
                @if($btnText)
                    <a href="{{ $btnLink }}" class="btn btn-light btn-lg px-4 fw-bold text-primary rounded-pill shadow-sm">
                        {{ $btnText }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>