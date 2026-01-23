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
                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                    <span class="text-muted">{{ $template['name'] }} Preview</span>
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