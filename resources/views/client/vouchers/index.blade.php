@extends('layouts.client')

@section('title', 'Kelola Voucher & Diskon')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark mb-0">Voucher Toko</h3>
        <a href="{{ route('client.vouchers.create', $website->id) }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Buat Voucher Baru
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Kode Voucher</th>
                            <th>Tipe & Nilai Diskon</th>
                            <th>Min. Belanja</th>
                            <th>Kuota Terpakai</th>
                            <th>Berlaku Hingga</th>
                            <th>Target RFM</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vouchers as $voucher)
                            <tr>
                                <td><span class="badge bg-dark fs-6">{{ $voucher->code }}</span></td>
                                <td>
                                    @if($voucher->discount_type == 'nominal')
                                        Rp {{ number_format($voucher->discount_value, 0, ',', '.') }}
                                    @else
                                        {{ $voucher->discount_value }}% 
                                        <br><small class="text-muted">(Maks. Rp {{ number_format($voucher->max_discount_amount, 0, ',', '.') }})</small>
                                    @endif
                                </td>
                                <td>Rp {{ number_format($voucher->min_purchase, 0, ',', '.') }}</td>
                                <td>
                                    {{ $voucher->used_count }} / 
                                    {{ $voucher->max_uses ? $voucher->max_uses : '∞' }}
                                </td>
                                <td>
                                    @if($voucher->valid_until)
                                        {{ $voucher->valid_until->format('d M Y H:i') }}
                                    @else
                                        <span class="text-muted">Tanpa Batas</span>
                                    @endif
                                </td>
                                <td>
                                    @if($voucher->target_rfm_segment)
                                        <span class="badge bg-info text-dark">{{ $voucher->target_rfm_segment }}</span>
                                    @else
                                        <span class="badge bg-secondary">Semua Pelanggan</span>
                                    @endif
                                </td>
                                <td>
                                    @if($voucher->is_active && ($voucher->valid_until == null || $voucher->valid_until > now()) && ($voucher->max_uses == null || $voucher->used_count < $voucher->max_uses))
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak Aktif / Habis</span>
                                    @endif
                                </td>
                                <td>
                                    <!-- Tombol Edit (Contoh pakai Modal) -->
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editVoucherModal{{ $voucher->id }}">
                                        Edit
                                    </button>
                                    
                                   <div class="modal fade" id="editVoucherModal{{ $voucher->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header bg-light">
                                                    <h5 class="modal-title fw-bold">Edit Voucher: {{ $voucher->code }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('client.vouchers.update', [$website->id, $voucher->id]) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body text-start">
                                                        <div class="row g-3">
                                                            
                                                            {{-- Kode & Status --}}
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-bold">Kode Voucher</label>
                                                                <input type="text" name="code" class="form-control text-uppercase" value="{{ $voucher->code }}" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-bold">Status Voucher</label>
                                                                <select class="form-select" name="is_active">
                                                                    <option value="1" {{ $voucher->is_active ? 'selected' : '' }}>Aktif</option>
                                                                    <option value="0" {{ !$voucher->is_active ? 'selected' : '' }}>Nonaktif</option>
                                                                </select>
                                                            </div>

                                                            {{-- Tipe & Nilai Diskon --}}
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-bold">Tipe Diskon</label>
                                                                {{-- 🚨 ID Unik untuk JS: editDiscountType_ID --}}
                                                                <select name="discount_type" id="editDiscountType_{{ $voucher->id }}" class="form-select" onchange="toggleMaxDiscount({{ $voucher->id }})">
                                                                    <option value="nominal" {{ $voucher->discount_type == 'nominal' ? 'selected' : '' }}>Nominal (Rp)</option>
                                                                    <option value="percent" {{ $voucher->discount_type == 'percent' ? 'selected' : '' }}>Persentase (%)</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-bold">Nilai Diskon</label>
                                                                <input type="number" name="discount_value" class="form-control" value="{{ $voucher->discount_value }}" required>
                                                            </div>

                                                            {{-- Batasan Pembelian --}}
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-bold">Minimal Belanja (Rp)</label>
                                                                <input type="number" name="min_purchase" class="form-control" value="{{ $voucher->min_purchase }}">
                                                            </div>
                                                            
                                                            {{-- 🚨 ID Unik untuk Container Maks Diskon --}}
                                                            <div class="col-md-6" id="editMaxDiscountContainer_{{ $voucher->id }}" style="display: {{ $voucher->discount_type == 'percent' ? 'block' : 'none' }};">
                                                                <label class="form-label fw-bold">Maksimal Potongan (Rp)</label>
                                                                <input type="number" name="max_discount_amount" class="form-control" value="{{ $voucher->max_discount_amount }}">
                                                            </div>

                                                            {{-- Kuota & Target RFM --}}
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-bold">Batas Kuota Pemakaian (Optional)</label>
                                                                <input type="number" name="max_uses" class="form-control" value="{{ $voucher->max_uses }}">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-bold">Target Segmen (RFM)</label>
                                                                <select name="target_rfm_segment" class="form-select">
                                                                    <option value="" {{ empty($voucher->target_rfm_segment) ? 'selected' : '' }}>-- Semua Pelanggan (Publik) --</option>
                                                                    <option value="Champions" {{ $voucher->target_rfm_segment == 'Champions' ? 'selected' : '' }}>Champions (VIP)</option>
                                                                    <option value="Loyal Customers" {{ $voucher->target_rfm_segment == 'Loyal Customers' ? 'selected' : '' }}>Loyal Customers</option>
                                                                    <option value="New / Recent Customers" {{ $voucher->target_rfm_segment == 'New / Recent Customers' ? 'selected' : '' }}>New / Recent Customers</option>
                                                                    <option value="Potential / Needs Attention" {{ $voucher->target_rfm_segment == 'Potential / Needs Attention' ? 'selected' : '' }}>Potential / Needs Attention</option>
                                                                    <option value="At Risk" {{ $voucher->target_rfm_segment == 'At Risk' ? 'selected' : '' }}>At Risk</option>
                                                                    <option value="Hibernating" {{ $voucher->target_rfm_segment == 'Hibernating' ? 'selected' : '' }}>Hibernating</option>
                                                                </select>
                                                            </div>

                                                            {{-- Waktu Mulai & Berakhir (Format datetime-local harus Y-m-d\TH:i) --}}
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-bold">Berlaku Mulai</label>
                                                                <input type="datetime-local" name="valid_from" class="form-control" 
                                                                    value="{{ $voucher->valid_from ? \Carbon\Carbon::parse($voucher->valid_from)->format('Y-m-d\TH:i') : '' }}">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-bold">Berlaku Hingga</label>
                                                                <input type="datetime-local" name="valid_until" class="form-control" 
                                                                    value="{{ $voucher->valid_until ? \Carbon\Carbon::parse($voucher->valid_until)->format('Y-m-d\TH:i') : '' }}">
                                                            </div>

                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-light">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Perubahan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tombol Hapus -->
                                    <form action="{{ route('client.vouchers.destroy', [$website, $voucher->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus voucher ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                    <form action="{{ route('client.vouchers.toggle', [$website, $voucher->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        @if($voucher->is_active)
                                            <button type="submit" class="btn btn-sm btn-warning">Matikan</button>
                                        @else
                                            <button type="submit" class="btn btn-sm btn-success">Aktifkan</button>
                                        @endif
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Belum ada voucher yang dibuat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    // Fungsi dinamis untuk Toggle Max Discount di setiap Modal Edit
    function toggleMaxDiscount(voucherId) {
        const typeSelect = document.getElementById('editDiscountType_' + voucherId);
        const maxContainer = document.getElementById('editMaxDiscountContainer_' + voucherId);
        
        if (typeSelect && maxContainer) {
            if (typeSelect.value === 'percent') {
                maxContainer.style.display = 'block';
            } else {
                maxContainer.style.display = 'none';
                // Opsional: Kosongkan nilai saat pindah ke nominal
                maxContainer.querySelector('input').value = ''; 
            }
        }
    }
</script>
@endsection