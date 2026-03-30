{{-- resources/views/client/products/partials/product_table.blade.php --}}

<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light text-muted">
            <tr>
                <th class="ps-4 py-3 border-0" style="width: 30%;">Nama Produk</th>
                <th class="py-3 border-0" style="width: 10%;">Kategori</th>
                <th class="py-3 border-0" style="width: 15%;">Harga</th>
                <th class="py-3 border-0" style="width: 10%;">Stok</th>
                <th class="py-3 border-0" style="width: 15%; text-align: center;">Status</th>
                <th class="pe-4 py-3 border-0 text-end " style="width: 20%;">Aksi</th>
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
                            {{-- @if(!$product->is_active)
                                <span class="badge bg-secondary ms-2" style="font-size: 0.6rem;">  {{ $product->name }} </span>
                            @else --}}
                            {{-- @endif --}}
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
                      {{-- Ini contoh kolom stok Anda saat ini --}}
                        <td>
                            <span class="fw-bold fs-5">{{ $product->stock }}</span>
                            <br>
                            {{-- 🚨 TAMBAHKAN BADGE AI INI DI BAWAH ANGKA STOK --}}
                            @if($product->stock_status === 'Critical')
                                <span class="badge bg-danger mt-1" title="Kecepatan: {{ number_format($product->velocity, 1) }} item/hari">
                                    <i class="bi bi-exclamation-octagon-fill"></i> Sisa {{ $product->runway_days }} Hari
                                </span>
                            @elseif($product->stock_status === 'Safe')
                            <span class="badge bg-success mt-1">
                                <i class="bi bi-check-circle-fill"></i> 
                                @if($product->runway_days > 90)
                                    Aman (> 3 Bulan)
                                @else
                                    Aman ({{ $product->runway_days }} Hari)
                                @endif
                            </span>
                            @elseif($product->stock_status === 'Overstock')
                                <span class="badge bg-warning text-dark mt-1">
                                    <i class="bi bi-box-seam"></i> Overstock (Macet)
                                </span>
                            @else
                                <span class="badge bg-secondary mt-1">Kosong</span>
                            @endif
                        </td>
                <td class="align-middle text-center">
                    <div class="form-check form-switch d-flex justify-content-center">
                        <input class="form-check-input toggle-active-switch" type="checkbox" role="switch" 
                            id="switch_{{ $product->id }}" 
                            data-id="{{ $product->id }}" 
                            {{ $product->is_active ? 'checked' : '' }}
                            style="cursor: pointer; transform: scale(1.2);">
                    </div>
                    <small class="text-muted status-label-{{ $product->id }} mt-1 d-block">
                        {{ $product->is_active ? 'Aktif' : 'Draft' }}
                    </small>
                </td>
                <td class="pe-4 text-end">
                        {{-- 🚨 UBAH btn-group MENJADI d-flex gap-1 --}}
                        <div class="d-flex justify-content-end align-items-stretch gap-1">
                            
                            <a href="{{ route('client.products.edit', [$website->id, $product->id]) }}" class="btn btn-sm btn-light border d-flex align-items-center" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            
                            {{-- Hapus d-inline dari form, biarkan flex yang mengatur --}}
                            <form action="{{ route('client.products.destroy', [$website->id, $product->id]) }}" method="POST" class="m-0" onsubmit="return confirm('Hapus produk ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light border text-danger h-100 d-flex align-items-center" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            
                            <a href="{{ route('client.products.insight', ['website' => $website->id, 'product' => $product->id]) }}" class="btn btn-sm btn-light border d-flex align-items-center" title="Lihat Analisis AI">
                                <i class="bi bi-graph-up-arrow text-info"></i>
                            </a>
                            
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gunakan Event Delegation karena tabelnya sering di-reload oleh fitur Search/Pagination AJAX
    document.getElementById('productTableContainer').addEventListener('change', async function(e) {
        
        // Jika yang diklik adalah tombol Switch Status
        if (e.target.classList.contains('toggle-active-switch')) {
            const checkbox = e.target;
            const productId = checkbox.dataset.id;
            const isChecked = checkbox.checked;
            const label = document.querySelector(`.status-label-${productId}`);
            
            // Kunci sementara (Loading)
            checkbox.disabled = true;
            const originalLabel = label.innerText;
            label.innerText = 'Menyimpan...';

            try {
                const response = await fetch(`/manage/{{ $website->id }}/products/${productId}/toggle-active`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ is_active: isChecked })
                });

                const result = await response.json();

                if (response.ok && result.status === 'success') {
                    // Berhasil! Ubah teks labelnya
                    label.innerText = isChecked ? 'Aktif' : 'Draft';
                    
                    // UPDATE ANGKA BADGE DI ATAS (Misal: Produk Aktif: 8 / 10)
                    // Pastikan Anda memberi ID="activeCountBadge" pada elemen badge slot di atas
                    const badge = document.getElementById('activeCountBadge');
                    if(badge) badge.innerText = result.active_count;

                } else {
                    // DITOLAK KARENA KUOTA PENUH! Kembalikan posisi saklar
                    alert(result.message);
                    checkbox.checked = !isChecked; 
                    label.innerText = originalLabel;
                }
            } catch (error) {
                alert('Gagal menghubungi server.');
                checkbox.checked = !isChecked;
                label.innerText = originalLabel;
            } finally {
                // Buka kuncinya kembali
                checkbox.disabled = false;
            }
        }
    });
});
</script>