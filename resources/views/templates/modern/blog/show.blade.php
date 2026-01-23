<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $post->title }} - {{ $website->site_name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.0/dist/trix.css">
</head>
<body>
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
</body>
</html> 