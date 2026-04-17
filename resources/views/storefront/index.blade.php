{{-- Pilih Layout berdasarkan active_template di database --}}
@extends('layouts.' . ($website->active_template ?? 'modern'))

@section('content')

    {{-- LOOPING JSON DARI DATABASE --}}
    @if(!empty($website->sections) && is_array($website->sections) && count($website->sections) > 0)
        
        @foreach($website->sections as $section)
            @php 
                // 1. Ambil Konten, Tipe, Visibilitas, dan Settings
                $sectionData = $section['data'] ?? []; 
                $sectionType = $section['type'] ?? '';
                $isVisible = $section['visible'] ?? true; 
                $sectionSettings = $section['settings'] ?? [];
                
                // Inject ID unik agar Live Preview Iframe tetap berfungsi
                $sectionData['id'] = $section['id'] ?? 'sec-' . uniqid();
            @endphp

            {{-- 2. Penjaga Pintu: Render hanya jika status visible adalah TRUE --}}
            @if($isVisible)
                <div class="template-section">
                    
                    {{-- 3. Pemanggilan Dinamis: Mengirim 'data' DAN 'settings' --}}
                    @if(view()->exists('storefront.sections.' . $sectionType))
                        @include('storefront.sections.' . $sectionType, [
                            'data' => $sectionData,
                            'settings' => $sectionSettings
                        ])
                    @else
                        {{-- Log jika file section tidak ditemukan untuk debugging --}}
                        @endif
                    
                </div>
            @endif
        @endforeach

    @else
        {{-- Tampilan Standar jika Data JSON Kosong --}}
        <div class="container py-5 text-center">
            <h3 class="text-muted">Belum ada konten yang disusun.</h3>
            <p>Silakan buka Editor Website untuk mulai menambahkan bagian halaman.</p>
        </div>
    @endif

@endsection