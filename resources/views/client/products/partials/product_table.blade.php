{{-- resources/views/client/products/partials/product_table.blade.php --}}

<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light text-muted">
            <tr>
                <th class="ps-4 py-3 border-0">Nama Produk</th>
                <th class="py-3 border-0">Kategori</th>
                <th class="py-3 border-0">Harga</th>
                <th class="py-3 border-0">Stok</th>
                {{-- <th class="py-3 border-0">Status</th> --}}
                <th class="pe-4 py-3 border-0 text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
            <tr>
                <td class="ps-4 py-3">
                    <div class="d-flex align-items-center gap-3">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" class="rounded border" style="width: 48px; height: 48px; object-fit: cover;">
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
                {{-- <td>
                    @if($product->status == 'active') 
                        <span class="badge bg-success-subtle text-success">Aktif</span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary">Draft</span>
                    @endif
                </td> --}}
                <td class="pe-4 text-end">
                    <div class="btn-group">
                        <a href="{{ route('client.products.edit', [$website->id, $product->id]) }}" class="btn btn-sm btn-light border" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('client.products.destroy', [$website->id, $product->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus produk ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-light border text-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-5">
                    <div class="opacity-50 mb-2"><i class="bi bi-box-seam fs-1"></i></div>
                    <h6 class="text-muted">Tidak ada produk ditemukan</h6>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($products->hasPages())
    <div class="card-footer bg-white border-0 py-3 ajax-pagination">
        {{ $products->links() }}
    </div>
@endif