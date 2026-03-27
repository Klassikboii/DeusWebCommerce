@extends('layouts.client')

@section('title', 'Semua Produk')

@section('content')

<div class="container-fluid p-0">
    {{-- Header --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
     @if (session('error'))
         <div class="alert alert-danger alert-dismissible fade show" role="alert">
             {{ session('error') }}
             <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
         </div>
     @endif
        
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Produk</h4>
            <p class="text-muted small mb-0">Kelola katalog barang dagangan Anda.</p>
        </div>
        
        {{-- Kumpulan Tombol di Kanan --}}
        <div class="d-flex gap-2 align-items-center">
            
            <div class="badge {{ $isLimitReached ? 'bg-danger' : 'bg-success' }} p-2 d-none d-md-block">
                Slot: {{ $currentCount }} / {{ $limit }}
            </div>
            
            @if($isLimitReached)
                <button class="btn btn-secondary" disabled><i class="bi bi-lock-fill me-1"></i> Penuh</button>
            @else
                <a href="{{ route('client.products.create', $website->id) }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah</a>
            @endif

            {{-- 🚨 MENU DROPDOWN RAPI --}}
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-gear"></i> Opsi
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#importModal"><i class="bi bi-file-earmark-excel text-success me-2"></i> Import CSV</button></li>
                    
                    @if($website->accurateIntegration && $website->accurateIntegration->access_token)
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('client.products.sync_accurate', $website->id) }}" method="POST" onsubmit="return confirm('Tarik data terbaru dari Accurate? Harga dan Stok akan tertimpa.');">
                                @csrf
                                <button type="submit" class="dropdown-item"><i class="bi bi-arrow-repeat text-primary me-2"></i> Sync Accurate</button>
                            </form>
                        </li>
                        <li><a href="https://account.accurate.id/" target="_blank" class="dropdown-item"><i class="bi bi-box-arrow-up-right text-info me-2"></i> Buka Accurate</a></li>
                        <hr class="dropdown-divider">
                        <li> 
                            {{-- Tombol Tarik Gambar --}}
                                <button id="btnSyncImages" class="btn">
                                    <i class="bi bi-cloud-arrow-down-fill me-2"></i>Tarik Gambar Accurate
                                </button>
                        </li>
                    @endif
                    
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('client.products.destroy_all', $website->id) }}" method="POST" onsubmit="return confirm('Kosongkan semua produk?');">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash3-fill me-2"></i> Kosongkan Katalog</button>
                        </form>
                    </li>
                </ul>
                
            </div>
        </div>
        

        </div>
        {{-- Kotak Progress Bar (Awalnya disembunyikan) --}}
<div id="syncProgressContainer" class="card border-warning shadow-sm mb-4 d-none">
    <div class="card-body">
        <h6 class="text-warning fw-bold mb-2">
            <i class="spinner-border spinner-border-sm me-2"></i>Sedang menarik gambar... Jangan tutup halaman ini!
        </h6>
        <div class="progress" style="height: 25px;">
            <div id="syncProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-warning text-dark fw-bold" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
        </div>
        <small id="syncStatusText" class="text-muted mt-2 d-block">Menyiapkan data sinkronisasi...</small>
    </div>
</div>
    </div>

    {{-- Search Bar (Input Langsung, Tanpa Form Submit) --}}
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" id="searchInput" class="form-control border-start-0 ps-0" 
                       placeholder="Cari nama produk atau SKU..." value="{{ request('search') }}">
            </div>
        </div>
    </div>

    {{-- Product Table Container --}}
    <div class="card border-0 shadow-sm">
        {{-- Loader Overlay --}}
        <div id="tableLoader" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none justify-content-center pt-5" style="z-index: 5;">
            <div class="spinner-border text-primary" role="status"></div>
        </div>

        <div class="card-body p-0" id="productTableContainer">
            @include('client.products.partials.product_table')
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById('searchInput');
    const tableContainer = document.getElementById('productTableContainer');
    const loader = document.getElementById('tableLoader');
    let debounceTimer;

    // Fungsi Fetch Data
    function fetchProducts(url) {
        loader.classList.remove('d-none');
        
        // Jika URL tidak diberikan, gunakan URL saat ini (untuk search)
        if (!url) {
            url = new URL("{{ route('client.products.index', $website->id) }}");
            url.searchParams.set('search', searchInput.value);
        }

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            tableContainer.innerHTML = html;
            loader.classList.add('d-none');
            attachPaginationListeners(); // Pasang ulang listener pagination
        })
        .catch(err => {
            console.error('Error:', err);
            loader.classList.add('d-none');
        });
    }

    // Listener Search (Real-time Debounce)
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            fetchProducts(); 
        }, 500);
    });

    // Listener Pagination (Agar tidak reload halaman)
    function attachPaginationListeners() {
        const pageLinks = document.querySelectorAll('.ajax-pagination a');
        pageLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                fetchProducts(this.href);
            });
        });
    }

    // Jalankan listener pertama kali
    attachPaginationListeners();
});
</script>

<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('client.products.import', $website->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Produk via CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Pastikan format file Anda sesuai dengan template kami. SKU digunakan untuk mencocokkan data. Jika SKU sama, stok/harga akan diperbarui.</p>
                    
                    <div class="mb-4">
                        <a href="{{ route('client.products.template', $website->id) }}" class="btn btn-sm btn-light border">
                            <i class="bi bi-download"></i> Download Template CSV
                        </a>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pilih File CSV</label>
                        <input type="file" name="file_csv" class="form-control" accept=".csv" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Mulai Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.getElementById('btnSyncImages').addEventListener('click', async function() {
    // 1. Kunci tombol dan tampilkan Progress Bar
    this.disabled = true;
    const progressContainer = document.getElementById('syncProgressContainer');
    const progressBar = document.getElementById('syncProgressBar');
    const statusText = document.getElementById('syncStatusText');
    progressContainer.classList.remove('d-none');

    try {
        // 2. Ambil daftar ID Produk yang belum punya gambar
        statusText.innerText = "Mencari produk yang membutuhkan gambar...";
        let response = await fetch(`/manage/{{ $website->id }}/accurate/missing-images`);
        let result = await response.json();
        
        // 🚨 KEMBALIKAN KE result.data
        let productIds = result.data; 
        
        console.log("Mencoba menarik gambar untuk ID:", productIds);

        let total = productIds.length;

        if(total === 0) {
            progressBar.style.width = "100%";
            progressBar.innerText = "100%";
            statusText.innerText = "Semua produk sudah memiliki gambar / tidak ada SKU!";
            
            progressBar.classList.remove('progress-bar-animated', 'progress-bar-striped');
            progressBar.classList.add('bg-success');
            
            // 🚨 AKTIFKAN LAGI AUTO-REFRESH NYA:
            setTimeout(() => location.reload(), 2000); 
            
            this.disabled = false;
            return;
        }

        // ... (Sisa kode ke bawahnya biarkan sama, sistem cicilan / chunkSize) ...

        // 3. Sistem Cicilan: Tarik gambar per 5 produk (Batched)
        let chunkSize = 5;
        let processed = 0;

        for (let i = 0; i < total; i += chunkSize) {
            let chunk = productIds.slice(i, i + chunkSize);
            
            statusText.innerText = `Menarik gambar untuk produk ${processed + 1} hingga ${Math.min(processed + chunkSize, total)} dari ${total}...`;

            // Tembak API Controller kita untuk memproses 5 ID ini
            await fetch(`/manage/{{ $website->id }}/accurate/sync-images-batch`, {
                    method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ ids: chunk })
            });

            // Update Progress Bar
            processed += chunk.length;
            let percent = Math.round((processed / total) * 100);
            progressBar.style.width = percent + "%";
            progressBar.innerText = percent + "%";
        }

        // 4. Selesai
        statusText.innerText = "Sinkronisasi Gambar Selesai!";
        progressBar.classList.remove('progress-bar-animated');
        progressBar.classList.add('bg-success');
        
        // Refresh halaman setelah 2 detik untuk melihat hasilnya
        setTimeout(() => location.reload(), 2000);

    } catch (error) {
        statusText.innerHTML = "<span class='text-danger'>Terjadi kesalahan saat sinkronisasi. Silakan coba lagi.</span>";
        console.error(error);
        this.disabled = false;
    }
});
</script>
@endsection