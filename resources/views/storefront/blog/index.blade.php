@extends('layouts.' . ($website->active_template ?? 'simple'))

@section('content')
<style>
        :root {
            --primary-color: {{ $website->primary_color ?? '#0d6efd' }};
            --secondary-color: {{ $website->secondary_color ?? '#6c757d' }};
        }
        .text-primary-custom { color: var(--primary-color) !important; }
        .btn-primary-custom { background-color: var(--primary-color); border-color: var(--primary-color); color: white; }
        .card-img-top { height: 200px; object-fit: cover; }
    </style>
    <nav class="navbar navbar-expand-lg bg-white shadow-sm mb-5">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary-custom" href="{{ route('store.home', $website->subdomain) }}">
                <i class="bi bi-arrow-left me-2"></i> Kembali ke Toko
            </a>
        </div>
    </nav>

    <div class="container pb-5" style="max-width: 900px;">
        <div class="text-center mb-5">
            <h1 class="fw-bold">Blog & Artikel Terbaru</h1>
            <p class="text-muted">Dapatkan tips dan informasi menarik dari kami.</p>
        </div>

        <div class="row g-4">
            @forelse($posts as $post)
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm hover-shadow transition">
                    @if($post->image)
                        <img src="{{ asset('storage/' . $post->image) }}" class="card-img-top" alt="{{ $post->title }}">
                    @else
                        <div class="card-img-top bg-secondary-subtle d-flex align-items-center justify-content-center text-muted">
                            <i class="bi bi-image h1"></i>
                        </div>
                    @endif

                    <div class="card-body d-flex flex-column">
                        <small class="text-muted mb-2">
                            <i class="bi bi-calendar3 me-1"></i> {{ $post->created_at->format('d M Y') }}
                        </small>
                        
                        <h5 class="card-title fw-bold mb-3">
                            <a href="{{ route('storefronts.blog.show', [$website->subdomain, $post->slug]) }}" class="text-decoration-none text-dark stretched-link">
                                {{ $post->title }}
                            </a>
                        </h5>
                        
                        <p class="card-text text-muted small flex-grow-1">
                            {{ Str::limit(strip_tags($post->content), 100) }}
                        </p>
                        
                        <div class="mt-3 text-primary-custom fw-bold small">
                            Baca Selengkapnya &rarr;
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486747.png" width="80" class="opacity-25 mb-3">
                <h5 class="text-muted">Belum ada artikel.</h5>
                <p class="text-muted small">Cek kembali nanti ya!</p>
            </div>
            @endforelse
        </div>
    </div>

    <footer class="text-center py-4 text-muted small">
        &copy; {{ date('Y') }} {{ $website->site_name }}.
    </footer>
@endsection