@extends('layouts.client')

@section('title', 'Tulis Artikel Baru')

@section('content')
<head>
    
    <style>
        /* Sedikit perbaikan tampilan agar editornya lebih tinggi */
        trix-editor {
            min-height: 300px; 
            background-color: white;
        }
        /* Sembunyikan tombol upload file di toolbar (karena kita belum setup logika upload gambar di body text) */
        .trix-button--icon-attach { display: none; }

        .ck-editor__editable {
        min-height: 300px;
}
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
                    
                    <textarea name="content" id="editor">
                            {{ old('content') }}
                        </textarea>

                        <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
                        <script>
                        ClassicEditor.create(document.querySelector('#editor'), {
                            toolbar: [
                                'heading',
                                '|',
                                'bold', 'italic', 'strikethrough',
                                'link',
                                '|',
                                'blockQuote', 'code',
                                '|',
                                'bulletedList', 'numberedList',
                                '|',
                                'undo', 'redo'
                            ],
                            heading: {
                                options: [
                                    { model: 'paragraph', title: 'Paragraph' },
                                    { model: 'heading1', view: 'h1', title: 'Heading 1' },
                                    { model: 'heading2', view: 'h2', title: 'Heading 2' },
                                    { model: 'heading3', view: 'h3', title: 'Heading 3' },
                                    { model: 'heading4', view: 'h4', title: 'Heading 4' },
                                    { model: 'heading5', view: 'h5', title: 'Heading 5' },
                                ]
                            },
                            placeholder: 'Mulai menulis artikel Anda di sini...',
                        });
                        </script>
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