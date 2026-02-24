@php
    $title = $data['title'] ?? 'Pertanyaan Umum';
    $subtitle = $data['subtitle'] ?? 'Temukan jawaban untuk pertanyaan yang sering diajukan.';
    $sectionId = $data['id'] ?? uniqid();
@endphp

<section class="py-5 bg-white faq-section" id="{{ $sectionId }}">
    <div class="container py-4">
        
        {{-- HEADER --}}
        <div class="text-center mb-5">
            <h2 class="fw-bold live-editable" data-section-id="{{ $sectionId }}" data-key="title">{{ $title }}</h2>
            <p class="text-muted live-editable" data-section-id="{{ $sectionId }}" data-key="subtitle">{{ $subtitle }}</p>
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
                        
                        <div class="accordion-item border-0 mb-3 shadow-sm rounded">
                            <h2 class="accordion-header" id="heading-{{ $sectionId }}-{{ $i }}">
                                <button class="accordion-button {{ $i !== 1 ? 'collapsed' : '' }} fw-bold bg-light rounded" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#collapse-{{ $sectionId }}-{{ $i }}" 
                                        aria-expanded="{{ $i === 1 ? 'true' : 'false' }}">
                                    
                                    {{-- Sensor Live Preview Teks Pertanyaan --}}
                                    <span class="live-editable" data-section-id="{{ $sectionId }}" data-key="q{{ $i }}_ask">{{ $ask }}</span>
                                    
                                </button>
                            </h2>
                            <div id="collapse-{{ $sectionId }}-{{ $i }}" 
                                 class="accordion-collapse collapse {{ $i === 1 ? 'show' : '' }}" 
                                 data-bs-parent="#accordion-{{ $sectionId }}">
                                <div class="accordion-body text-muted pt-4 pb-4">
                                    
                                    {{-- Sensor Live Preview Teks Jawaban --}}
                                    <span class="live-editable d-block" data-section-id="{{ $sectionId }}" data-key="q{{ $i }}_ans" style="line-height: 1.8;">{!! nl2br(e($ans)) !!}</span>
                                    
                                </div>
                            </div>
                        </div>
                    @endfor
                    
                </div>
            </div>
        </div>
        
    </div>
</section>

{{-- Tambahan CSS agar Accordion terlihat lebih modern (mirip Elementor) --}}
<style>
    .faq-section .accordion-button:not(.collapsed) {
        color: var(--primary-color);
        background-color: #fff !important;
        box-shadow: inset 0 -1px 0 rgba(0,0,0,.125);
    }
    .faq-section .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(0,0,0,.125);
    }
    .faq-section .accordion-item {
        border: 1px solid rgba(0,0,0,.05) !important;
    }
</style>