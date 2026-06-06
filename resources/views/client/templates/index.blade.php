@extends('layouts.client')

@section('title', 'Pilih Template')

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <h4 class="fw-bold mb-1">Galeri Template</h4>
        <p class="text-muted m-0">Ubah tampilan toko Anda dalam sekejap.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    <div class="row g-4">
        @foreach($templates as $template)
        <div class="col-md-4">
            <div class="card h-100 {{ $website->active_template == $template['id'] ? 'border-primary border-2' : '' }}">
                {{-- 🚨 MENAMPILKAN GAMBAR PREVIEW --}}
                {{-- 🚨 MENAMPILKAN GAMBAR PREVIEW DENGAN RENDER KUALITAS TINGGI --}}
               {{-- 🚨 MENAMPILKAN GAMBAR PREVIEW --}}
                <div class="position-relative overflow-hidden" style="height: 180px;">
                    <img src="{{ $template['preview_image'] }}" 
                         alt="{{ $template['name'] }} Preview" 
                         class="w-100 h-100 object-fit-cover"
                         {{-- 🚨 TAMBAHAN: object-position: top; agar Navbar selalu terlihat --}}
                         style="transition: transform 0.3s ease; object-position: top;"
                         onmouseover="this.style.transform='scale(1.05)'"
                         onmouseout="this.style.transform='scale(1)'">
                         
                    {{-- Badge Tema Aktif di pojok gambar --}}
                    @if($website->active_template == $template['id'])
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge bg-success shadow-sm">
                                <i class="bi bi-star-fill me-1"></i> Dipakai
                            </span>
                        </div>
                    @endif
                </div>
                
                <div class="card-body">
                    <h5 class="fw-bold">{{ $template['name'] }}</h5>
                    <p class="text-muted small">{{ $template['description'] }}</p>
                </div>
                
                <div class="card-footer bg-white border-0 pb-3">
                    @if($website->active_template == $template['id'])
                        <button class="btn btn-success w-100" disabled>
                            <i class="bi bi-check-circle-fill"></i> Sedang Digunakan
                        </button>
                    @else
                        <form action="{{ route('client.templates.update', $website->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="template_id" value="{{ $template['id'] }}">
                            <button type="submit" class="btn btn-outline-primary w-100">
                                Pasang Template
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection