@extends('layouts.client')

@section('title', 'Pengaturan Pengiriman (Radius)')

{{-- LOAD CSS & JS LEAFLET (OPENSTREETMAP) --}}
@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map { height: 300px; width: 100%; border-radius: 8px; z-index: 1; }
</style>
@endsection

@section('content')
<div class="container-fluid p-0">
    <h4 class="fw-bold mb-3">Pengaturan Pengiriman (Radius)</h4>

    <div class="row">
        {{-- BAGIAN 1: SETTING LOKASI TOKO --}}
        <div class="col-md-5 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0">1. Titik Lokasi Toko</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Geser pin di peta untuk menentukan lokasi toko Anda. Titik ini akan menjadi pusat perhitungan jarak.</p>
                    
                    {{-- PETA --}}
                    <div id="map" class="mb-3 border"></div>

                    <form action="{{ route('client.shipping.updateLocation', $website->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="small text-muted">Latitude</label>
                                <input type="text" id="lat" name="latitude" class="form-control form-control-sm bg-light" value="{{ $website->latitude }}" readonly>
                            </div>
                            <div class="col-6">
                                <label class="small text-muted">Longitude</label>
                                <input type="text" id="lng" name="longitude" class="form-control form-control-sm bg-light" value="{{ $website->longitude }}" readonly>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-sm">Simpan Lokasi Toko</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- BAGIAN 2: TABEL HARGA JARAK --}}
        <div class="col-md-7 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">2. Tarif per Jarak</h6>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addRangeModal">
                        <i class="bi bi-plus-lg"></i> Tambah Tarif
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Jarak Min</th>
                                    <th>Jarak Max</th>
                                    <th>Harga</th>
                                    <th class="text-end pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ranges as $range)
                                <tr>
                                    <td class="ps-4">{{ $range->min_km }} KM</td>
                                    <td>{{ $range->max_km }} KM</td>
                                    <td class="fw-bold text-success">Rp {{ number_format($range->price, 0, ',', '.') }}</td>
                                    <td class="text-end pe-4">
                                        <form action="{{ route('client.shipping.destroy', [$website->id, $range->id]) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-link text-danger p-0 btn-sm"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small">Belum ada setting tarif.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH RANGE --}}
<div class="modal fade" id="addRangeModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <form action="{{ route('client.shipping.store', $website->id) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Tambah Tarif</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="small">Dari (KM)</label>
                    <input type="number" name="min_km" class="form-control" step="0.1" placeholder="0" required>
                </div>
                <div class="mb-2">
                    <label class="small">Sampai (KM)</label>
                    <input type="number" name="max_km" class="form-control" step="0.1" placeholder="5" required>
                </div>
                <div class="mb-3">
                    <label class="small">Ongkos Kirim (Rp)</label>
                    <input type="number" name="price" class="form-control" placeholder="10000" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 1. Ambil koordinat awal dari database (atau default Surabaya)
        var curLat = {{ $website->latitude ?? -7.2575 }};
        var curLng = {{ $website->longitude ?? 112.7521 }};

        // 2. Inisialisasi Peta
        var map = L.map('map').setView([curLat, curLng], 13);

        // 3. Pasang Tile Layer (Gambar Peta - Gratis dari OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // 4. Tambahkan Marker (Bisa digeser)
        var marker = L.marker([curLat, curLng], {draggable: true}).addTo(map);

        // 5. Update Input saat marker digeser
        marker.on('dragend', function(event) {
            var position = marker.getLatLng();
            document.getElementById('lat').value = position.lat.toFixed(6);
            document.getElementById('lng').value = position.lng.toFixed(6);
        });

        // 6. Klik Peta untuk memindahkan marker
        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            document.getElementById('lat').value = e.latlng.lat.toFixed(6);
            document.getElementById('lng').value = e.latlng.lng.toFixed(6);
        });
    });
</script>
@endsection