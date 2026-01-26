@extends('layouts.admin')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.packages.index') }}" class="text-decoration-none text-muted"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<div class="card border-0 shadow-sm" style="max-width: 600px;">
    <div class="card-body p-4">
        <h4 class="fw-bold mb-4">Edit Paket: {{ $package->name }}</h4>
        
        <form action="{{ route('admin.packages.update', $package->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Nama Paket</label>
                <input type="text" name="name" class="form-control" value="{{ $package->name }}" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Harga (Rp)</label>
                    <input type="number" name="price" class="form-control" value="{{ $package->price }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Limit Produk (Qty)</label>
                    <input type="number" name="max_products" class="form-control" value="{{ $package->max_products }}" required>
                </div>
            </div>

            <hr>
            <label class="form-label fw-bold mb-3">Fitur Premium</label>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="can_custom_domain" id="domainCheck" {{ $package->can_custom_domain ? 'checked' : '' }}>
                <label class="form-check-label" for="domainCheck">Izinkan Custom Domain</label>
            </div>

            <div class="form-check form-switch mb-4">
                <input class="form-check-input" type="checkbox" name="remove_branding" id="brandCheck" {{ $package->remove_branding ? 'checked' : '' }}>
                <label class="form-check-label" for="brandCheck">Izinkan Hapus "Powered By"</label>
            </div>

            <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
        </form>
    </div>
</div>
@endsection