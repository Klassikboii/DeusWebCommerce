@extends('layouts.client')

@section('title', 'Edit Artikel')

@section('content')
<head>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.0/dist/trix.css">
    <script type="text/javascript" src="https://unpkg.com/trix@2.0.0/dist/trix.umd.min.js"></script>
    <style>
        trix-editor { min-height: 300px; background-color: white; }
        .trix-button--icon-attach { display: none; }
    </style>
</head>

<div class="container-fluid p-0" style="max-width: 800px;">
    <div class="mb-4">
        <a href="{{ route('client.posts.index', $website->id) }}" class="text-decoration-none text-muted">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <h4 class="fw-bold mt-2">Edit Artikel</h4>
    </div>

    <form action="{{ route('client.posts.update', [$website->id, $post->id]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT') <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Judul Artikel</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $post->title) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Ganti Gambar (Opsional)</label>
                    @if($post->image)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $post->image) }}" height="100" class="rounded">
                        </div>
                    @endif
                    <input type="file" name="image" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Isi Artikel</label>
                    
                    <input id="bodyContent" type="hidden" name="content" value="{{ $post->content }}">
                    
                    <trix-editor input="bodyContent"></trix-editor>
                </div>

            </div>
        </div>
        
        <div class="text-end mb-5">
            <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection