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

        {{-- 1. INFORMASI DASAR --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3 text-primary">Informasi Dasar</h6>
                <div class="mb-3">
                    <label class="form-label">Foto Produk</label>
                    
                    {{-- 🚨 WADAH PREVIEW: Ditambahkan di sini --}}
                    <div class="mb-2" id="preview-container" style="display: none;">
                        <img id="image-preview" src="#" alt="Preview" class="img-thumbnail rounded" style="height: 100px; width: 100px; object-fit: cover;">
                    </div>
                    
                    {{-- 🚨 TRIGGER ONCHANGE: Ditambahkan di sini --}}
                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*" onchange="window.previewNewImage(event)">
                    
                    <div class="form-text small text-muted">Format: JPG, PNG, JPEG. Maksimal 2MB.</div>
                    @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Contoh: Kemeja Flannel Kotak" required>
                </div>
                
                {{-- Toggle Status --}}
                <div class="mb-3 p-3 bg-light rounded border">
                    <div class="form-check form-switch d-flex align-items-center m-0 p-0">
                        <input class="form-check-input ms-0 me-3 mt-0" type="checkbox" id="is_active" name="is_active" value="1" checked style="width: 2.5em; height: 1.25em;">
                        <label class="form-check-label fw-bold mb-0" for="is_active" style="cursor: pointer;">Produk Aktif (Ditampilkan di Toko)</label>
                    </div>
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
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Berat (Gram) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="weight" class="form-control" value="{{ old('weight', 1000) }}" required min="1">
                            <span class="input-group-text">gram</span>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi Produk</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        {{-- 2. HARGA & VARIAN --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3 text-primary">Harga & Inventaris</h6>
                
                <div class="row mb-4">
                    <div class="col-12">
                       <div class="form-check form-switch p-3 border rounded bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <label class="form-check-label fw-bold" for="has_variants">Produk ini memiliki variasi (Ukuran, Warna, dll)</label>
                                <div class="form-text small">Jika aktif, harga dan stok akan diatur per variasi.</div>
                            </div>
                            <input class="form-check-input ms-0" type="checkbox" id="has_variants" name="has_variants" value="1" role="switch">
                        </div>
                    </div>
                </div>

                {{-- SINGLE PRODUCT --}}
                <div id="single-product-fields">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control" value="{{ old('price') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stok <span class="text-danger">*</span></label>
                            <input type="number" name="stock" class="form-control" value="{{ old('stock') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">SKU (Kode Unik)</label>
                            <input type="text" name="sku" class="form-control" value="{{ old('sku') }}" placeholder="SKU-001">
                        </div>
                        {{-- TAMBAHAN: DROPDOWN MOVING CLASS --}}
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Karakter Jual <i class="bi bi-info-circle text-primary" title="Aturan kecepatan habis barang"></i></label>
                            <select name="moving_class" class="form-select">
                                <option value="fast" {{ old('moving_class', $product->moving_class ?? 'normal') == 'fast' ? 'selected' : '' }}>🔥 Fast Moving (Cepat Habis)</option>
                                <option value="normal" {{ old('moving_class', $product->moving_class ?? 'normal') == 'normal' ? 'selected' : '' }}>📦 Normal</option>
                                <option value="slow" {{ old('moving_class', $product->moving_class ?? 'normal') == 'slow' ? 'selected' : '' }}>🐢 Slow Moving (Jarang Laku)</option>
                            </select>
                            <div class="form-text small text-muted">Bantu sistem menentukan standar peringatan stok.</div>
                        </div>
                    </div>
                </div>

                {{-- VARIANT PRODUCT (Compare Price Dihapus!) --}}
                <div id="variant-product-fields" style="display: none;">
                    <div class="alert alert-info small py-2">
                        <i class="bi bi-info-circle me-1"></i> Tambahkan varian produk di bawah ini.
                    </div>
                    <div class="table-responsive border rounded p-3 bg-white">
                        <table class="table table-bordered align-middle mb-0" id="variant-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 25%">Nama Varian</th>
                                    <th style="width: 15%">Foto Varian</th> 
                                    <th style="width: 20%">Harga Jual <span class="text-danger">*</span></th>
                                    <th style="width: 10%">Stok</th>
                                    <th style="width: 20%">SKU & Status</th>
                                    <th style="width: 10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Baris ditambahkan via JS --}}
                            </tbody>
                        </table>
                        <div class="mt-3">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.addVariantRow()">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Varian
                            </button>
                        </div>
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

<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        // ==========================================
        // 1. DAFTARKAN SEMUA FUNGSI TERLEBIH DAHULU
        // ==========================================

        // Fungsi Preview Gambar Utama
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

        // Fungsi Preview Gambar Varian
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

        // Fungsi Hapus Gambar Varian
        window.removeVariantImage = function(btn) {
            let td = btn.closest('td');
            td.querySelector('.variant-image-preview').innerHTML = '';
            let fileInput = td.querySelector('input[type="file"]');
            if(fileInput) fileInput.value = '';
            let flagInput = td.querySelector('.remove-image-flag');
            if(flagInput) flagInput.value = '1';
        };

        // Fungsi Tambah Baris Varian (Versi Create - Tanpa Kunci SKU)
       // Fungsi Tambah Baris Varian (Versi Create - Tanpa Kunci SKU)
        window.addVariantRow = function() {
            const index = Date.now() + Math.floor(Math.random() * 1000); 
            const tableBody = document.querySelector('#variant-table tbody');
            
            // BERIKAN NILAI DEFAULT KARENA INI BARIS BARU
            const movingClass = 'normal';
            const sku = '';
            const isSkuLocked = '';
            const skuBgClass = '';
            const isActive = 1; // Default aktif

            const row = `
                <tr>
                    <td>
                        <input type="text" name="variants[${index}][name]" class="form-control form-control-sm" placeholder="Misal: Merah" required>
                    </td>
                    <td class="text-center">
                        <div class="variant-image-preview text-center"></div>
                        <input type="file" name="variants[${index}][image]" class="form-control form-control-sm mt-1" accept="image/*" onchange="window.previewVariantImage(this, ${index})">
                        <input type="hidden" name="variants[${index}][remove_image]" value="0" class="remove-image-flag">
                    </td>
                    <td><input type="number" name="variants[${index}][price]" class="form-control form-control-sm" required></td>
                    <td><input type="number" name="variants[${index}][stock]" class="form-control form-control-sm" required></td>
                    <td>
                        {{-- 1. Input SKU --}}
                        <input type="text" name="variants[${index}][sku]" class="form-control form-control-sm mb-1 ${skuBgClass}" placeholder="SKU" value="${sku}" ${isSkuLocked}>
                        
                        {{-- 2. Dropdown Status (Beri mb-1 agar ada jarak bawah) --}}
                        <select name="variants[${index}][is_active]" class="form-select form-select-sm mb-1">
                            <option value="1" ${isActive == 1 ? 'selected' : ''}>Status: Aktif</option>
                            <option value="0" ${isActive == 0 ? 'selected' : ''}>Status: Mati</option>
                        </select>
                        
                        {{-- 3. Dropdown Karakter Jual --}}
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

        // Fungsi Hapus Baris
        window.removeRow = function(btn) {
            const tableBody = document.querySelector('#variant-table tbody');
            if (tableBody.children.length > 1) {
                btn.closest('tr').remove();
            } else {
                alert('Minimal harus ada 1 varian aktif jika opsi variasi dinyalakan.');
            }
        };

        // ==========================================
        // 2. LOGIKA TOGGLE SINGLE VS VARIAN
        // ==========================================
        const checkbox = document.getElementById('has_variants');
        const singleFields = document.getElementById('single-product-fields');
        const variantFields = document.getElementById('variant-product-fields');
        const tableBody = document.querySelector('#variant-table tbody');

        function toggleVariantFields() {
            if (checkbox.checked) {
                singleFields.style.display = 'none';
                variantFields.style.display = 'block';
                toggleInputs(singleFields, true);
                
                // Tambahkan 1 baris otomatis jika tabel kosong saat diaktifkan
                if(tableBody.children.length === 0) window.addVariantRow();
            } else {
                singleFields.style.display = 'block';
                variantFields.style.display = 'none';
                toggleInputs(singleFields, false);
            }
        }

        function toggleInputs(container, isDisabled) {
            container.querySelectorAll('input').forEach(input => input.disabled = isDisabled);
        }

        checkbox.addEventListener('change', toggleVariantFields);
        toggleVariantFields(); // Inisialisasi awal saat halaman diload
    });
</script>

@endsection