@extends('layouts.client')

@section('title', 'Tulis Artikel Baru')

@section('content')
<head>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.0/dist/trix.css">
    <script type="text/javascript" src="https://unpkg.com/trix@2.0.0/dist/trix.umd.min.js"></script>
    <style>
        /* Sedikit perbaikan tampilan agar editornya lebih tinggi */
        trix-editor {
            min-height: 300px; 
            background-color: white;
        }
        /* Sembunyikan tombol upload file di toolbar (karena kita belum setup logika upload gambar di body text) */
        .trix-button--icon-attach { display: none; }
    </style>
</head>

<div class="container-fluid p-0" style="max-width: 800px;">
    <div class="mb-4">
        <a href="{{ route('client.posts.index', $website->id) }}" class="text-decoration-none text-muted">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <h4 class="fw-bold mt-2">Tulis Artikel</h4>
    </div>

    <form action="{{ route('client.posts.store', $website->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Judul Artikel</label>
                    <input type="text" name="title" class="form-control" required placeholder="Contoh: 5 Tips Merawat Gadget">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Gambar Utama</label>
                    <input type="file" name="image" class="form-control">
                    <div class="form-text small">Gambar yang muncul di daftar artikel.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Isi Artikel</label>
                    
                    <input id="bodyContent" type="hidden" name="content">
                    
                    <trix-editor input="bodyContent" placeholder="Mulai menulis cerita Anda di sini..."></trix-editor>
                </div>

            </div>
        </div>
        
        <div class="text-end mb-5">
            <a href="{{ route('client.posts.index', $website->id) }}" class="btn btn-light border me-2">Batal</a>
            <button type="submit" class="btn btn-primary px-4">Terbitkan Artikel</button>
        </div>
    </form>
</div>
@endsection