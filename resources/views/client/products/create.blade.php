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
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3 text-primary">Informasi Dasar</h6>
                <div class="mb-3">
                    <label class="form-label">Foto Produk</label>
                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                    <div class="form-text small text-muted">Format: JPG, PNG, JPEG. Maksimal 2MB.</div>
                    @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Contoh: Kemeja Flannel Kotak" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                        <div class="form-text"><a href="#" class="text-decoration-none text-primary small">+ Buat Kategori Baru</a></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SKU (Kode Barang)</label>
                        <input type="text" name="sku" class="form-control" value="{{ old('sku') }}" placeholder="Opsional">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi Produk</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
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
                                <label class="form-check-label fw-bold" for="has_variants">Produk ini memiliki variasi (Ukuran, Warna, dll)</label>
                                <div class="form-text small">Jika aktif, harga dan stok akan diatur per variasi.</div>
                            </div>
                            <input class="form-check-input ms-0" type="checkbox" id="has_variants" name="has_variants" value="1" role="switch">
                        </div>

                    </div>
                </div>

                <div id="single-product-fields">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Harga (Rp)</label>
                            <input type="number" name="price" class="form-control" value="{{ old('price') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stok</label>
                            <input type="number" name="stock" class="form-control" value="{{ old('stock') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Berat (Gram)</label>
                            <input type="number" name="weight" class="form-control" value="{{ old('weight', 1000) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">SKU (Opsional)</label>
                            <input type="text" name="sku" class="form-control" value="{{ old('sku') }}">
                        </div>
                    </div>
                </div>

                <div id="variant-product-fields" style="display: none;">
                    <div class="table-responsive border rounded p-3 bg-white">
                        <table class="table table-bordered align-middle" id="variant-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 30%">Nama Varian <span class="text-danger">*</span><br><small class="text-muted fw-normal">Contoh: Merah - XL</small></th>
                                    <th style="width: 25%">Harga (Rp) <span class="text-danger">*</span></th>
                                    <th style="width: 15%">Stok <span class="text-danger">*</span></th>
                                    <th style="width: 20%">SKU / Kode</th>
                                    <th style="width: 10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Baris akan ditambahkan via JS --}}
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
            <button type="submit" class="btn btn-primary px-4">Simpan Produk</button>
        </div>
    </form>
</div>

<script>
                    const checkbox = document.getElementById('has_variants');
                    const singleFields = document.getElementById('single-product-fields');
                    const variantFields = document.getElementById('variant-product-fields');
                    const tableBody = document.querySelector('#variant-table tbody');

                    // 1. Logic Toggle Tampilan
                    function toggleVariantFields() {
                        if (checkbox.checked) {
                            singleFields.style.display = 'none';
                            variantFields.style.display = 'block';
                            // Disable input single agar tidak dikirim/divalidasi jika varian aktif
                            toggleInputs(singleFields, true);
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
                    
                    // Init saat load (berguna jika validasi error dan kembali ke form)
                    toggleVariantFields(); 

                    // 2. Logic Tambah Baris Varian
                    function addVariantRow(data = {}) {
                        const index = tableBody.children.length; // Index unik 0, 1, 2...
                        
                        const row = `
                            <tr>
                                <td>
                                    <input type="text" name="variants[${index}][name]" class="form-control form-control-sm" placeholder="Warna - Size" required>
                                </td>
                                <td>
                                    <input type="number" name="variants[${index}][price]" class="form-control form-control-sm" placeholder="0" required>
                                </td>
                                <td>
                                    <input type="number" name="variants[${index}][stock]" class="form-control form-control-sm" placeholder="0" required>
                                </td>
                                <td>
                                    <input type="text" name="variants[${index}][sku]" class="form-control form-control-sm" placeholder="SKU-XXX">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                        tableBody.insertAdjacentHTML('beforeend', row);
                    }

                    // Tambah 1 baris default jika kosong saat varian aktif
                    checkbox.addEventListener('change', () => {
                        if(checkbox.checked && tableBody.children.length === 0) addVariantRow();
                    });
                </script>
@endsection