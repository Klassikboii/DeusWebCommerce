@extends('layouts.admin')

@section('content')
<div class="container" style="max-width: 800px;">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold m-0">Buat Paket Baru</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.packages.store') }}" method="POST">
                @csrf
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Paket</label>
                        <input type="text" name="name" class="form-control" placeholder="Misal: Enterprise Plan" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Slug (Kode Unik)</label>
                        <input type="text" name="slug" class="form-control" placeholder="enterprise-yearly" required>
                        <small class="text-muted">Huruf kecil, tanpa spasi.</small>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Harga (Rupiah)</label>
                        <input type="number" name="price" class="form-control" placeholder="150000" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Durasi (Hari)</label>
                        <input type="number" name="duration_days" class="form-control" value="30" required>
                        <small class="text-muted">30 = 1 Bulan, 365 = 1 Tahun</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Maksimal Produk</label>
                    <input type="number" name="max_products" class="form-control" placeholder="100" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Fitur Tambahan</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="can_custom_domain" id="customDomain">
                        <label class="form-check-label" for="customDomain">Bisa Custom Domain (.com/.id)</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="remove_branding" id="removeBrand">
                        <label class="form-check-label" for="removeBrand">Hapus Branding "Powered by WebCommerce"</label>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.packages.index') }}" class="btn btn-light border">Batal</a>
                    <button type="submit" class="btn btn-primary px-4">Simpan Paket</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection