@extends('layouts.client')

@section('title', 'Wawasan Pelanggan (AI)')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Wawasan Pelanggan</h3>
            <p class="text-muted mb-0">Segmentasi pelanggan cerdas menggunakan algoritma <strong>RFM Analysis</strong>.</p>
        </div>
        <div>
            {{-- Tombol untuk menjalankan ulang analisis secara manual jika klien tidak sabar menunggu tengah malam --}}
            <button class="btn btn-outline-primary" onclick="alert('Di mode produksi, fitur ini akan otomatis berjalan setiap jam 12 malam.')">
                <i class="bi bi-arrow-clockwise me-1"></i> Update Data
            </button>
            
        </div>
    </div>
    {{-- ALERT INFORMASI PEMBARUAN CRON JOB --}}
    <div class="alert alert-info border-info bg-info bg-opacity-10 d-flex align-items-center mb-4 shadow-sm">
        <i class="bi bi-info-circle-fill text-info fs-3 me-3"></i>
        <div>
            <h6 class="fw-bold text-info-emphasis mb-1">Kapan data ini diperbarui?</h6>
            <p class="mb-0 small text-muted">Untuk menjaga kecepatan website Anda, kecerdasan buatan (AI) kami menganalisis ribuan data transaksi secara serentak di latar belakang. Oleh karena itu, perubahan segmen pelanggan baru akan terlihat setiap hari pada pukul <strong>00:00 WIB (Tengah Malam)</strong>.</p>
        </div>
    </div>

    @if($rfmData->isEmpty())
        <div class="alert alert-warning">
            <i class="bi bi-info-circle me-2"></i> Belum ada data transaksi yang cukup untuk dianalisis. AI membutuhkan setidaknya beberapa pesanan selesai untuk mengelompokkan pelanggan.
        </div>
    @else
        <div class="row g-4 mb-4">
            {{-- BAGIAN GRAFIK PIE CHART --}}
            <div class="col-md-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4 text-center">
                        <h6 class="fw-bold mb-4">Distribusi Segmen Pelanggan</h6>
                        <div style="height: 250px; display: flex; justify-content: center;">
                            <canvas id="rfmChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BAGIAN PENJELASAN SEGMEN --}}
            <div class="col-md-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">Tindakan Lanjutan (Actionable Insights)</h6>
                        <div class="table-responsive">
                            <table class="table table-borderless table-sm mb-0">
                                <tbody>
                                    <tr class="border-bottom">
                                        <td style="width: 30px;"><span class="badge bg-success"><i class="bi bi-star-fill"></i></span></td>
                                        <td class="fw-bold text-success">Champions</td>
                                        <td class="text-muted small">Sering belanja, bayar mahal, transaksi terakhir masih baru. <strong>Beri mereka hadiah atau akses awal produk baru!</strong></td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td><span class="badge bg-info"><i class="bi bi-heart-fill"></i></span></td>
                                        <td class="fw-bold text-info">Loyal Customers</td>
                                        <td class="text-muted small">Pelanggan setia yang responsif. <strong>Minta mereka memberi ulasan/review toko.</strong></td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td><span class="badge bg-primary"><i class="bi bi-person-plus-fill"></i></span></td>
                                        <td class="fw-bold text-primary">New Customers</td>
                                        <td class="text-muted small">Baru bergabung dan belanja. <strong>Bantu mereka mengenali produk Anda agar kembali lagi.</strong></td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td><span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle-fill"></i></span></td>
                                        <td class="fw-bold text-warning">At Risk</td>
                                        <td class="text-muted small">Dulu sering belanja uang besar, tapi sekarang menghilang. <strong>Kirim pesan WA berisi diskon kangen agar mereka kembali!</strong></td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-secondary"><i class="bi bi-moon-stars-fill"></i></span></td>
                                        <td class="fw-bold text-secondary">Hibernating</td>
                                        <td class="text-muted small">Belanja kecil dan sudah sangat lama pergi. Tidak perlu membuang biaya promosi untuk grup ini.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- BAGIAN TABEL DATA PELANGGAN --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3">Daftar Pelanggan Detail</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Pelanggan</th>
                                <th>WhatsApp</th>
                                <th class="text-center">Terakhir Beli (Hari)</th>
                                <th class="text-center">Jumlah Beli</th>
                                <th class="text-end">Total Habis (Rp)</th>
                                <th>Segmen (AI)</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rfmData as $rfm)
                                <tr>
                                    <td class="fw-bold">{{ $rfm->customer_name ?? 'Tanpa Nama' }}</td>
                                    <td>{{ $rfm->customer_whatsapp }}</td>
                                    <td class="text-center">
                                        {{ $rfm->recency_days }} hr<br>
                                        <small class="text-muted">Skor: {{ $rfm->r_score }}/5</small>
                                    </td>
                                    <td class="text-center">
                                        {{ $rfm->frequency_count }}x<br>
                                        <small class="text-muted">Skor: {{ $rfm->f_score }}/5</small>
                                    </td>
                                    <td class="text-end">
                                        {{ number_format($rfm->monetary_value, 0, ',', '.') }}<br>
                                        <small class="text-muted">Skor: {{ $rfm->m_score }}/5</small>
                                    </td>
                                    <td>
                                        @php
                                            $badgeClass = match($rfm->segment) {
                                                'Champions' => 'bg-success',
                                                'Loyal Customers' => 'bg-info',
                                                'New / Recent Customers' => 'bg-primary',
                                                'At Risk' => 'bg-warning text-dark',
                                                'Hibernating' => 'bg-secondary',
                                                default => 'bg-dark'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} px-2 py-1">{{ $rfm->segment }}</span>
                                    </td>
                                    <td>
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $rfm->customer_whatsapp) }}" target="_blank" class="btn btn-sm btn-outline-success" title="Hubungi via WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- SCRIPT UNTUK GRAFIK CHART.JS --}}
@if(!$rfmData->isEmpty())
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Ambil data JSON yang dilempar dari Controller
        const segmentData = @json($segmentCounts);
        
        const labels = Object.keys(segmentData);
        const dataValues = Object.values(segmentData);

        // Warna yang sesuai dengan badge
        const backgroundColors = labels.map(label => {
            if(label.includes('Champion')) return '#198754'; // Success
            if(label.includes('Loyal')) return '#0dcaf0';    // Info
            if(label.includes('New')) return '#0d6efd';      // Primary
            if(label.includes('Risk')) return '#ffc107';     // Warning
            if(label.includes('Hibernating')) return '#6c757d'; // Secondary
            return '#212529'; // Dark
        });

        const ctx = document.getElementById('rfmChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: dataValues,
                    backgroundColor: backgroundColors,
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 11 } }
                    }
                }
            }
        });
    });
</script>
@endif
@endsection