@extends('layouts.client')

@section('title', 'Edit Kategori')

@section('content')
<div class="mb-3">
    <a href="{{ route('client.categories.index', $website->id) }}" class="text-decoration-none text-muted">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar
    </a>
</div>

<div class="card border-0 shadow-sm" style="max-width: 600px;">
    <div class="card-body p-4">
        <h4 class="fw-bold mb-4">Edit Kategori</h4>

        <form action="{{ route('client.categories.update', [$website->id, $category->id]) }}" method="POST">
            @csrf
            @method('PUT') <div class="mb-4">
                <label class="form-label">Nama Kategori</label>
                <input type="text" name="name" 
                       class="form-control @error('name') is-invalid @enderror" 
                       value="{{ old('name', $category->name) }}" required>
                
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('client.categories.index', $website->id) }}" class="btn btn-light">Batal</a>
                <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection