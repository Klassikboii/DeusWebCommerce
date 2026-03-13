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
                            <div>
                        <h6 class="mb-0 fw-bold">
                            {{ $product->name }}
                            @if(!$product->is_active)
                                <span class="badge bg-secondary ms-2" style="font-size: 0.6rem;">Nonaktif</span>
                            @endif
                        </h6>
                        
                    </div>
                            @if($product->variants->count() > 0)
                                <small class="text-muted text-break">- Multi SKU -</small>
                            @else
                                <small class="text-muted text-break">{{ $product->sku ?: '-' }}</small>
                            @endif
                        </div>
                    </div>
                </td>
                <td>{{ $product->category->name ?? 'Tanpa Kategori' }}</td>
                <td>
                    @if($product->variants->count() > 0)
                        {{-- Jika punya varian, tampilkan rentang harga (Termurah - Termahal) --}}
                        @php
                            $minPrice = $product->variants->min('price');
                            $maxPrice = $product->variants->max('price');
                        @endphp
                        
                        @if($minPrice == $maxPrice)
                            Rp {{ number_format($minPrice, 0, ',', '.') }}
                        @else
                            Rp {{ number_format($minPrice, 0, ',', '.') }} - Rp {{ number_format($maxPrice, 0, ',', '.') }}
                        @endif
                        <br>
                        <small class="text-primary">{{ $product->variants->count() }} Varian</small>
                    @else
                        {{-- Jika tidak punya varian, tampilkan harga produk utama --}}
                        Rp {{ number_format($product->price, 0, ',', '.') }}
                    @endif
                </td>
               {{-- KOLOM STOK --}}
                        <td>
                            @if($product->variants->count() > 0)
                                {{-- Tampilkan total stok dari semua varian --}}
                                <span class="badge bg-info text-dark">Total: {{ $product->variants->sum('stock') }}</span>
                            @else
                                {{-- Tampilkan stok produk utama --}}
                                @if($product->stock <= 5 && $product->stock > 0)
                                    <span class="badge bg-danger">Sisa: {{ $product->stock }}</span>
                                @elseif($product->stock == 0)
                                    <span class="badge bg-secondary">Habis</span>
                                @else
                                    <span class="badge bg-success">{{ $product->stock }}</span>
                                @endif
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