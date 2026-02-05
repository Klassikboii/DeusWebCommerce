{{-- Pilih Layout berdasarkan active_template di database --}}
@extends('layouts.' . ($website->active_template ?? 'modern'))

@section('content')

    {{-- LOOPING JSON DARI DATABASE --}}
    @if($website->sections && is_array($website->sections) && count($website->sections) > 0)
        
        @foreach($website->sections as $section)
    
    {{-- 1. Ambil Data Dasar --}}
    @php 
        $sectionData = $section['data'] ?? []; 
        $sectionType = $section['type'] ?? '';
        
        // 2. LOGIKA BARU: Cek Visibilitas
        // Jika ada key 'visible' bernilai false, atau key 'hidden' bernilai true -> SKIP
        $isVisible = $section['visible'] ?? true; 
        
        // Fitur tambahan: inject ID agar live preview jalan
        $sectionData['id'] = $section['id'] ?? uniqid();
    @endphp

    {{-- 3. Penjaga Pintu: Kalau tidak visible, lewati (continue) --}}
    @if(!$isVisible)
        @continue
    @endif

    <div id="{{ $section['id'] ?? '' }}">
        
        @if($sectionType === 'hero')
            @include('storefront.sections.hero', ['data' => $sectionData])
        
        @elseif($sectionType === 'products')
            @include('storefront.sections.products', ['data' => $sectionData])
        
        @elseif($sectionType === 'features')
            @include('storefront.sections.features', ['data' => $sectionData])
        
        @endif

    </div>

@endforeach
        

    @else
        {{-- Pesan jika Data JSON Kosong --}}
        <div class="py-5 text-center">
            <h3>Belum ada konten.</h3>
            <p>Silakan atur tampilan di menu Editor Website.</p>
        </div>
    @endif

@endsection