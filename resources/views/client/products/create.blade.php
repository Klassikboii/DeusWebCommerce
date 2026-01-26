@extends('layouts.client')

@section('title', 'Tambah Produk Baru')

@section('content')
<div class="container-fluid p-0" style="max-width: 800px;">
    
    <div class="mb-4">
        <a href="{{ route('client.products.index', $website->id) }}" class="text-decoration-none text-muted mb-2 d-inline-block">
            <i class="bi bi-arrow-left"></i> Kembali ke Produk
        </a>
        <h4 class="fw-bold">Tambah Produk Baru</h4>
    </div>

    <form action="{{ route('client.products.store', $website->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3 text-primary">Informasi Dasar</h6>
                <div class="mb-3">
                    <label class="form-label">Foto Produk</label>
                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                    <div class="form-text small text-muted">Format: JPG, PNG, JPEG. Maksimal 2MB.</div>
                    @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Contoh: Kemeja Flannel Kotak" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="category_id" class="form-select">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text"><a href="#" class="text-decoration-none text-primary small">+ Buat Kategori Baru</a></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SKU (Kode Barang)</label>
                        <input type="text" name="sku" class="form-control" value="{{ old('sku') }}" placeholder="Opsional">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi Produk</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3 text-primary">Harga & Inventaris</h6>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}" required>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Stok Awal</label>
                        <input type="number" name="stock" class="form-control" value="{{ old('stock', 0) }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Berat (Gram)</label>
                        <input type="number" name="weight" class="form-control" value="{{ old('weight', 100) }}">
                        <div class="form-text small">Untuk hitung ongkir.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="{{ route('client.products.index', $website->id) }}" class="btn btn-light border">Batal</a>
            <button type="submit" class="btn btn-primary px-4">Simpan Produk</button>
        </div>
    </form>
</div>
@endsection