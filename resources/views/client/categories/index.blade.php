@extends('layouts.client')

@section('title', 'Kategori Produk')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Kategori Produk</h4>
            <p class="text-muted m-0">Kelompokkan produk Anda agar mudah dicari.</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="bi bi-plus-lg me-1"></i> Tambah Kategori
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted">
                    <tr>
                        <th class="ps-4 py-3">Nama Kategori</th>
                        <th class="py-3">Slug (URL)</th>
                        <th class="py-3">Jumlah Produk</th>
                        <th class="pe-4 py-3 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td class="ps-4 fw-bold">{{ $category->name }}</td>
                        <td class="text-muted">{{ $category->slug }}</td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                {{ $category->products->count() }} Produk
                            </span>
                        </td>
                        <td class="pe-4 text-end">
                            <form action="{{ route('client.categories.destroy', [$website->id, $category->id]) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light text-danger border">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            Belum ada kategori. Silakan buat baru.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('client.categories.store', $website->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Kategori Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" 
                                name="name" 
                                class="form-control @error('name') is-invalid @enderror" 
                                placeholder="Contoh: Elektronik, Pakaian Pria" 
                                value="{{ old('name') }}" 
                                required>

                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
                // Jika ada error validasi (misal nama kembar), buka modalnya lagi otomatis
                @if($errors->any())
                    var myModal = new bootstrap.Modal(document.getElementById('addCategoryModal'));
                    myModal.show();
                @endif
            </script>
            </form>
        </div>
    </div>
</div>
@endsection