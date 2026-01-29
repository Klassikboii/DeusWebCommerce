@extends('layouts.storefront') 

@section('content')

    {{-- DEBUG SEMENTARA: Cek apakah JSON terbaca? --}}
    {{-- @php dd($website->sections); @endphp --}}

    @if($website->sections && count($website->sections) > 0)
        
        {{-- LOOPING BALOK LEGO (SECTION) --}}
        @foreach($website->sections as $section)
            
            <div id="{{ $section['id'] ?? '' }}">
                
                {{-- Cek Tipe Section --}}
                @if($section['type'] === 'hero')
                    @include('storefront.sections.hero', ['data' => $section['data']])
                
                @elseif($section['type'] === 'products')
                    @include('storefront.sections.products', ['data' => $section['data']])
                
                @endif

            </div>

        @endforeach

    @else
        {{-- Tampilan Darurat jika JSON Kosong --}}
        <div class="py-5 text-center">
            <h2>Data Tampilan Kosong</h2>
            <p>Silakan reset data section di database.</p>
        </div>
    @endif

@endsection