@extends('layouts.modern')

@section('title', 'Katalog Produk - ' . $website->site_name)

@section('content')
<div class="container py-5">
    
    {{-- Header & Sort --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Semua Produk</h2>
            <p class="text-muted small mb-0">Temukan produk favoritmu</p>
        </div>
        
        <div class="d-flex gap-2">
            <button class="btn btn-outline-dark d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas">
                <i class="bi bi-funnel"></i> Filter
            </button>

            {{-- SORTING (Diberi ID filter-sort) --}}
            <select class="form-select w-auto" id="filter-sort">
                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Harga Terendah</option>
                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Harga Tertinggi</option>
                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Terlama</option>
            </select>
        </div>
    </div>

    <div class="row">
        {{-- SIDEBAR FILTER (DESKTOP) --}}
        <div class="col-lg-3 d-none d-lg-block">
            {{-- Karena Anda menghapus partials, masukkan kode sidebar langsung di sini --}}
            @include('storefront.products.partials.filter_sidebar_content') 
        </div>

        {{-- PRODUCT GRID AREA --}}
        <div class="col-lg-9">
            
            {{-- Search Bar (Real Time) --}}
            <div class="mb-4">
                <div class="input-group">
                    <input type="text" id="search-input" class="form-control" placeholder="Cari produk (ketik untuk mencari)..." value="{{ request('search') }}">
                    <button class="btn btn-primary"><i class="bi bi-search"></i></button>
                </div>
            </div>

            {{-- CONTAINER PRODUK (Akan di-refresh oleh AJAX) --}}
            <div id="product-grid-container" style="min-height: 300px; position: relative;">
                {{-- Loader (Hidden by default) --}}
                <div id="loading-overlay" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none justify-content-center pt-5" style="z-index: 10;">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>

                {{-- Include Awal --}}
                @include('storefront.products.partials.product_list')
            </div>

        </div>
    </div>
</div>

{{-- OFFCANVAS FILTER (MOBILE) --}}
<div class="offcanvas offcanvas-start" tabindex="-1" id="filterOffcanvas">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title fw-bold">Filter Produk</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
         @include('storefront.products.partials.filter_sidebar_content')
    </div>
</div>

{{-- JAVASCRIPT REAL TIME --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Elemen-elemen penting
        const container = document.getElementById('product-grid-container');
        const loader = document.getElementById('loading-overlay');
        const searchInput = document.getElementById('search-input');
        const sortSelect = document.getElementById('filter-sort');
        
        // Ambil semua input filter (Radio & Number)
        const filterInputs = document.querySelectorAll('input[name="category"], input[name="min_price"], input[name="max_price"]');
        
        // Timer untuk Debounce (Agar tidak request setiap ketikan)
        let debounceTimer;

        // FUNGSI UTAMA: Fetch Data
        function fetchProducts(url = null) {
            // Tampilkan Loading
            loader.classList.remove('d-none');

            // Bangun URL Query
            let currentUrl = new URL(url || "{{ route('store.products', $website->subdomain) }}");
            let params = new URLSearchParams(currentUrl.search);

            // Masukkan data Search
            if(searchInput.value) params.set('search', searchInput.value);
            else params.delete('search');

            // Masukkan data Sort
            if(sortSelect.value) params.set('sort', sortSelect.value);

            // Masukkan data Filter Sidebar
            filterInputs.forEach(input => {
                if ((input.type === 'radio' && input.checked) || (input.type === 'number' && input.value)) {
                    params.set(input.name, input.value);
                }
            });

            // Update URL Browser (biar kalau di-refresh tetap sama)
            window.history.pushState({}, '', `${currentUrl.pathname}?${params.toString()}`);

            // Request AJAX
            fetch(`${currentUrl.pathname}?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.text())
            .then(html => {
                // Ganti konten Grid
                container.innerHTML = html;
                // Masukkan ulang loader karena dia ikut tertimpa (atau pisahkan loader dari container)
                container.prepend(loader); 
                loader.classList.add('d-none');
                
                // Re-attach event listener untuk Pagination baru
                attachPaginationListeners();
            })
            .catch(err => {
                console.error('Error fetching products:', err);
                loader.classList.add('d-none');
            });
        }

        // 1. EVENT: SEARCH (Debounce 500ms)
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                fetchProducts();
            }, 500); // Tunggu 0.5 detik setelah user berhenti mengetik
        });

        // 2. EVENT: SORT CHANGE
        sortSelect.addEventListener('change', () => fetchProducts());

        // 3. EVENT: FILTER CHANGE (Kategori & Harga)
        filterInputs.forEach(input => {
            input.addEventListener('change', () => fetchProducts());
        });

        // 4. EVENT: PAGINATION CLICK
        function attachPaginationListeners() {
            const pageLinks = document.querySelectorAll('.ajax-pagination a');
            pageLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    fetchProducts(this.href); // Load halaman yang diklik
                    
                    // Scroll ke atas grid sedikit biar enak dilihat
                    container.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            });
        }

        // Jalankan listener pagination pertama kali
        attachPaginationListeners();
    });
</script>
@endsection