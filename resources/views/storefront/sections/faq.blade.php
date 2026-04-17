@php
    // 1. AMBIL DATA KONTEN DARI JSON BUILDER
    $title = $data['title'] ?? 'Pertanyaan Umum';
    $subtitle = $data['subtitle'] ?? 'Temukan jawaban untuk pertanyaan yang sering diajukan.';
    $sectionId = $data['id'] ?? 'faq-' . uniqid();

    // 2. AMBIL PENGATURAN GAYA / SETTINGS (Gaya Klasik via JSON)
    $settings = $settings ?? []; 
    $bgColor = $settings['bg_color'] ?? '#ffffff'; 
    $textColor = $settings['text_color'] ?? '#000000'; 
    $paddingY = $settings['padding'] ?? 'py-5 py-md-5';
@endphp

<section class="{{ $paddingY }} faq-section live-section" id="{{ $sectionId }}" style="background-color: {{ $bgColor }};">
    <div class="container py-4">
        
        {{-- HEADER SECTION --}}
        <div class="text-center mb-5 pb-3">
            <h2 class="display-6 fw-bold live-editable serif text-uppercase" 
                data-section-id="{{ $sectionId }}" 
                data-key="title"
                style="color: {{ $textColor }}; letter-spacing: 2px;">
                {{ $title }}
            </h2>
            
            <p class="live-editable mt-3" 
               data-section-id="{{ $sectionId }}" 
               data-key="subtitle"
               style="color: {{ $textColor }}; opacity: 0.7; letter-spacing: 0.5px;">
                {{ $subtitle }}
            </p>
        </div>
        
        {{-- ACCORDION CONTENT --}}
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion accordion-flush" id="accordion-{{ $sectionId }}">
                    
                    {{-- Loop 3 kali sesuai jumlah maksimal FAQ kita --}}
                    @for($i = 1; $i <= 3; $i++)
                        @php
                            $ask = $data["q{$i}_ask"] ?? '';
                            $ans = $data["q{$i}_ans"] ?? '';
                            
                            // Jika pertanyaannya kosong, lewati (jangan di-render)
                            if(empty($ask) && empty($ans)) continue;
                        @endphp
                        
                        {{-- KOTAK ACCORDION KLASIK: Tanpa shadow, tanpa rounded --}}
                        <div class="accordion-item classic-accordion-item mb-3">
                            <h2 class="accordion-header" id="heading-{{ $sectionId }}-{{ $i }}">
                                <button class="accordion-button classic-accordion-button {{ $i !== 1 ? 'collapsed' : '' }} fw-bold" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#collapse-{{ $sectionId }}-{{ $i }}" 
                                        aria-expanded="{{ $i === 1 ? 'true' : 'false' }}">
                                    
                                    {{-- Sensor Live Preview Teks Pertanyaan --}}
                                    <span class="live-editable" 
                                          data-section-id="{{ $sectionId }}" 
                                          data-key="q{{ $i }}_ask"
                                          style="letter-spacing: 0.5px;">
                                        {{ $ask }}
                                    </span>
                                    
                                </button>
                            </h2>
                            <div id="collapse-{{ $sectionId }}-{{ $i }}" 
                                 class="accordion-collapse collapse {{ $i === 1 ? 'show' : '' }}" 
                                 data-bs-parent="#accordion-{{ $sectionId }}">
                                <div class="accordion-body pt-3 pb-4">
                                    
                                    {{-- Sensor Live Preview Teks Jawaban --}}
                                    <span class="live-editable d-block" 
                                          data-section-id="{{ $sectionId }}" 
                                          data-key="q{{ $i }}_ans" 
                                          style="line-height: 1.8; color: {{ $textColor }}; opacity: 0.8; font-size: 0.95rem;">
                                        {!! nl2br(e($ans)) !!}
                                    </span>
                                    
                                </div>
                            </div>
                        </div>
                    @endfor
                    
                </div>
            </div>
        </div>
        
    </div>
</section>

{{-- CSS Tambahan Khusus untuk Tampilan Klasik --}}
<style>
    /* Reset gaya bawaan Bootstrap pada Accordion khusus di section ini */
    #{{ $sectionId }} .classic-accordion-item {
        border: 1px solid rgba(0,0,0,0.1) !important;
        border-radius: 0px !important;
        background-color: transparent !important;
    }
    
    #{{ $sectionId }} .classic-accordion-button {
        border-radius: 0px !important;
        background-color: transparent !important;
        color: {{ $textColor }} !important;
        box-shadow: none !important;
        padding: 1.25rem 1.5rem;
    }

    /* Saat accordion terbuka, berikan highlight sangat tipis */
    #{{ $sectionId }} .classic-accordion-button:not(.collapsed) {
        background-color: rgba(0,0,0,0.02) !important;
        color: {{ $textColor }} !important;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    /* Menghilangkan border focus yang mengganggu dari Bootstrap */
    #{{ $sectionId }} .classic-accordion-button:focus {
        border-color: rgba(0,0,0,0.1) !important;
        box-shadow: none !important;
    }
</style>