@extends('layouts.client')

@section('title', 'Edit Produk')

@section('content')
<div class="container-fluid p-0" style="max-width: 800px;">
    
    <div class="mb-4">
        <a href="{{ route('client.products.index', $website->id) }}" class="text-decoration-none text-muted mb-2 d-inline-block">
            <i class="bi bi-arrow-left"></i> Kembali ke Produk
        </a>
        <h4 class="fw-bold">Edit Produk: {{ $product->name }}</h4>
    </div>
    {{-- PENANGKAP PESAN ERROR VALIDASI LARAVEL --}}
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
                
                {{-- Foto Produk --}}
                <div class="mb-3">
                    <label class="form-label">Foto Produk</label>
                    
                    {{-- Wadah Preview: Selalu ada, tapi disembunyikan jika kosong --}}
                    <div class="mb-2" id="preview-container" style="{{ $product->image ? '' : 'display: none;' }}">
                        <img id="image-preview" 
                             src="{{ $product->image ? asset('storage/' . $product->image) : '#' }}" 
                             alt="Preview" 
                             class="img-thumbnail rounded" 
                             style="height: 100px; width: 100px; object-fit: cover;">
                    </div>
                    
                    {{-- Tambahkan ID dan event onchange di input file --}}
                    <input type="file" name="image" id="image-input" class="form-control" accept="image/*" onchange="previewNewImage(event)">
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                    <div class="mb-3 p-3 bg-light rounded border">
                    <div class="form-check form-switch d-flex align-items-center m-0 p-0">
                        <input class="form-check-input ms-0 me-3 mt-0" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }} style="width: 2.5em; height: 1.25em;">
                        <label class="form-check-label fw-bold mb-0" for="is_active" style="cursor: pointer;">Produk Aktif (Ditampilkan di Toko)</label>
                    </div>
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

                    {{-- BERAT GLOBAL (Dipindah ke sini agar aman) --}}
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
                        <div class="form-check form-switch p-3 border rounded bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <label class="form-check-label fw-bold" for="has_variants">Produk ini memiliki variasi?</label>
                                <div class="form-text small">Centang jika produk punya ukuran/warna berbeda.</div>
                            </div>
                            {{-- Cek apakah produk punya varian di DB --}}
                            {{-- INDIKATOR TIPE PRODUK (MENGGANTIKAN CHECKBOX) --}}
                            <div class="mb-4 p-3 bg-light border rounded">
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
                </div>

                {{-- FIELD SINGLE PRODUCT --}}
                <div id="single-product-fields">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Harga (Rp)</label>
                            <input type="number" name="price" class="form-control" value="{{ old('price', $product->price) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stok</label>
                            <input type="number" name="stock" class="form-control" value="{{ old('stock', $product->stock) }}">
                        </div>
                        {{-- UNTUK PRODUK UTAMA (SINGLE) --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">SKU (Stock Keeping Unit)</label>
                            {{-- 🚨 TAMBAHKAN READONLY DI SINI --}}
                            <input type="text" name="sku" class="form-control bg-light" value="{{ old('sku', $product->sku) }}" readonly>
                            <div class="form-text text-danger">
                                <i class="bi bi-info-circle"></i> SKU bersifat permanen dan tidak dapat diubah setelah produk dibuat demi menjaga sinkronisasi dengan Accurate. Jika terjadi kesalahan, silakan hapus dan buat produk baru.
                            </div>
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

        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="{{ route('client.products.index', $website->id) }}" class="btn btn-light border">Batal</a>
            <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
        </div>
    </form>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        // ==========================================
        // 1. DAFTARKAN SEMUA FUNGSI TERLEBIH DAHULU
        // (Agar aman dari efek domino jika ada error data)
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

        // Fungsi Render Baris Tabel Varian
        window.addVariantRow = function(data = null) {
            const index = Date.now() + Math.floor(Math.random() * 1000); 
            const tableBody = document.querySelector('#variant-table tbody');
            
            const idInput = data ? `<input type="hidden" name="variants[${index}][id]" value="${data.id}">` : '';
            const name = data ? data.name : '';
            const price = data ? data.price : '';
            const stock = data ? data.stock : '';
            const sku = data ? (data.sku || '') : '';
            const isActive = data ? data.is_active : 1;

            // Kunci hanya berlaku untuk SKU
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

            const row = `
                <tr>
                    <td>
                        ${idInput}
                        <input type="text" name="variants[${index}][name]" class="form-control form-control-sm" placeholder="Misal: Merah" value="${name}" required>
                    </td>
                    <td class="text-center">
                        <div class="variant-image-preview text-center">
                            ${imagePreview}
                        </div>
                        <input type="file" name="variants[${index}][image]" class="form-control form-control-sm mt-1" accept="image/*" onchange="window.previewVariantImage(this, ${index})">
                        <input type="hidden" name="variants[${index}][remove_image]" value="0" class="remove-image-flag">
                    </td>
                    <td><input type="number" name="variants[${index}][price]" class="form-control form-control-sm" value="${price}" required></td>
                    <td><input type="number" name="variants[${index}][stock]" class="form-control form-control-sm" value="${stock}" required></td>
                    <td>
                        <input type="text" name="variants[${index}][sku]" class="form-control form-control-sm mb-1 ${skuBgClass}" placeholder="SKU" value="${sku}" ${isSkuLocked}>
                        <select name="variants[${index}][is_active]" class="form-select form-select-sm">
                            <option value="1" ${isActive == 1 ? 'selected' : ''}>Aktif</option>
                            <option value="0" ${isActive == 0 ? 'selected' : ''}>Mati</option>
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
                alert('Minimal harus ada 1 varian aktif.');
            }
        };

        // ==========================================
        // 2. EKSEKUSI PENGAMBILAN DATA & RENDER UI
        // ==========================================
        const typeIndicator = document.getElementById('has_variants');
        const isVariant = typeIndicator ? typeIndicator.value === '1' : false;
        
        const singleFields = document.getElementById('single-product-fields');
        const variantFields = document.getElementById('variant-product-fields');
        
        // 🚨 TARIK DATA VARIAN LANGSUNG DARI DATABASE (Kebal Error Relasi)
        const existingVariants = @json(\Illuminate\Support\Facades\DB::table('product_variants')->where('product_id', $product->id)->get());

        if (isVariant) {
            singleFields.style.display = 'none';
            variantFields.style.display = 'block';
            singleFields.querySelectorAll('input').forEach(input => input.disabled = true);
            
            // Cek apakah data array valid dan merender baris
            if (Array.isArray(existingVariants) && existingVariants.length > 0) {
                existingVariants.forEach(variant => window.addVariantRow(variant));
            } else {
                window.addVariantRow(); // Beri 1 baris kosong jika kebetulan datanya kosong
            }
        } else {
            singleFields.style.display = 'block';
            variantFields.style.display = 'none';
            variantFields.querySelectorAll('input, select, button').forEach(el => el.disabled = true);
        }
    });
</script>
@endsection