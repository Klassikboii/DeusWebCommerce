@extends('layouts.client') @section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Pengaturan Keuntungan Ongkir (Markup)</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tambah Aturan Baru</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('client.shipping_markups.store', $website->id) }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label>Kota Tujuan</label>
                            <select name="city_id" class="form-control select2" required>
                                <option value="">-- Pilih Kota --</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}">{{ $city->type }} {{ $city->name }} ({{ $city->province->name }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Tipe Keuntungan</label>
                            <select name="markup_type" class="form-control" required>
                                <option value="nominal">Nominal (Rupiah)</option>
                                <option value="percent">Persentase (%)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Jumlah Keuntungan</label>
                            <input type="number" name="markup_value" class="form-control" placeholder="Contoh: 5000" required>
                            <small class="text-muted">Isi angka saja. Jika tipe persen, isi 10 untuk 10%.</small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Simpan Aturan</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Keuntungan Ongkir</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Kota Tujuan</th>
                                    <th>Provinsi</th>
                                    <th>Tipe</th>
                                    <th>Keuntungan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($markups as $markup)
                                <tr>
                                    <td>{{ $markup->city->type }} {{ $markup->city->name }}</td>
                                    <td>{{ $markup->city->province->name }}</td>
                                    <td>
                                        <span class="badge {{ $markup->markup_type == 'nominal' ? 'bg-success' : 'bg-info' }}">
                                            {{ ucfirst($markup->markup_type) }}
                                        </span>
                                    </td>
                                    <td class="fw-bold">
                                        @if($markup->markup_type == 'nominal')
                                            Rp {{ number_format($markup->markup_value, 0, ',', '.') }}
                                        @else
                                            {{ $markup->markup_value }}%
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('client.shipping_markups.destroy', ['website' => $website->id, 'id' => $markup->id]) }}" method="POST" onsubmit="return confirm('Hapus aturan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada aturan markup ongkir yang dibuat.</td>
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

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "-- Cari Kota --"
        });
    });
</script>
@endsection