@extends('layouts.client')

@section('title', 'Buat Voucher Baru')

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <a href="{{ route('client.vouchers.index', $website->id) }}" class="text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Voucher
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h4 class="fw-bold mb-4">Detail Voucher Baru</h4>

            <form action="{{ route('client.vouchers.store', $website->id) }}" method="POST">
                @csrf
                <div class="row g-3">
                    {{-- Kode & Tipe --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Kode Voucher</label>
                        <input type="text" name="code" class="form-control text-uppercase" placeholder="Contoh: MERDEKA20" required>
                        <small class="text-muted">Gunakan kombinasi huruf dan angka tanpa spasi.</small>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Tipe Diskon</label>
                        <select name="discount_type" id="discountType" class="form-select">
                            <option value="nominal">Nominal (Rp)</option>
                            <option value="percent">Persentase (%)</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Nilai Diskon</label>
                        <input type="number" name="discount_value" class="form-control" placeholder="Contoh: 50000" required>
                    </div>

                    {{-- Batasan --}}
                    <div class="col-md-6" id="maxDiscountContainer" style="display: none;">
                        <label class="form-label fw-bold">Maksimal Potongan (Rp)</label>
                        <input type="number" name="max_discount_amount" class="form-control" placeholder="Biarkan kosong jika tanpa batas">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Minimal Belanja (Rp)</label>
                        <input type="number" name="min_purchase" class="form-control" value="0" required>
                    </div>

                    {{-- Kuota & Target --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Batas Kuota Pemakaian (Optional)</label>
                        <input type="number" name="max_uses" class="form-control" placeholder="Contoh: 100 orang pertama">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Target Segmen Pelanggan (Privilege)</label>
                        <select name="target_rfm_segment" class="form-select">
                            <option value="">-- Semua Pelanggan (Publik) --</option>
                            <option value="Champions">Champions (VIP)</option>
                            <option value="Loyal Customers">Loyal Customers</option>
                            <option value="New / Recent Customers">New / Recent Customers</option>
                            <option value="Potential / Needs Attention">Potential / Needs Attention</option>
                            <option value="At Risk">At Risk</option>
                            <option value="Hibernating">Hibernating</option>
                        </select>
                        <small class="text-muted">Pilih jika voucher ini bersifat eksklusif.</small>
                    </div>

                    {{-- Waktu --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Berlaku Mulai</label>
                        <input type="datetime-local" name="valid_from" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Berlaku Hingga</label>
                        <input type="datetime-local" name="valid_until" class="form-control">
                    </div>

                    <div class="col-12 mt-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" checked>
                            <label class="form-check-label fw-bold" for="isActive">Voucher Langsung Aktif</label>
                        </div>
                    </div>

                    <div class="col-12 mt-4 text-end">
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save"></i> Simpan Voucher</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Script sederhana untuk menyembunyikan/menampilkan kolom "Maksimal Potongan"
    document.getElementById('discountType').addEventListener('change', function() {
        const maxContainer = document.getElementById('maxDiscountContainer');
        if (this.value === 'percent') {
            maxContainer.style.display = 'block';
        } else {
            maxContainer.style.display = 'none';
        }
    });
</script>
@endsection