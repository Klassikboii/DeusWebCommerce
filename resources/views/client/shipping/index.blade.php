@extends('layouts.client')

@section('title', 'Pengaturan Pengiriman')

@section('content')
{{-- ALERT ERROR VALIDASI DARI CONTROLLER --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-exclamation-octagon-fill fs-5 me-2"></i>
                <strong class="mb-0">Gagal menyimpan data!</strong>
            </div>
            <ul class="mb-0 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
<div class="container-fluid p-0">
    {{-- HEADER HALAMAN --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Pengiriman & Logistik</h4>
            <p class="text-muted small mb-0">Kelola kurir otomatis (RajaOngkir) dan tarif pengiriman manual toko Anda.</p>
        </div>
    </div>
    
    {{-- ALERT NOTIFIKASI --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }} 
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ======================================================= --}}
    {{-- BAGIAN 1: KURIR OTOMATIS (RAJAONGKIR) --}}
    {{-- ======================================================= --}}
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm border-top border-primary border-3">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-truck text-primary me-2"></i>Kurir Nasional (Otomatis)</h6>
                    <p class="small text-muted mb-0 mt-1">Tarif pengiriman akan dihitung otomatis berdasarkan berat produk dan kota tujuan via API RajaOngkir.</p>
                </div>
                <div class="card-body bg-light">
                   <form action="{{ route('client.shipping.update_couriers', $website->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        {{-- Trik agar jika semua di-uncheck, array kosong tetap terdeteksi --}}
                       
                        
                        <div class="row g-3">
                            @php
                                // Daftar kurir yang didukung API RajaOngkir
                                $availableCouriers = [
                                    'jne'      => 'JNE Express',
                                    'sicepat'  => 'SiCepat',
                                    'jnt'      => 'J&T Express',
                                    'pos'      => 'POS Indonesia',
                                    'anteraja' => 'AnterAja',
                                    'ninja'    => 'Ninja Xpress',
                                    'tiki'     => 'TIKI',
                                    'lion'     => 'Lion Parcel',
                                    'ide'      => 'ID Express',
                                    'sap'      => 'SAP Express'
                                ];
                                // Ambil data kurir yang aktif dari database
                                $activeCouriers = is_array($website->active_couriers) ? $website->active_couriers : [];
                            @endphp

                            @foreach($availableCouriers as $code => $name)
                            <div class="col-md-4">
                                <div class="border rounded p-3 d-flex justify-content-between align-items-center bg-white shadow-sm {{ in_array($code, $activeCouriers) ? 'border-primary' : '' }}">
                                    <div class="fw-bold text-uppercase text-dark">
                                        {{ $code }} <br>
                                        <span class="small text-muted fw-normal text-capitalize">{{ $name }}</span>
                                    </div>
                                    <div class="form-check form-switch fs-4 mb-0">
                                        <input class="form-check-input" type="checkbox" name="active_couriers[]" value="{{ $code }}" {{ in_array($code, $activeCouriers) ? 'checked' : '' }} style="cursor: pointer;">
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">Simpan Pilihan Kurir</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ======================================================= --}}
    {{-- BAGIAN 2: KURIR MANUAL / KUSTOM --}}
    {{-- ======================================================= --}}
    <div class="card border-0 shadow-sm border-top border-success border-3">
        <div class="card-header bg-white py-3 border-0 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h6 class="mb-0 fw-bold"><i class="bi bi-box-seam text-success me-2"></i>Kurir Manual / Kustom</h6>
                <p class="small text-muted mb-0 mt-1">Buat tarif pengiriman khusus seperti kurir toko lokal atau pengiriman instan.</p>
            </div>
            
            {{-- KUMPULAN TOMBOL AKSI MANUAL (Dipindah ke sini agar rapi) --}}
            <div class="d-flex flex-wrap gap-2">
                @if($rates->count() > 0)
                <form action="{{ route('client.shipping.clear', $website->id) }}" method="POST" class="m-0" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin MENGHAPUS SEMUA data ongkir? Tindakan ini tidak bisa dibatalkan.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger shadow-sm">
                        <i class="bi bi-trash me-1"></i> Hapus Semua
                    </button>
                </form>
                @endif

                <button class="btn btn-sm btn-outline-success shadow-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Import CSV
                </button>
                <button class="btn btn-sm btn-success shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Manual
                </button>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3">Kota Asal</th>
                            <th class="py-3">Kota Tujuan</th>
                            <th class="py-3">Ekspedisi</th>
                            <th class="py-3">Layanan</th>
                            <th class="py-3">Tarif / Kg</th>
                            <th class="py-3">Estimasi</th>
                            <th class="text-end pe-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rates as $rate)
                        <tr>
                            <td class="ps-4 text-muted">{{ $rate->origin_city }}</td>
                            <td class="fw-bold text-primary">{{ $rate->destination_city }}</td>
                            <td class="fw-semibold">{{ $rate->courier_name }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $rate->service_name ?? '-' }}</span>
                            </td>
                            <td class="text-success fw-bold">Rp {{ number_format($rate->rate_per_kg, 0, ',', '.') }}</td>
                            <td class="text-muted small">
                                @if($rate->min_day)
                                    {{ $rate->min_day }}{{ $rate->max_day ? ' - '.$rate->max_day : '' }} Hari
                                @elseif($rate->min_day && $rate->min_day == $rate->max_day)
                                    {{ $rate->min_day }} Hari
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    {{-- TOMBOL EDIT (Trigger Modal) --}}
                                    <button type="button" class="btn btn-sm btn-light border me-1 d-flex align-items-center"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editModal"
                                        data-action="{{ route('client.shipping.update', [$website->id, $rate->id]) }}"
                                        data-origin="{{ $rate->origin_city }}"
                                        data-destination="{{ $rate->destination_city }}"
                                        data-courier="{{ $rate->courier_name }}"
                                        data-service="{{ $rate->service_name }}"
                                        data-rate="{{ $rate->rate_per_kg }}"
                                        data-min-day="{{ $rate->min_day }}"
                                        data-max-day="{{ $rate->max_day }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    {{-- TOMBOL DELETE --}}
                                    <form action="{{ route('client.shipping.destroy', [$website->id, $rate->id]) }}" method="POST" class="m-0" onsubmit="return confirm('Hapus tarif ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light border text-danger d-flex align-items-center h-100"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-box-seam fs-1 d-block mb-3 opacity-25"></i>
                                <h6 class="fw-bold">Belum ada tarif manual.</h6>
                                <p class="small mb-0">Jika Anda menggunakan kurir lokal, silakan Import CSV atau Tambah Manual.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($rates->hasPages())
        <div class="card-footer bg-white border-0 py-3">{{ $rates->links() }}</div>
        @endif
    </div>
</div>

{{-- MODAL TAMBAH MANUAL --}}
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('client.shipping.store', $website->id) }}" method="POST" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2 text-success"></i>Tambah Ongkir Manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Kota Asal</label>
                        <input type="text" name="origin_city" class="form-control" placeholder="Cth: Surabaya" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Kota Tujuan</label>
                        <input type="text" name="destination_city" class="form-control" placeholder="Cth: Sidoarjo" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Nama Kurir</label>
                        <input type="text" name="courier_name" class="form-control" placeholder="Cth: Kurir Toko" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Layanan</label>
                        <input type="text" name="service_name" class="form-control" placeholder="Cth: Instan" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Tarif per Kg (Rp)</label>
                        <input type="number" name="rate_per_kg" class="form-control" placeholder="15000" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Estimasi (Hari)</label>
                        <div class="input-group">
                            <input type="number" name="min_day" class="form-control" placeholder="Min">
                            <span class="input-group-text bg-light border-start-0 border-end-0">-</span>
                            <input type="number" name="max_day" class="form-control" placeholder="Max">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success fw-bold">Simpan Tarif</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL IMPORT --}}
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('client.shipping.import', $website->id) }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-spreadsheet me-2 text-success"></i>Import Tarif CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info small mb-4">
                    <strong>Format 8 Kolom:</strong><br>
                    Asal, Tujuan, Kurir, Layanan, Tarif, Min Berat, Est Min, Est Max
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Pilih File CSV</label>
                    <input type="file" name="file" class="form-control" accept=".csv" required>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="submit" class="btn btn-success fw-bold px-4">Import Data</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editForm" method="POST" class="modal-content border-0 shadow">
            @csrf
            @method('PUT')
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Ongkos Kirim</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Kota Asal</label>
                        <input type="text" name="origin_city" id="edit_origin" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Kota Tujuan</label>
                        <input type="text" name="destination_city" id="edit_destination" class="form-control" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Kurir</label>
                        <input type="text" name="courier_name" id="edit_courier" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Layanan</label>
                        <input type="text" name="service_name" id="edit_service" class="form-control" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Tarif per Kg (Rp)</label>
                        <input type="number" name="rate_per_kg" id="edit_rate" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Estimasi (Hari)</label>
                        <div class="input-group">
                            <input type="number" name="min_day" id="edit_min_day" class="form-control" placeholder="Min">
                            <span class="input-group-text bg-light border-start-0 border-end-0">-</span>
                            <input type="number" name="max_day" id="edit_max_day" class="form-control" placeholder="Max">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary fw-bold">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- SCRIPT MODAL EDIT (TIDAK ADA YANG DIHAPUS) --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const editModal = document.getElementById('editModal');
        
        if(editModal) {
            editModal.addEventListener('show.bs.modal', function(event) {
                // Tombol yang diklik
                const button = event.relatedTarget;
                
                // Ambil data dari atribut data-*
                const action = button.getAttribute('data-action');
                const origin = button.getAttribute('data-origin');
                const destination = button.getAttribute('data-destination');
                const courier = button.getAttribute('data-courier');
                const service = button.getAttribute('data-service');
                const rate = button.getAttribute('data-rate');
                const minDay = button.getAttribute('data-min-day');
                const maxDay = button.getAttribute('data-max-day');

                // Isi Form
                const form = document.getElementById('editForm');
                form.action = action;
                
                document.getElementById('edit_origin').value = origin;
                document.getElementById('edit_destination').value = destination;
                document.getElementById('edit_courier').value = courier;
                document.getElementById('edit_service').value = service;
                document.getElementById('edit_rate').value = rate;
                document.getElementById('edit_min_day').value = minDay;
                document.getElementById('edit_max_day').value = maxDay;
            });
        }
    });
</script>
@endsection