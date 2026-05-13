{{-- resources/views/client/products/partials/product_table.blade.php --}}

<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light text-muted">
            <tr>
                <th class="ps-4 py-3 border-0" style="width: 25%;">Nama Produk</th>
                <th class="py-3 border-0" style="width: 15%;">Harga</th>
                <th class="py-3 border-0" style="width: 10%;">Stok</th>
                <th class="py-3 border-0" style="width: 25%;">Status & Prediksi</th>
                <th class="py-3 border-0" style="width: 10%;">Terakhir Terjual</th> {{-- 🚨 KOLOM BARU --}}
                <th class="pe-4 py-3 border-0 text-end " style="width: 15%;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
            <tr>
                {{-- 1. INFO PRODUK UTAMA --}}
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
                            <h6 class="mb-0 fw-bold">{{ $product->name }}</h6>
                            <div class="small text-muted mb-1">{{ $product->category->name ?? 'Tanpa Kategori' }}</div>
                            @if($product->variants->count() > 0)
                                <span class="badge bg-light text-dark border" style="font-size: 0.65rem;">Multi Varian</span>
                            @else
                                <small class="text-muted text-break">SKU: {{ $product->sku ?: '-' }}</small>
                            @endif
                        </div>
                    </div>
                </td>

                {{-- 2. HARGA --}}
                <td>
                    @if($product->variants->count() > 0)
                        @php
                            $minPrice = $product->variants->min('price');
                            $maxPrice = $product->variants->max('price');
                        @endphp
                        
                        <div class="fw-bold">
                            @if($minPrice == $maxPrice)
                                Rp {{ number_format($minPrice, 0, ',', '.') }}
                            @else
                                Rp {{ number_format($minPrice, 0, ',', '.') }} - {{ number_format($maxPrice, 0, ',', '.') }}
                            @endif
                        </div>
                        <small class="text-primary">{{ $product->variants->count() }} Varian</small>
                    @else
                        <div class="fw-bold">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                    @endif
                </td>

                {{-- 3. MURNI STOK FISIK --}}
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold fs-5">{{ $product->stock }}</span>
                        
                        {{-- Logika Selisih Accurate --}}
                        @if($product->accurate_stock !== null && $product->stock !== $product->accurate_stock)
                            <div class="alert alert-danger py-1 px-2 m-0 d-flex flex-column" style="font-size: 0.7rem; line-height: 1.2;">
                                <strong><i class="bi bi-exclamation-triangle-fill"></i> Accurate: {{ $product->accurate_stock }}</strong>
                                <div class="d-flex gap-1 mt-1">
                                    <button class="btn btn-sm btn-outline-danger px-1 py-0" style="font-size: 0.65rem;" onclick="resolveStock({{ $product->id }}, 'pull')" title="Tarik angka dari Accurate">
                                        <i class="bi bi-download"></i> Tarik
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary px-1 py-0" style="font-size: 0.65rem;" onclick="resolveStock({{ $product->id }}, 'push')" title="Paksa Accurate ikuti Web">
                                        <i class="bi bi-upload"></i> Paksa
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </td>

                {{-- 4. STATUS JUAL & ANALITIK AI --}}
                <td class="align-middle">
                    <div class="d-flex flex-column align-items-start gap-2">
                        {{-- Switch Aktif/Draft --}}
                        <div class="d-flex align-items-center gap-2">
                            <div class="form-check form-switch m-0 p-0 d-flex align-items-center">
                                {{-- 🚨 FIX: Hapus ps-5 dan paksa margin-left: 0 agar lurus sejajar --}}
                                <input class="form-check-input toggle-active-switch m-0" type="checkbox" role="switch" 
                                    id="switch_{{ $product->id }}" data-id="{{ $product->id }}" 
                                    {{ $product->is_active ? 'checked' : '' }} 
                                    style="cursor: pointer; margin-left: 0 !important; width: 2.5em; height: 1.25em;">
                            </div>
                            <small class="text-muted status-label-{{ $product->id }} fw-bold mb-0">
                                {{ $product->is_active ? 'Aktif' : 'Inaktif' }}
                            </small>
                        </div>

                        {{-- Status AI (Membaca dari Database stock_status) --}}
                        <div>
                            @if($product->stock_status == 'Empty')
                                <span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i> Habis</span>
                            @elseif($product->stock_status == 'Critical')
                                <span class="badge bg-danger animate__animated animate__pulse animate__infinite" title="Kecepatan: {{ number_format($product->velocity ?? 0, 1) }} item/hari">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Kritis (~{{ $product->runway_days }} hr)
                                </span>
                            @elseif($product->stock_status == 'Warning')
                                <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-circle me-1"></i> Siap-siap (~{{ $product->runway_days }} hr)</span>
                            @elseif($product->stock_status == 'Overstock')
                                <span class="badge bg-warning text-dark border border-warning"><i class="bi bi-box-seam me-1"></i> Overstock</span>
                            @elseif($product->stock_status == 'Dead Stock')
                                <span class="badge bg-dark"><i class="bi bi-archive me-1"></i> Dead Stock</span>
                            @else
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Aman 
                                    @if($product->runway_days > 90) (> 3 Bln) @elseif($product->runway_days) (~{{ $product->runway_days }} hr) @endif
                                </span>
                            @endif
                        </div>
                    </div>
                </td>

                {{-- 5. TERAKHIR DIBELI (Logika Detektif Kapan Terakhir Laku) --}}
                <td>
                    @php
                        // Mencari tanggal pesanan terakhir yang sukses/selesai untuk produk ini
                        $lastOrder = \App\Models\OrderItem::where('product_id', $product->id)
                            ->whereHas('order', function($q) {
                                $q->whereIn('status', ['processing', 'shipped', 'completed']);
                            })
                            ->latest()
                            ->first();
                    @endphp

                    @if($lastOrder)
                        <div class="fw-bold text-dark" style="font-size: 0.85rem;">
                            {{ $lastOrder->created_at->diffForHumans() }}
                        </div>
                        <div class="small text-muted" style="font-size: 0.75rem;">
                            {{ $lastOrder->created_at->format('d M Y') }}
                        </div>
                    @else
                        <span class="badge bg-light text-muted border fw-normal" style="font-size: 0.75rem;">Belum pernah terjual</span>
                    @endif
                </td>

                {{-- 6. AKSI --}}
                <td class="pe-4 text-end align-middle">
                    <div class="d-flex justify-content-end align-items-stretch gap-1">
                        <a href="{{ route('client.products.edit', [$website->id, $product->id]) }}" class="btn btn-sm btn-light border d-flex align-items-center" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('client.products.destroy', [$website->id, $product->id]) }}" method="POST" class="m-0" onsubmit="return confirm('Hapus produk ini secara permanen?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-light border text-danger h-100 d-flex align-items-center" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        @if($website->hasFeature('has_ai_insights'))
                        <a href="{{ route('client.products.insight', ['website' => $website->id, 'product' => $product->id]) }}" class="btn btn-sm btn-light border d-flex align-items-center" title="Lihat Analisis AI">
                            <i class="bi bi-graph-up-arrow text-info"></i>
                        </a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-5">
                    <div class="opacity-50 mb-3"><i class="bi bi-box-seam" style="font-size: 3rem;"></i></div>
                    <h6 class="text-muted fw-bold">Belum Ada Produk</h6>
                    <p class="text-muted small">Tambahkan produk pertama Anda untuk mulai berjualan.</p>
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
// Fungsi untuk menyelesaikan selisih stok (Resolusi)
async function resolveStock(productId, action) {
    let actionText = action === 'pull' ? 'MENARIK stok dari Accurate ke Web' : 'MENGIRIM stok Web ke Accurate';
    
    if (!confirm(`Anda yakin ingin ${actionText}?`)) {
        return;
    }

    // Ubah kursor jadi loading agar user tahu sedang diproses
    document.body.style.cursor = 'wait';

    try {
        const response = await fetch(`/manage/{{ $website->id }}/products/${productId}/resolve-stock`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ action: action })
        });

        const result = await response.json();

        if (response.ok && result.status === 'success') {
            alert('Stok berhasil disinkronkan!');
            window.location.reload(); // Refresh untuk melihat hilangnya alert merah
        } else {
            alert('Gagal menyinkronkan stok: ' + (result.message || 'Terjadi kesalahan di Accurate.'));
        }
    } catch (error) {
        alert('Gagal menghubungi server. Pastikan koneksi internet Anda stabil.');
        console.error(error);
    } finally {
        document.body.style.cursor = 'default';
    }
}
</script>
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
            window.location.reload();
        }
    });
});
</script>