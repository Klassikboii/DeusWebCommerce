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
        {{-- Letakkan di bawah @if(session('success')) --}}
    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
            <strong><i class="bi bi-exclamation-triangle-fill me-2"></i> Perhatian:</strong> {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                <div style="font-size: 1.0rem;">Slot Produk Aktif: {{ $activeCount }} / {{ $limit }} </div>
                <hr style="margin-top: 3px; margin-bottom: 3px; height: 5px; background-color: white; border:none">
                <div style="font-size: 0.75rem;">Total Produk: {{ $currentCount }}</div>
            </div>
                        
            <a href="{{ route('client.products.create', $website->id) }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Tambah
            </a>

            {{-- 🚨 MENU DROPDOWN RAPI --}}
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-gear"></i> Opsi
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#importModal"><i class="bi bi-file-earmark-excel text-success me-2"></i> Import CSV</button></li>
                    
                    @if($website->accurateIntegration && $website->accurateIntegration->access_token && $website->accurateIntegration->accurate_database_id)
                        <li><hr class="dropdown-divider"></li>
                         <li><a href="https://account.accurate.id/" target="_blank" class="dropdown-item"><i class="bi bi-box-arrow-up-right text-info me-2"></i> Buka Accurate</a></li>

                        <li>
                            <form action="{{ route('client.products.sync_accurate', $website->id) }}" method="POST" onsubmit="return confirm('Tarik data terbaru dari Accurate? Harga dan Stok akan tertimpa.');">
                                @csrf
                                <button type="submit" class="dropdown-item"><i class="bi bi-arrow-repeat text-primary me-2"></i> Sync Accurate</button>
                            </form>
                        </li>
                        
                        @if ($website->products)
                            <hr class="dropdown-divider">
                             <li> 
                            {{-- Tombol Tarik Gambar --}}
                                <button id="btnSyncImages" class="btn">
                                    <i class="bi bi-cloud-arrow-down-fill me-2"></i>Tarik Gambar Accurate
                                </button>
                             </li>
                        @endif
                       
                    @else
                    <li><hr class="dropdown-divider"></li>
                     <li><a href="https://account.accurate.id/" target="_blank" class="dropdown-item"><i class="bi bi-box-arrow-up-right text-info me-2"></i> Buka Accurate</a></li>
                        <li>
                            <form action="{{ route('client.products.sync_accurate', $website->id) }}" method="POST" onsubmit="return confirm('Tarik data terbaru dari Accurate? Harga dan Stok akan tertimpa.');">
                                @csrf
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#syncAccurateModal">
                                    <i class="fas fa-sync"></i> Sync ke Accurate
                                </button>
                            </form>
                        </li>
                        <hr class="dropdown-divider">
                        <li> 
                            {{-- Tombol Tarik Gambar --}}
                                <button id="btnSyncImages" class="btn" disabled >
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

    <form id="filterForm" action="{{ url()->current() }}" method="GET" class="d-flex flex-wrap gap-2 mb-4">
    
    <input type="text" name="search" id="searchInput" class="form-control" placeholder="Cari nama atau SKU..." value="{{ request('search') }}" style="max-width: 250px;">

    <select name="status" class="form-select w-auto filter-select">
        <option value="">Semua Status</option>
        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Aktif</option>
        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Non-Aktif</option>
    </select>

    <select name="stock_status" class="form-select w-auto filter-select">
        <option value="">Semua Stok</option>
        <option value="Safe" {{ request('stock_status') == 'Safe' ? 'selected' : '' }}>Aman</option>
        <option value="Critical" {{ request('stock_status') == 'Critical' ? 'selected' : '' }}>Kritis</option>
        <option value="Overstock" {{ request('stock_status') == 'Overstock' ? 'selected' : '' }}>Overstock</option>
    </select>

    <select name="image_status" class="form-select w-auto filter-select">
        <option value="">Semua Gambar</option>
        <option value="has_image" {{ request('image_status') == 'has_image' ? 'selected' : '' }}>Sudah Ada Gambar</option>
        <option value="missing" {{ request('image_status') == 'missing' ? 'selected' : '' }}>Belum Ada Gambar</option>
    </select>

    <select name="sort" class="form-select w-auto filter-select">
        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Terbaru Ditambahkan</option>
        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Paling Lama</option>
        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Harga (Termurah)</option>
        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Harga (Termahal)</option>
        <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Nama (A - Z)</option>
        <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Nama (Z - A)</option>
        <option value="status_active_first" {{ request('sort') == 'status_active_first' ? 'selected' : '' }}>Status (Aktif di atas)</option>
        <option value="status_inactive_first" {{ request('sort') == 'status_inactive_first' ? 'selected' : '' }}>Status (Non-Aktif di atas)</option>
    </select>
</form>
    @if(empty($website->accurateIntegration->accurate_database_id))
        <div class="alert alert-warning d-flex align-items-center">
            <i class="fas fa-exclamation-triangle me-2"></i> 
            Pilih dan simpan Database Accurate Anda terlebih dahulu untuk mengaktifkan fitur Sinkronisasi.
        </div>
    @endif
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
            // 1. CARI PRODUK BOTAK
            let response = await fetch(`/manage/{{ $website->id }}/accurate/missing-images`);
            let result = await response.json();
            
            let productIds = result.data; 
            let total = productIds.length;

            if(total === 0) {
                progressBar.style.width = "100%";
                progressBar.innerText = "100%";
                statusText.innerHTML = "<b class='text-success'>Semua produk sudah memiliki gambar!</b>";
                
                progressBar.classList.remove('progress-bar-animated', 'progress-bar-striped');
                progressBar.classList.add('bg-success');
                
                // Refresh halaman otomatis jika sudah lengkap
                setTimeout(() => location.reload(), 1500); 
                
                this.disabled = false;
                return;
            }

            let chunkSize = 5; 
            let processed = 0;

            // 2. PROSES CICILAN UNDUH
            for (let i = 0; i < total; i += chunkSize) {
                let chunk = productIds.slice(i, i + chunkSize);
                
                statusText.innerText = `Menarik gambar untuk produk ${processed + 1} hingga ${Math.min(processed + chunk.length, total)} dari ${total}...`;

                let syncResponse = await fetch(`/manage/{{ $website->id }}/accurate/sync-images-batch`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ ids: chunk })
                });

                let syncResult = await syncResponse.json();

                // Airbag jika terjadi error fatal dari server
                if (syncResult.status === 'fatal_error') {
                    statusText.innerHTML = `<b class="text-danger">Gagal:</b> <span class="text-danger">${syncResult.pesan_error}</span>`;
                    progressBar.classList.add('bg-danger');
                    this.disabled = false;
                    return; 
                }

                // Update Progress Bar secara normal (KOTAK KUNING DETEKTIF DIHAPUS)
                processed += chunk.length;
                let percent = Math.round((processed / total) * 100);
                progressBar.style.width = percent + "%";
                progressBar.innerText = percent + "%";
            }

            // 3. SEMUA SELESAI
            statusText.innerHTML = `<b class="text-success">Selesai!</b> Menyegarkan halaman...`;
            progressBar.classList.remove('progress-bar-animated', 'progress-bar-striped');
            progressBar.classList.add('bg-success');
            
            // 🚨 INI DIA TOMBOL REFRESH OTOMATISNYA! 🚨
            setTimeout(() => location.reload(), 1500);

        } catch (error) {
            console.error("Terjadi kesalahan sistem:", error);
            statusText.innerHTML = `<b class="text-danger">Terjadi kesalahan pada sistem Javascript.</b>`;
            this.disabled = false;
        }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const searchInput = document.getElementById('searchInput');
    const filterSelects = document.querySelectorAll('.filter-select');
    
    // GANTI ID INI jika container tabel Anda memiliki ID yang berbeda
    const tableContainer = document.getElementById('productTableContainer'); 

    function fetchProducts(url) {
        // Opsional: Tambahkan efek loading di sini jika mau
        tableContainer.style.opacity = '0.5';

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            tableContainer.innerHTML = html;
            tableContainer.style.opacity = '1'; // Kembalikan opacity
        })
        .catch(error => {
            console.error('Error fetching products:', error);
            tableContainer.style.opacity = '1';
        });
    }

    function updateFilters() {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData).toString();
        const url = `${filterForm.action}?${params}`;
        
        // Update URL di browser tanpa reload (agar jika di-refresh, filter tidak hilang)
        window.history.pushState({}, '', url);
        
        fetchProducts(url);
    }

    let typingTimer;
    searchInput.addEventListener('input', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(updateFilters, 500);
    });

    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            updateFilters();
        });
    });
});
</script>
@endsection