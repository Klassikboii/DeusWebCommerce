@php
    $title = $data['title'] ?? 'Produk Pilihan';
    
    // 1. Ambil Limit Saat Ini (dari JSON)
    $currentLimit = $data['limit'] ?? 8;
    
    // 2. TAPI, Kita query selalu MAX (12) agar Live Preview bisa jalan
    // (Jika kita cuma query 4, nanti pas diganti ke 8, datanya kurang)
    $products = $website->products()->with('category')->latest()->take(12)->get();
    
    $sectionId = $data['id'] ?? 'products';
    $isSimple = ($website->active_template == 'simple');
@endphp

<div id="products" class="container pb-5 pt-4">
    
    {{-- ... (Bagian Judul Biarkan Seperti Sebelumnya) ... --}}
    @if($isSimple)
        <h3 class="text-center mb-5 fst-italic live-editable" data-section-id="{{ $sectionId }}" data-key="title">{{ $title }}</h3>
    @else
        <div class="text-center mb-5">
            <h2 class="fw-bold live-editable" data-section-id="{{ $sectionId }}" data-key="title">{{ $title }}</h2>
            <div style="height: 4px; width: 60px; background-color: var(--primary-color); margin: 0 auto;"></div>
        </div>
    @endif

    <div class="row g-4 product-grid-container"> {{-- Tambahkan ID/Class Container --}}
        
        @forelse($products as $index => $item)
        
            {{-- LOGIKA SHOW/HIDE: --}}
            {{-- Jika urutan produk > limit saat ini, sembunyikan dengan style="display:none" --}}
            {{-- Tambahkan class 'product-item' agar mudah dihitung JS --}}
            
            <div class="col-6 col-md-3 product-item" 
                 style="{{ ($index >= $currentLimit) ? 'display: none !important;' : '' }}">
                
                <div class="card h-100 {{ $isSimple ? 'border-0' : 'shadow-sm border' }}">
                    {{-- ... (Isi Card Produk SAMA SEPERTI SEBELUMNYA, tidak perlu diubah) ... --}}
                    @if($item->image)
                        <img src="{{ asset('storage/' . $item->image) }}" class="card-img-top" style="aspect-ratio: var(--ratio-product, 1/1); object-fit: cover;">
                    @else
                        <div class="bg-light card-img-top d-flex align-items-center justify-content-center" style="aspect-ratio: 1/1;"><i class="bi bi-image text-muted"></i></div>
                    @endif
                    <div class="card-body text-center p-3 d-flex flex-column">
                        <h6 class="card-title mb-1 small text-uppercase fw-bold">{{ $item->name }}</h6>
                        <p class="card-text fw-bold text-primary-custom mb-3">Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                        <p class="card-text mb-3">Stock : {{ $item->stock  }}</p>
                        <div class="mt-auto">
                            <form action="{{ route('store.cart.add', ['subdomain' => $website->subdomain, 'id' => $item->id]) }}" method="POST">
                                 @csrf   
                             <button class="btn w-100 btn-sm {{ $isSimple ? 'btn-outline-dark rounded-0' : 'btn-outline-secondary-custom rounded-pill' }}">{{ $isSimple ? 'ADD TO CART' : '+ Keranjang' }}</button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        @empty
            <div class="col-12 text-center text-muted py-5">
                Belum ada produk.
            </div>
        @endforelse
    </div>
</div>