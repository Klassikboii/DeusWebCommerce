@extends('layouts.client')

@section('title', 'Edit Produk')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div class="container-fluid p-0" style="max-width: 800px;">
    
    <div class="mb-4">
        <a href="{{ route('client.products.index', $website->id) }}" class="text-decoration-none text-muted mb-2 d-inline-block">
            <i class="bi bi-arrow-left"></i> Kembali ke Produk
        </a>
        <h4 class="fw-bold">Edit Produk: {{ $product->name }}</h4>
    </div>
    
    @if ($errors->any())
        <div class="alert alert-danger shadow-sm border-danger">
            <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Gagal Menyimpan!</h6>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <form action="{{ route('client.products.update', ['website' => $website->id, 'product' => $product->id]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        {{-- 1. INFORMASI DASAR --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3 text-primary">Informasi Dasar</h6>
                
                <div class="mb-3">
                    <label class="form-label">Foto Produk</label>
                    <div class="mb-2" id="preview-container" style="{{ $product->image ? '' : 'display: none;' }}">
                        <img id="image-preview" src="{{ $product->image ? asset('storage/' . $product->image) : '#' }}" alt="Preview" class="img-thumbnail rounded" style="height: 100px; width: 100px; object-fit: cover;">
                    </div>
                    <input type="file" name="image" id="image-input" class="form-control" accept="image/*" onchange="previewNewImage(event)">
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                </div>
                
                <div class="mb-3 p-3 bg-light rounded border">
                    <div class="form-check form-switch d-flex align-items-center m-0 p-0">
                        <input class="form-check-input ms-0 me-3 mt-0" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }} style="width: 2.5em; height: 1.25em;">
                        <label class="form-check-label fw-bold mb-0" for="is_active" style="cursor: pointer;">Produk Aktif (Ditampilkan di Toko)</label>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="category_id" class="form-select">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Berat (Gram) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="weight" class="form-control" value="{{ old('weight', $product->weight) }}" required min="1">
                            <span class="input-group-text">gram</span>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi Produk</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description', $product->description) }}</textarea>
                </div>
            </div>
        </div>

        {{-- 2. HARGA & VARIAN --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3 text-primary">Harga & Inventaris</h6>
                
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="mb-0 p-3 bg-light border rounded">
                            <label class="form-label fw-bold mb-1 text-muted">Struktur Produk (Permanen)</label>
                            <div>
                                @if($product->hasVariants())
                                    <span class="badge bg-primary fs-6"><i class="bi bi-tags me-1"></i> Produk Bervarian</span>
                                    <input type="hidden" id="has_variants" name="has_variants" value="1">
                                @else
                                    <span class="badge bg-secondary fs-6"><i class="bi bi-box me-1"></i> Produk Tunggal (Single)</span>
                                    <input type="hidden" id="has_variants" name="has_variants" value="0">
                                @endif
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="bi bi-info-circle"></i> Struktur produk ditetapkan saat pembuatan dan tidak dapat diubah agar data sinkronisasi dengan Accurate tetap aman.
                            </small>
                        </div>
                    </div>
                </div>

                {{-- FIELD SINGLE PRODUCT --}}
                <div id="single-product-fields">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Harga (Rp)</label>
                            <input type="number" name="price" class="form-control" value="{{ old('price', $product->price) }}">
                            
                            @if(!empty($product->price_history) && is_array($product->price_history))
                                @if(count($product->price_history) > 4)
                                    @php
                                        $chartHistory = array_reverse($product->price_history);
                                        $labels = [];
                                        $data = [];
                                        foreach($chartHistory as $h) {
                                            $labels[] = \Carbon\Carbon::parse($h['changed_at'])->format('d M');
                                            $data[] = $h['price'];
                                        }
                                        $labels[] = 'Sekarang';
                                        $data[] = $product->price;
                                    @endphp
                                    <div class="mt-3 p-2 bg-light border rounded">
                                        <span class="fw-bold d-block mb-1 text-muted" style="font-size: 0.7rem;"><i class="bi bi-graph-up text-primary"></i> Tren Harga Lama:</span>
                                        <div style="height: 50px; width: 100%;">
                                            <canvas id="singlePriceChart"></canvas>
                                        </div>
                                    </div>
                                    <script>
                                        document.addEventListener("DOMContentLoaded", function() {
                                            const ctx = document.getElementById('singlePriceChart').getContext('2d');
                                            new Chart(ctx, {
                                                type: 'line',
                                                data: {
                                                    labels: @json($labels),
                                                    datasets: [{
                                                        data: @json($data),
                                                        borderColor: '#0d6efd',
                                                        borderWidth: 2,
                                                        pointRadius: 3,
                                                        pointBackgroundColor: '#0d6efd',
                                                        fill: false,
                                                        tension: 0.3
                                                    }]
                                                },
                                                options: {
                                                    responsive: true,
                                                    maintainAspectRatio: false,
                                                    plugins: { legend: { display: false }, tooltip: { enabled: true } },
                                                    scales: { x: { display: false }, y: { display: false } },
                                                    layout: { padding: 5 }
                                                }
                                            });
                                        });
                                    </script>
                                @else
                                    <div class="mt-2 text-muted" style="font-size: 0.7rem;">
                                        <i class="bi bi-clock-history"></i> Sblmnya: Rp {{ number_format($product->price_history[0]['price'], 0, ',', '.') }}
                                    </div>
                                @endif
                            @endif
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stok</label>
                            <input type="number" name="stock" class="form-control" value="{{ old('stock', $product->stock) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">SKU (Kode Unik)</label>
                            <input type="text" name="sku" class="form-control bg-light" value="{{ old('sku', $product->sku) }}" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Karakter Jual <i class="bi bi-info-circle text-primary" title="Aturan kecepatan habis barang"></i></label>
                            <select name="moving_class" class="form-select">
                                <option value="fast" {{ old('moving_class', $product->moving_class) == 'fast' ? 'selected' : '' }}>🔥 Fast Moving (Cepat Habis)</option>
                                <option value="normal" {{ old('moving_class', $product->moving_class ?? 'normal') == 'normal' ? 'selected' : '' }}>📦 Normal</option>
                                <option value="slow" {{ old('moving_class', $product->moving_class) == 'slow' ? 'selected' : '' }}>🐢 Slow Moving (Jarang Laku)</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- FIELD VARIANT PRODUCT --}}
                <div id="variant-product-fields" style="display: none;">
                    <div class="alert alert-info small py-2">
                        <i class="bi bi-info-circle me-1"></i> Edit varian produk di bawah ini.
                    </div>
                    <div class="table-responsive border rounded p-3 bg-white">
                        <table class="table table-bordered align-middle mb-0" id="variant-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 20%">Nama Varian</th>
                                    <th style="width: 15%">Foto Varian</th> 
                                    <th style="width: 15%">Harga Jual <span class="text-danger">*</span></th>
                                    <th style="width: 10%">Stok</th>
                                    <th style="width: 15%">SKU & Status</th>
                                    <th style="width: 10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Diisi oleh JS --}}
                            </tbody>
                        </table>
                        <div class="mt-3">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addVariantRow()">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Varian
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 🚨 BAGIAN GROSIR (EDIT MODE) 🚨 --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-primary">Harga Grosir</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" id="add-wholesale-btn">
                    <i class="bi bi-plus-lg"></i> Tambah Aturan Grosir
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0" id="wholesale-table">
                        <thead class="table-light">
                            <tr>
                                {{-- Kolom ini hanya muncul via JS jika produk bervarian --}}
                                <th id="th-variant-wholesale" style="display: none; width: 30%">Berlaku Untuk Varian</th>
                                <th>Minimal Pembelian (Qty)</th>
                                <th>Harga Satuan Grosir (Rp)</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="{{ route('client.products.index', $website->id) }}" class="btn btn-light border">Batal</a>
            <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
        </div>
    </form>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // --- 1. DATA DATABASE VIA BLADE DIRECTIVE ---
        const typeIndicator = document.getElementById('has_variants');
        const isVariant = typeIndicator ? typeIndicator.value === '1' : false;
        
        // Tarik data varian dan grosir langsung dari DB (Sangat Aman)
        const existingVariants = @json(\Illuminate\Support\Facades\DB::table('product_variants')->where('product_id', $product->id)->get());
        const existingWholesale = @json(\Illuminate\Support\Facades\DB::table('wholesale_prices')->where('product_id', $product->id)->orderBy('min_qty', 'asc')->get());

        // --- 2. LOGIKA VARIAN ---
        window.previewNewImage = function(event) {
            const input = event.target;
            const previewContainer = document.getElementById('preview-container');
            const imagePreview = document.getElementById('image-preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    previewContainer.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        };

        window.previewVariantImage = function(input, index) {
            let flagInput = input.closest('td').querySelector('.remove-image-flag');
            if(flagInput) flagInput.value = '0';
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let previewContainer = input.closest('td').querySelector('.variant-image-preview');
                    if (previewContainer) {
                        previewContainer.innerHTML = `
                            <div class="position-relative d-inline-block mb-1">
                                <img src="${e.target.result}" class="rounded border" style="height:50px; width:50px; object-fit:cover;">
                                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 start-100 translate-middle rounded-circle p-0" style="width:20px; height:20px; line-height:1;" onclick="window.removeVariantImage(this)">
                                    <i class="bi bi-x" style="font-size: 14px;"></i>
                                </button>
                            </div>
                        `;
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        };

        window.removeVariantImage = function(btn) {
            let td = btn.closest('td');
            td.querySelector('.variant-image-preview').innerHTML = '';
            let fileInput = td.querySelector('input[type="file"]');
            if(fileInput) fileInput.value = '';
            let flagInput = td.querySelector('.remove-image-flag');
            if(flagInput) flagInput.value = '1';
        };

        window.addVariantRow = function(data = null) {
            const index = Date.now() + Math.floor(Math.random() * 1000); 
            const tableBody = document.querySelector('#variant-table tbody');
            
            const idInput = data ? `<input type="hidden" name="variants[${index}][id]" value="${data.id}">` : '';
            const name = data ? data.name : '';
            const price = data ? data.price : '';
            const stock = data ? data.stock : '';
            const sku = data ? (data.sku || '') : '';
            const isActive = data ? data.is_active : 1;
            const movingClass = data ? (data.moving_class || 'normal') : 'normal';

            const isSkuLocked = (data && data.sku) ? 'readonly' : '';
            const skuBgClass = isSkuLocked ? 'bg-light' : ''; 

            const imagePreview = (data && data.image) 
                ? `<div class="position-relative d-inline-block mb-1">
                     <img src="/storage/${data.image}" class="rounded border" style="height:50px; width:50px; object-fit:cover;">
                     <button type="button" class="btn btn-danger btn-sm position-absolute top-0 start-100 translate-middle rounded-circle p-0" style="width:20px; height:20px; line-height:1;" onclick="window.removeVariantImage(this)">
                         <i class="bi bi-x" style="font-size: 14px;"></i>
                     </button>
                   </div>` 
                : '';

            let historyHtml = '';
            let priceHistory = [];
            if (data && data.price_history) {
                try { priceHistory = typeof data.price_history === 'string' ? JSON.parse(data.price_history) : data.price_history; } catch(e) { priceHistory = []; }
            }

            let canvasId = 'chart_variant_' + index;
            if (priceHistory && Array.isArray(priceHistory) && priceHistory.length >= 5) {
                historyHtml += `<div class="mt-2 p-1 bg-light border rounded" style="height: 60px; width: 99%;">
                                    <canvas id="${canvasId}"></canvas>
                                </div>`;
                let checkChartReady = setInterval(() => {
                    if (typeof Chart !== 'undefined') {
                        clearInterval(checkChartReady);
                        const ctx = document.getElementById(canvasId).getContext('2d');
                        let chartHistory = [...priceHistory].reverse();
                        let labels = chartHistory.map(h => new Date(h.changed_at.replace(' ', 'T')).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }));
                        let dataPoints = chartHistory.map(h => h.price);
                        labels.push('Sekarang');
                        dataPoints.push(price);

                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: [{ data: dataPoints, borderColor: '#0d6efd', borderWidth: 2, pointRadius: 2, fill: false, tension: 0.3 }]
                            },
                            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { enabled: true } }, scales: { x: { display: false }, y: { display: false } }, layout: { padding: 3 } }
                        });
                    }
                }, 100);
            } else if (priceHistory && Array.isArray(priceHistory) && priceHistory.length > 0) {
                historyHtml += `<div class="mt-2 p-2 bg-light border rounded text-start" style="font-size: 0.65rem;">
                                    <div class="fw-bold text-muted mb-1">Histori Harga (${priceHistory.length}):</div>`;
                priceHistory.forEach(hist => {
                    let formattedPrice = new Intl.NumberFormat('id-ID').format(hist.price);
                    historyHtml += `<div class="text-muted lh-1 mb-1">&bull; Rp ${formattedPrice}</div>`;
                });
                historyHtml += `</div>`;
            }

            const row = `
                <tr>
                    <td>
                        ${idInput}
                        <input type="text" name="variants[${index}][name]" class="form-control form-control-sm" placeholder="Misal: Merah" value="${name}" required>
                    </td>
                    <td class="text-center">
                        <div class="variant-image-preview text-center">${imagePreview}</div>
                        <input type="file" name="variants[${index}][image]" class="form-control form-control-sm mt-1" accept="image/*" onchange="window.previewVariantImage(this, ${index})">
                        <input type="hidden" name="variants[${index}][remove_image]" value="0" class="remove-image-flag">
                    </td>
                    <td class="align-top">
                        <input type="number" name="variants[${index}][price]" class="form-control form-control-sm" value="${price}" required>
                        ${historyHtml}
                    </td>
                    <td><input type="number" name="variants[${index}][stock]" class="form-control form-control-sm" value="${stock}" required></td>
                    <td>
                        <input type="text" name="variants[${index}][sku]" class="form-control form-control-sm mb-1 ${skuBgClass}" placeholder="SKU" value="${sku}" ${isSkuLocked}>
                        <select name="variants[${index}][is_active]" class="form-select form-select-sm mb-1">
                            <option value="1" ${isActive == 1 ? 'selected' : ''}>Status: Aktif</option>
                            <option value="0" ${isActive == 0 ? 'selected' : ''}>Status: Mati</option>
                        </select>
                        <select name="variants[${index}][moving_class]" class="form-select form-select-sm">
                            <option value="fast" ${movingClass == 'fast' ? 'selected' : ''}>🔥 Fast Moving</option>
                            <option value="normal" ${movingClass == 'normal' ? 'selected' : ''}>📦 Normal</option>
                            <option value="slow" ${movingClass == 'slow' ? 'selected' : ''}>🐢 Slow Moving</option>
                        </select>
                    </td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-danger btn-sm" onclick="window.removeRow(this)"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            `;
            tableBody.insertAdjacentHTML('beforeend', row);
        };

        window.removeRow = function(btn) {
            const tableBody = document.querySelector('#variant-table tbody');
            if (tableBody.children.length > 1) { btn.closest('tr').remove(); } 
            else { alert('Minimal harus ada 1 varian aktif.'); }
        };

        // Render Varian saat Edit Load
        const singleFields = document.getElementById('single-product-fields');
        const variantFields = document.getElementById('variant-product-fields');
        if (isVariant) {
            singleFields.style.display = 'none';
            variantFields.style.display = 'block';
            singleFields.querySelectorAll('input').forEach(input => input.disabled = true);
            if (Array.isArray(existingVariants) && existingVariants.length > 0) {
                existingVariants.forEach(variant => window.addVariantRow(variant));
            } else { window.addVariantRow(); }
        } else {
            singleFields.style.display = 'block';
            variantFields.style.display = 'none';
            variantFields.querySelectorAll('input, select, button').forEach(el => el.disabled = true);
        }

        // --- 3. LOGIKA HARGA GROSIR (EDIT) ---
        let wholesaleIndex = 0;
        
        window.addWholesaleRow = function(data = null) {
            let tbody = document.querySelector('#wholesale-table tbody');
            let tr = document.createElement('tr');
            
            let idInput = data ? `<input type="hidden" name="wholesale_prices[${wholesaleIndex}][id]" value="${data.id}">` : '';
            let minQty = data ? data.min_qty : '';
            let price = data ? data.price : '';
            let variantId = data ? data.product_variant_id : '';
            
            let variantDropdown = '';
            
            // 🚨 SULAP UX: JIKA PRODUK BERVARIAN, MUNCULKAN DROPDOWN PILIHAN VARIAN 🚨
            if (isVariant) {
                document.getElementById('th-variant-wholesale').style.display = ''; // Munculkan Header Kolom
                
                let options = `<option value="">-- Semua Varian (Global) --</option>`;
                if (Array.isArray(existingVariants)) {
                    existingVariants.forEach(v => {
                        let selected = (variantId == v.id) ? 'selected' : '';
                        options += `<option value="${v.id}" ${selected}>${v.name}</option>`;
                    });
                }
                
                variantDropdown = `
                    <td>
                        <select name="wholesale_prices[${wholesaleIndex}][product_variant_id]" class="form-select form-select-sm">
                            ${options}
                        </select>
                        <div class="form-text small text-muted">Pilih spesifik / biarkan kosong utk semua.</div>
                    </td>
                `;
            }

            tr.innerHTML = `
                ${variantDropdown}
                <td>
                    ${idInput}
                    <input type="number" name="wholesale_prices[${wholesaleIndex}][min_qty]" class="form-control form-control-sm" min="2" placeholder="Min. Qty" value="${minQty}" required>
                </td>
                <td>
                    <input type="number" name="wholesale_prices[${wholesaleIndex}][price]" class="form-control form-control-sm" min="0" placeholder="Harga Satuan" value="${price}" required>
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-danger btn-sm remove-wholesale-btn"><i class="bi bi-trash"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
            wholesaleIndex++;
        };

        // Render Grosir Lama jika ada di Database
        if (Array.isArray(existingWholesale) && existingWholesale.length > 0) {
            existingWholesale.forEach(w => window.addWholesaleRow(w));
        }

        // Event listener Tambah Grosir Baru
        document.getElementById('add-wholesale-btn').addEventListener('click', function() {
            window.addWholesaleRow();
        });

        // Event listener Hapus Grosir
        document.querySelector('#wholesale-table').addEventListener('click', function(e) {
            if(e.target.closest('.remove-wholesale-btn')) {
                e.target.closest('tr').remove();
            }
        });
    });
</script>
@endsection