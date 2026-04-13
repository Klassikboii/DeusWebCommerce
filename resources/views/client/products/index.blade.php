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
       {{-- Kumpulan Tombol di Kanan --}}
        <div class="d-flex gap-2 align-items-center">
            
            <div class="badge {{ $isLimitReached ? 'bg-danger' : 'bg-success' }} p-2 d-none d-md-block">
                <div style="font-size: 1.0rem;">Slot Produk Aktif: {{ $activeCount }} / {{ $limit }} </div>
                <hr style="margin-top: 3px; margin-bottom: 3px; height: 5px; background-color: white; border:none">
                <div style="font-size: 0.75rem;">Total Produk: {{ $currentCount }}</div>
            </div>
                        
            <a href="{{ route('client.products.create', $website->id) }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Tambah
            </a>

            {{-- 🚨 1. MENU KHUSUS INTEGRASI ACCURATE 🚨 --}}
            @if($website->accurateIntegration && $website->accurateIntegration->access_token && $website->accurateIntegration->accurate_database_id)
            <div class="dropdown">
                <button class="btn btn-success shadow-sm dropdown-toggle fw-bold" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-arrow-left-right me-1"></i> Sinkronisasi Accurate
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow" style="width: 320px;"> {{-- Diperlebar agar teks muat --}}
                    
                    {{-- GRUP 1: PULL (TARIK) --}}
                    <li>
                        <h6 class="dropdown-header text-success fw-bold">
                            <i class="bi bi-cloud-download me-1"></i> Dari Accurate ke Web (Tarik Data)
                        </h6>
                    </li>
                    <li>
                        <form action="{{ route('client.products.sync_accurate', $website->id) }}" method="POST" onsubmit="return confirm('Tarik data terbaru dari Accurate?\n\nHarga, Stok, dan Status di Web akan diperbarui mengikuti Accurate.');">
                            @csrf
                            <button type="submit" class="dropdown-item py-2">
                                <span class="d-block fw-bold text-dark">Tarik Katalog Produk</span>
                                <small class="text-muted text-wrap">Perbarui stok dan harga web agar sama dengan Accurate.</small>
                            </button>
                        </form>
                    </li>
                    @if ($currentCount > 0)
                    <li>
                        <button id="btnSyncImages" class="dropdown-item py-2">
                            <span class="d-block fw-bold text-dark">Tarik Gambar Baru</span>
                            <small class="text-muted text-wrap">Cari dan unduh gambar untuk produk yang belum memiliki foto.</small>
                        </button>
                    </li>
                    @endif

                    <li><hr class="dropdown-divider"></li>

                    {{-- GRUP 2: PUSH (KIRIM) --}}
                    <li>
                        <h6 class="dropdown-header text-primary fw-bold">
                            <i class="bi bi-cloud-upload me-1"></i> Dari Web ke Accurate (Kirim Data)
                        </h6>
                    </li>
                    @if ($currentCount > 0)
                    <li>
                        <button type="button" class="dropdown-item py-2" data-bs-toggle="modal" data-bs-target="#modalBulkSyncAccurate">
                            <span class="d-block fw-bold text-dark">Kirim Semua Produk</span>
                            <small class="text-muted text-wrap">Ekspor/buat produk yang ada di web ke sistem Accurate.</small>
                        </button>
                    </li>
                    @else
                    <li><span class="dropdown-item py-2 text-muted"><small>Web masih kosong, tidak ada yang bisa dikirim.</small></span></li>
                    @endif

                    <li><hr class="dropdown-divider"></li>

                    {{-- GRUP 3: LINK EKSTERNAL --}}
                    <li>
                        <a href="https://account.accurate.id/" target="_blank" class="dropdown-item text-info py-2">
                            <i class="bi bi-box-arrow-up-right me-2"></i> Buka Dashboard Accurate
                        </a>
                    </li>
                </ul>
            </div>
            @endif

            {{-- 🚨 2. MENU OPSI LAINNYA (LOKAL) 🚨 --}}
            <div class="dropdown">
                <button class="btn btn-outline-secondary shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" title="Opsi Web">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><h6 class="dropdown-header">Manajemen Lokal</h6></li>
                    
                    <li>
                        <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="bi bi-file-earmark-excel text-success me-2"></i> Import CSV
                        </button>
                    </li>
                    
                    @if(empty($website->accurateIntegration->accurate_database_id))
                    <li>
                        <a href="https://account.accurate.id/" target="_blank" class="dropdown-item text-info">
                            <i class="bi bi-box-arrow-up-right me-2"></i> Buka Accurate
                        </a>
                    </li>
                    @endif

                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('client.products.destroy_all', $website->id) }}" method="POST" onsubmit="return confirm('🚨 PERINGATAN: Anda yakin ingin mengosongkan SELURUH KATALOG PRODUK di Web ini?');">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-trash3-fill me-2"></i> Kosongkan Katalog
                            </button>
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

    {{-- 🚨 LOGIKA EMPTY STATE DIMULAI DI SINI 🚨 --}}
    @if($currentCount == 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body text-center py-5">
                {{-- Icon Dus Kosong --}}
                <div class="mb-4">
                    <i class="bi bi-box-seam text-secondary opacity-50" style="font-size: 6rem;"></i>
                </div>
                
                {{-- Jika Sudah Konek Accurate tapi Belum Ada Produk --}}
                @if($website->accurateIntegration && $website->accurateIntegration->access_token && $website->accurateIntegration->accurate_database_id)
                    <h4 class="fw-bold text-dark">Katalog Produk Masih Kosong</h4>
                    <p class="text-muted mx-auto mb-4" style="max-width: 500px;">
                        Toko Anda sudah terhubung dengan sistem Accurate Online. Silakan tarik data barang Anda untuk mulai memajangnya di toko.
                    </p>
                    <form action="{{ route('client.products.sync_accurate', $website->id) }}" method="POST" onsubmit="return confirm('Tarik data terbaru dari Accurate sekarang?');">
                        @csrf
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-bold shadow-sm">
                            <i class="bi bi-arrow-repeat me-2"></i> Tarik Data dari Accurate
                        </button>
                    </form>
                
                {{-- Jika Belum Konek Accurate --}}
                @else
                    <h4 class="fw-bold text-dark">Belum Ada Produk di Toko Anda</h4>
                    <p class="text-muted mx-auto mb-4" style="max-width: 500px;">
                        Tambahkan produk pertama Anda secara manual, atau hubungkan dengan sistem Accurate Online untuk mengimpor data barang secara otomatis.
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('client.products.create', $website->id) }}" class="btn btn-primary px-4 py-2 fw-bold shadow-sm">
                            <i class="bi bi-plus-lg me-2"></i> Tambah Manual
                        </a>
                        <a href="{{ route('client.settings.index', $website->id) }}" class="btn btn-outline-success px-4 py-2 fw-bold shadow-sm">
                            <i class="bi bi-link-45deg me-2"></i> Hubungkan Accurate
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @else
        {{-- TAMPILAN NORMAL (FILTER & TABEL) JIKA PRODUK > 0 --}}
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
            <div class="alert alert-warning d-flex align-items-center shadow-sm">
                <i class="fas fa-exclamation-triangle me-2"></i> 
                Pilih dan simpan Database Accurate Anda di Pengaturan terlebih dahulu untuk mengaktifkan fitur Sinkronisasi.
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
    @endif
</div>

{{-- SCRIPT JAVASCRIPT BAWAAN TETAP SAMA --}}
<script>
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById('searchInput');
    const tableContainer = document.getElementById('productTableContainer');
    const loader = document.getElementById('tableLoader');
    let debounceTimer;

    if(searchInput && tableContainer) {
        function fetchProducts(url) {
            loader.classList.remove('d-none');
            
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
                attachPaginationListeners();
            })
            .catch(err => {
                console.error('Error:', err);
                loader.classList.add('d-none');
            });
        }

        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                fetchProducts(); 
            }, 500);
        });

        function attachPaginationListeners() {
            const pageLinks = document.querySelectorAll('.ajax-pagination a');
            pageLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    fetchProducts(this.href);
                });
            });
        }

        attachPaginationListeners();
    }
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
const btnSyncImages = document.getElementById('btnSyncImages');
if (btnSyncImages) {
    btnSyncImages.addEventListener('click', async function() {
        this.disabled = true;
        const progressContainer = document.getElementById('syncProgressContainer');
        const progressBar = document.getElementById('syncProgressBar');
        const statusText = document.getElementById('syncStatusText');
        progressContainer.classList.remove('d-none');

       try {
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
                    
                    setTimeout(() => location.reload(), 1500); 
                    
                    this.disabled = false;
                    return;
                }

                let chunkSize = 5; 
                let processed = 0;

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

                    if (syncResult.status === 'fatal_error') {
                        statusText.innerHTML = `<b class="text-danger">Gagal:</b> <span class="text-danger">${syncResult.pesan_error}</span>`;
                        progressBar.classList.add('bg-danger');
                        this.disabled = false;
                        return; 
                    }

                    processed += chunk.length;
                    let percent = Math.round((processed / total) * 100);
                    progressBar.style.width = percent + "%";
                    progressBar.innerText = percent + "%";
                }

                statusText.innerHTML = `<b class="text-success">Selesai!</b> Menyegarkan halaman...`;
                progressBar.classList.remove('progress-bar-animated', 'progress-bar-striped');
                progressBar.classList.add('bg-success');
                
                setTimeout(() => location.reload(), 1500);

            } catch (error) {
                console.error("Terjadi kesalahan sistem:", error);
                statusText.innerHTML = `<b class="text-danger">Terjadi kesalahan pada sistem Javascript.</b>`;
                this.disabled = false;
            }
    });
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const searchInput = document.getElementById('searchInput');
    const filterSelects = document.querySelectorAll('.filter-select');
    const tableContainer = document.getElementById('productTableContainer'); 

    if(filterForm && tableContainer) {
        function fetchProducts(url) {
            tableContainer.style.opacity = '0.5';

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                tableContainer.innerHTML = html;
                tableContainer.style.opacity = '1';
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
    }
});
</script>

{{-- Modal Kirim Semua Produk ke Accurate --}}
<div class="modal fade" id="modalBulkSyncAccurate" tabindex="-1" aria-labelledby="modalBulkSyncAccurateLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('client.products.bulk_sync_accurate', $website->id) }}" method="POST" id="formBulkSyncAccurate">
            @csrf
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="modalBulkSyncAccurateLabel">Kirim Semua Produk ke Accurate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    {{-- Pesan diubah agar klien paham SEMUA produk akan terkirim --}}
                    <p>Sistem akan memindai dan mengirim <strong>seluruh produk</strong> yang ada di katalog Web Anda ke sistem Accurate Online.</p>

                    <div class="alert alert-warning border-warning text-dark mb-0">
                        <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle me-1"></i> Jika SKU produk sudah ada di Accurate:</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="conflict_resolution" id="conflictSkip" value="skip" checked>
                            <label class="form-check-label" for="conflictSkip">
                                <strong>Lewati (Skip)</strong> - Biarkan data di Accurate apa adanya.
                            </label>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="radio" name="conflict_resolution" id="conflictOverwrite" value="overwrite">
                            <label class="form-check-label" for="conflictOverwrite">
                                <strong>Timpa Harga</strong> - Perbarui harga di Accurate agar sama dengan harga Web.
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitBulkSync">
                        <i class="bi bi-send me-1"></i> Mulai Pengiriman
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
{{-- Script Loading Tombol Kirim ke Accurate --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const formSync = document.getElementById('formBulkSyncAccurate');

        if(formSync) {
            formSync.addEventListener('submit', function() {
                let btn = document.getElementById('btnSubmitBulkSync');
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Memproses (Jangan ditutup)...';
                btn.disabled = true;
            });
        }
    });
</script>
@endsection