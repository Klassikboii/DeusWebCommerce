@extends('layouts.admin')

@section('content')
<div class="container" style="max-width: 800px;">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold m-0">Edit Paket</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.packages.update', $package->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Paket</label>
                        <input type="text" name="name" class="form-control" placeholder="Misal: Enterprise Plan" required value="{{ $package->name }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Slug (Kode Unik)</label>
                        <input type="text" name="slug" class="form-control" placeholder="enterprise-yearly" required value="{{ $package->slug }}">
                        <small class="text-muted">Huruf kecil, tanpa spasi.</small>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Harga (Rupiah)</label>
                        <input type="number" name="price" class="form-control" placeholder="150000" required value="{{ $package->price }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Durasi (Hari)</label>
                        <input type="number" name="duration_days" class="form-control" value="{{ $package->duration_days }}" required>
                        <small class="text-muted">30 = 1 Bulan, 365 = 1 Tahun</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Maksimal Produk</label>
                    <input type="number" name="max_products" class="form-control" placeholder="100" required value="{{ $package->max_products }}">
                </div>

                {{-- Input Deskripsi --}}
                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">Deskripsi Singkat</label>
                        <input type="text" class="form-control" id="description" name="description" value="{{ old('description', $package->description ?? '') }}" placeholder="Contoh: Cocok untuk bisnis yang sedang berkembang">
                    </div>

                    {{-- Saklar Fitur Sistem (Boolean) --}}
                    <div class="mb-4 p-3 border rounded bg-light">
                        <label class="form-label fw-bold d-block text-primary"><i class="bi bi-cpu me-2"></i>Limitasi Sistem (Akses Fitur)</label>
                        <small class="text-muted d-block mb-3">Nyalakan saklar ini untuk memberikan akses fitur secara sistem kepada Klien.</small>
                        
                        <div class="form-check form-switch mb-2">
                            {{-- Value 1 agar dikirim sebagai true saat dicentang --}}
                            <input class="form-check-input" type="checkbox" name="custom_domain" id="custom_domain" value="1" {{ old('custom_domain', $package->can_custom_domain ?? 0) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="custom_domain">Buka Akses Custom Domain</label>
                        </div>
                        
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="remove_branding" id="remove_branding" value="1" {{ old('remove_branding', $package->remove_branding ?? 0) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="remove_branding">Izinkan Hapus Branding (White-label)</label>
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