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

    <form action="{{ route('client.products.update', ['website' => $website->id, 'product' => $product->id]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT') {{-- PENTING: Method PUT untuk Update --}}
        
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3 text-primary">Informasi Dasar</h6>
                
                {{-- Foto Produk (Tampilkan foto lama jika ada) --}}
                <div class="mb-3">
                    <label class="form-label">Foto Produk</label>
                    @if($product->image)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $product->image) }}" alt="Preview" class="img-thumbnail" style="height: 100px;">
                        </div>
                    @endif
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
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
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi Produk</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description', $product->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3 text-primary">Harga & Inventaris</h6>
                
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="form-check form-switch p-3 border rounded bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <label class="form-check-label fw-bold" for="has_variants">Produk ini memiliki variasi:</label>
                            </div>
                            <input class="form-check-input" type="checkbox" id="has_variants" name="has_variants" value="1" 
                                                            {{ old('has_variants', $product->hasVariants()) ? 'checked' : '' }}>                      
                            </div>
                    </div>
                </div>

                {{-- FIELD SINGLE PRODUCT --}}
                <div id="single-product-fields">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Harga (Rp)</label>
                            <input type="number" name="price" class="form-control" value="{{ old('price', $product->price) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stok</label>
                            <input type="number" name="stock" class="form-control" value="{{ old('stock', $product->stock) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Berat (Gram)</label>
                            <input type="number" name="weight" class="form-control" value="{{ old('weight', $product->weight) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">SKU (Opsional)</label>
                            <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}">
                        </div>
                    </div>
                </div>

                {{-- FIELD VARIANT PRODUCT --}}
                <div id="variant-product-fields" style="display: none;">
                    <div class="table-responsive border rounded p-3 bg-white">
                        <table class="table table-bordered align-middle" id="variant-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 30%">Nama Varian</th>
                                    <th style="width: 25%">Harga (Rp)</th>
                                    <th style="width: 15%">Stok</th>
                                    <th style="width: 20%">SKU</th>
                                    <th style="width: 10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Diisi oleh JS --}}
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addVariantRow()">
                            <i class="bi bi-plus-lg"></i> Tambah Varian
                        </button>
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

{{-- SCRIPT DI BAWAH SINI (Sesuai solusi Anda) --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const checkbox = document.getElementById('has_variants');
        const singleFields = document.getElementById('single-product-fields');
        const variantFields = document.getElementById('variant-product-fields');
        const tableBody = document.querySelector('#variant-table tbody');

        // 1. Data Varian Existing (Dari Controller)
        // Kita convert Collection Laravel ke JSON Array JS
        const existingVariants = @json($product->variants);

        // 2. Logic Toggle
        function toggleVariantFields() {
            if (checkbox.checked) {
                singleFields.style.display = 'none';
                variantFields.style.display = 'block';
                toggleInputs(singleFields, true);
            } else {
                singleFields.style.display = 'block';
                variantFields.style.display = 'none';
                toggleInputs(singleFields, false);
            }
        }

        function toggleInputs(container, isDisabled) {
            container.querySelectorAll('input, select').forEach(input => input.disabled = isDisabled);
        }

        checkbox.addEventListener('change', toggleVariantFields);
        toggleVariantFields(); // Init load

        // 3. Render Baris
        window.addVariantRow = function(data = null) {
            const index = tableBody.children.length; // Index 0, 1, 2...
            
            // Trik: Jika data ada (edit), kita butuh ID-nya untuk update
            // Jika data null (new), ID kosong
            const idInput = data ? `<input type="hidden" name="variants[${index}][id]" value="${data.id}">` : '';
            
            const name = data ? data.name : '';
            const price = data ? data.price : '';
            const stock = data ? data.stock : '';
            const sku = data ? (data.sku || '') : '';

            const row = `
                <tr>
                    <td>
                        ${idInput}
                        <input type="text" name="variants[${index}][name]" class="form-control form-control-sm" value="${name}" placeholder="Warna - Size" required>
                    </td>
                    <td>
                        <input type="number" name="variants[${index}][price]" class="form-control form-control-sm" value="${price}" placeholder="0" required>
                    </td>
                    <td>
                        <input type="number" name="variants[${index}][stock]" class="form-control form-control-sm" value="${stock}" placeholder="0" required>
                    </td>
                    <td>
                        <input type="text" name="variants[${index}][sku]" class="form-control form-control-sm" value="${sku}" placeholder="SKU-XXX">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tableBody.insertAdjacentHTML('beforeend', row);
        };

        window.removeRow = function(btn) {
            btn.closest('tr').remove();
        };

        // 4. Load Data Existing
        if (existingVariants.length > 0) {
            existingVariants.forEach(variant => {
                addVariantRow(variant);
            });
        } else {
            // Jika checkbox aktif tapi kosong, tambah 1 baris
            if (checkbox.checked) addVariantRow();
        }
        
        // Listener tambahan: Jika user baru centang checkbox, kasih 1 baris kosong
        checkbox.addEventListener('change', () => {
            if(checkbox.checked && tableBody.children.length === 0) addVariantRow();
        });
    });
</script>
@endsection