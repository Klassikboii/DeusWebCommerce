@php
    // ... (kode title sebelumnya) ...
    $title = $data['title'] ?? 'Kenapa Memilih Kami?';
    
    // Fitur 1
    $f1_title = $data['f1_title'] ?? 'Produk Asli';
    $f1_desc  = $data['f1_desc'] ?? 'Jaminan produk 100% original.';
    $f1_icon  = $data['f1_icon'] ?? 'bi-patch-check'; // <--- Default Icon

    // Fitur 2
    $f2_title = $data['f2_title'] ?? 'Pengiriman Cepat';
    $f2_desc  = $data['f2_desc'] ?? 'Pesanan dikirim di hari yang sama.';
    $f2_icon  = $data['f2_icon'] ?? 'bi-lightning';   // <--- Default Icon
    
    // Fitur 3
    $f3_title = $data['f3_title'] ?? 'Garansi Resmi';
    $f3_desc  = $data['f3_desc'] ?? 'Garansi uang kembali jika rusak.';
    $f3_icon  = $data['f3_icon'] ?? 'bi-shield-check'; // <--- Default Icon

    $sectionId = $data['id'] ?? 'features';
    $isSimple = ($website->active_template == 'simple');
@endphp

<div id="features" class="container py-5">
    
    <div class="text-center mb-5">
        <h3 class="{{ $isSimple ? 'fst-italic' : 'fw-bold' }} live-editable"
            data-section-id="{{ $sectionId }}"
            data-key="title">
            {{ $title }}
        </h3>
        @if(!$isSimple)
            <div style="height: 4px; width: 60px; background-color: var(--primary-color); margin: 0 auto;"></div>
        @endif
    </div>

    <div class="row g-4 text-center">
        <div class="col-md-4">
            <div class="p-4 {{ $isSimple ? 'border-0' : 'border rounded bg-light' }} h-100">
                <div class="fs-1 text-primary-custom mb-3">
                    <i class="bi {{ $f1_icon }} live-editable" data-section-id="{{ $sectionId }}" data-key="f1_icon"></i>
                </div>
                <h5 class="fw-bold live-editable" data-section-id="{{ $sectionId }}" data-key="f1_title">{{ $f1_title }}</h5>
                <p class="text-muted small live-editable" data-section-id="{{ $sectionId }}" data-key="f1_desc">{{ $f1_desc }}</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="p-4 {{ $isSimple ? 'border-0' : 'border rounded bg-light' }} h-100">
                <div class="fs-1 text-primary-custom mb-3">
                    <i class="bi {{ $f2_icon }} live-editable" data-section-id="{{ $sectionId }}" data-key="f2_icon"></i>
                </div>
                <h5 class="fw-bold live-editable" data-section-id="{{ $sectionId }}" data-key="f2_title">{{ $f2_title }}</h5>
                <p class="text-muted small live-editable" data-section-id="{{ $sectionId }}" data-key="f2_desc">{{ $f2_desc }}</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="p-4 {{ $isSimple ? 'border-0' : 'border rounded bg-light' }} h-100">
                <div class="fs-1 text-primary-custom mb-3">
                    <i class="bi {{ $f3_icon }} live-editable" data-section-id="{{ $sectionId }}" data-key="f3_icon"></i>
                </div>
                <h5 class="fw-bold live-editable" data-section-id="{{ $sectionId }}" data-key="f3_title">{{ $f3_title }}</h5>
                <p class="text-muted small live-editable" data-section-id="{{ $sectionId }}" data-key="f3_desc">{{ $f3_desc }}</p>
            </div>
        </div>
    </div>
</div>