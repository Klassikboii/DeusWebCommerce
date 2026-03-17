@extends('layouts.client')
@section('title', 'Wawasan Cerdas (AI)')

@section('content')
<div class="container-fluid p-0">
    
    {{-- HEADER & BANNER INFO (Tetap di atas) --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Wawasan Cerdas (AI Analytics)</h3>
            <p class="text-muted mb-0">Pusat analisis data pelanggan dan pola belanja toko Anda.</p>
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
{{-- ========================================== --}}
    {{-- NAVIGASI TAB BOTSOTRAP                     --}}
    {{-- ========================================== --}}
    <ul class="nav nav-tabs mb-4" id="aiInsightsTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold" id="rfm-tab" data-bs-toggle="tab" data-bs-target="#rfm-content" type="button" role="tab">
                <i class="bi bi-people-fill me-1"></i> Segmen Pelanggan (RFM)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold" id="mba-tab" data-bs-toggle="tab" data-bs-target="#mba-content" type="button" role="tab">
                <i class="bi bi-cart-check-fill me-1"></i> Pola Belanja (MBA)
            </button>
        </li>
    </ul>

    {{-- ISI DARI TAB --}}
    <div class="tab-content" id="aiInsightsTabContent">
        
        {{-- TAB 1: ISI KODE RFM ANDA YANG LAMA DI SINI --}}
        <div class="tab-pane fade show active" id="rfm-content" role="tabpanel">
                    
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
                                        <td class="text-muted small">Sering belanja, bayar mahal, transaksi masih baru. <strong>Kirim pesan WA apresiasi sebagai pelanggan VIP. Informasikan produk baru secara eksklusif sebelum di-upload ke toko!</strong></td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td><span class="badge bg-info"><i class="bi bi-heart-fill"></i></span></td>
                                        <td class="fw-bold text-info">Loyal Customers</td>
                                        <td class="text-muted small">Pelanggan setia yang responsif. <strong>Sapa via WA untuk menanyakan kepuasan mereka. Jadikan masukan mereka sebagai bahan evaluasi toko.</strong></td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td><span class="badge bg-primary"><i class="bi bi-person-plus-fill"></i></span></td>
                                        <td class="fw-bold text-primary">New Customers</td>
                                        <td class="text-muted small">Baru bergabung dan belanja. <strong>Follow-up via WA untuk memastikan pesanan sampai dengan aman dan tawarkan bantuan jika ada kendala.</strong></td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td><span class="badge bg-dark"><i class="bi bi-eye-fill"></i></span></td>
                                        <td class="fw-bold text-dark">Potential / Needs Attention</td>
                                        <td class="text-muted small">Membeli rata-rata, tapi baru-baru ini. <strong>Lihat riwayat belanjanya, lalu tawarkan produk pelengkap (Cross-sell) secara manual via WA.</strong></td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td><span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle-fill"></i></span></td>
                                        <td class="fw-bold text-warning">At Risk</td>
                                        <td class="text-muted small">Dulu belanja uang besar, tapi sekarang menghilang. <strong>Kirim pesan WA personal untuk menanyakan kabar, tawarkan potongan harga khusus (manual via transfer) untuk menarik mereka kembali.</strong></td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-secondary"><i class="bi bi-moon-stars-fill"></i></span></td>
                                        <td class="fw-bold text-secondary">Hibernating</td>
                                        <td class="text-muted small">Belanja kecil dan sudah sangat lama pergi. Tidak perlu memprioritaskan waktu Anda untuk grup ini.</td>
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

        {{-- TAB 2: KODE BARU UNTUK MARKET BASKET ANALYSIS --}}
        <div class="tab-pane fade" id="mba-content" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Aturan Asosiasi Produk (Sering Dibeli Bersamaan)</h6>
                    <p class="text-muted small mb-4">
                        Data ini menunjukkan kecenderungan pembeli. Gunakan metrik <strong>Lift</strong> (> 1.0) sebagai acuan kekuatan hubungan antar produk untuk merancang promo <em>Bundling</em>.
                    </p>

                    @if($mbaData->isEmpty())
                        <div class="alert alert-warning text-center">
                            <i class="bi bi-info-circle me-2"></i> Belum ada pola belanja yang ditemukan. AI membutuhkan lebih banyak data transaksi pesanan.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle border">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 20%">Jika pelanggan membeli...</th>
                                        <th style="width: 20%">Mereka biasanya juga beli...</th>
                                        
                                        {{-- 🚨 ATRIBUT TOOLTIP DITAMBAHKAN DI SINI --}}
                                        <th class="text-center" style="width: 15%">
                                            Kepastian 
                                            <i class="bi bi-question-circle text-muted ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Probabilitas pembeli akan mengambil produk kedua setelah mengambil produk pertama."></i>
                                        </th>
                                        <th class="text-center" style="width: 10%">
                                            Kekuatan 
                                            <i class="bi bi-question-circle text-muted ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Jika nilainya di atas 1, artinya kedua produk ini memiliki ikatan kuat dan bukan dibeli bersamaan karena kebetulan."></i>
                                        </th>
                                        
                                        {{-- 🚨 KOLOM BARU: TRANSLASI MANUSIA --}}
                                        <th style="width: 35%">Insight AI & Rekomendasi Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($mbaData as $mba)
                                        <tr>
                                            <td class="fw-bold text-primary">
                                                {{ $mba->product->name ?? 'Produk Dihapus' }}
                                            </td>
                                            <td class="fw-bold text-success">
                                                <i class="bi bi-plus-lg me-1 small"></i> {{ $mba->recommendedProduct->name ?? 'Produk Dihapus' }}
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info text-dark px-2 py-1" style="font-size: 0.9em;">
                                                    {{ number_format($mba->confidence * 100, 1) }}%
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ $mba->lift > 2 ? 'bg-success' : 'bg-secondary' }} px-2 py-1" style="font-size: 0.9em;">
                                                    {{ number_format($mba->lift, 2) }}x
                                                </span>
                                            </td>
                                            
                                            {{-- 🚨 LOGIKA PENERJEMAH MESIN KE BAHASA MANUSIA --}}
                                            <td>
                                                @if($mba->lift >= 3)
                                                    <div class="text-success fw-bold small"><i class="bi bi-patch-check-fill me-1"></i>Kombinasi Sempurna</div>
                                                    <div class="text-muted" style="font-size: 0.8em; line-height: 1.2;">Sangat disarankan membuat promo <strong>Paket Bundling</strong> untuk {{ $mba->product->name . ' dan ' . $mba->recommendedProduct->name ?? 'kedua produk ini' }}.</div>
                                                @elseif($mba->lift >= 1.5)
                                                    <div class="text-primary fw-bold small"><i class="bi bi-bag-plus-fill me-1"></i>Potensi Cross-Selling</div>
                                                    <div class="text-muted" style="font-size: 0.8em; line-height: 1.2;">Tawarkan {{ $mba->recommendedProduct->name ?? 'produk kedua' }} sebagai pelengkap saat pembeli melihat {{ $mba->product->name ?? 'produk pertama' }}.</div>
                                                @else
                                                    <div class="text-secondary fw-bold small"><i class="bi bi-link me-1"></i>Hubungan Wajar</div>
                                                    <div class="text-muted" style="font-size: 0.8em; line-height: 1.2;">Cukup letakkan kedua barang ini di etalase / kategori yang berdekatan.</div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
{{-- SCRIPT UNTUK MENGAKTIFKAN TOOLTIP BOOTSTRAP --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Cari semua elemen yang punya atribut data-bs-toggle="tooltip"
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        
        // Aktifkan satu per satu
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
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