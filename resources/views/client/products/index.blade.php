@extends('layouts.client')

@section('title', 'Semua Produk')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Produk</h4>
            <p class="text-muted m-0">Kelola katalog barang dagangan Anda.</p>
        </div>
        <a href="{{ route('client.products.create', $website->id) }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Produk
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted">
                        <tr>
                            <th class="ps-4 py-3 border-0">Nama Produk</th>
                            <th class="py-3 border-0">Kategori</th>
                            <th class="py-3 border-0">Harga</th>
                            <th class="py-3 border-0">Stok</th>
                            <th class="py-3 border-0">Status</th>
                            <th class="pe-4 py-3 border-0 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" 
                                            alt="{{ $product->name }}" 
                                            class="rounded border" 
                                            style="width: 48px; height: 48px; object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center text-secondary border" style="width: 48px; height: 48px;">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    @endif

                                    <div>
                                        <div class="fw-bold text-dark">{{ $product->name }}</div>
                                        <small class="text-muted">SKU: {{ $product->sku ?? '-' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $product->category->name ?? 'Tanpa Kategori' }}</td>
                            <td class="fw-bold">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                            <td>
                                {{ $product->stock }}
                                @if($product->stock <= 5)
                                    <span class="badge bg-danger-subtle text-danger ms-1" style="font-size: 0.65rem;">Menipis</span>
                                @endif
                            </td>
                            <td>
                                @if($product->is_active)
                                    <span class="badge bg-success-subtle text-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Draft</span>
                                @endif
                            </td>
                            <td class="pe-4 text-end">
                                <div class="btn-group">
                                    <a href="{{ route('client.products.edit', [$website->id, $product->id]) }}" class="btn btn-sm btn-light border" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form action="{{ route('client.products.destroy', [$website->id, $product->id]) }}" 
                                        method="POST" 
                                        class="d-inline" 
                                        onsubmit="return confirm('Yakin ingin menghapus produk ini? Data tidak bisa dikembalikan.')">
                                        @csrf
                                        @method('DELETE') <button type="submit" class="btn btn-sm btn-light border text-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486747.png" width="80" class="opacity-25 mb-3">
                                <h6 class="text-muted">Belum ada produk</h6>
                                <p class="small text-muted mb-0">Klik tombol "Tambah Produk" di atas untuk mulai berjualan.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($products->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $products->links() }}
        </div>
        @endif
    </div>
</div>
@endsection