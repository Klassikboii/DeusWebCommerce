@extends('layouts.' . ($website->active_template ?? 'simple'))

{{-- INI KUNCI SEO DINAMIS PER HALAMAN --}}
@section('title', $post->title . ' - ' . $website->site_name)
@section('meta_description', Str::limit(strip_tags($post->content), 150))

@section('content')
    <div class="container py-5" style="max-width: 800px;">
        <a href="{{ route('store.home', $website->subdomain) }}" class="text-decoration-none text-muted mb-4 d-block">&larr; Kembali ke Home</a>

        <h1 class="fw-bold mb-3 display-5">{{ $post->title }}</h1>
        <p class="text-muted">{{ $post->created_at->format('d F Y') }} oleh Admin</p>

        @if($post->image)
            <img src="{{ asset('storage/' . $post->image) }}" class="w-100 rounded mb-4" style="max-height: 400px; object-fit: cover;">
        @endif

        <div class="trix-content fs-5 lh-lg">
            {!! $post->content !!}
        </div>

    </div>
@endsection