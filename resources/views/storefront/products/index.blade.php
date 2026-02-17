@extends('layouts.modern')

@section('title', 'Katalog Produk - ' . $website->site_name)

@section('content')
<div class="container py-5">
    
    {{-- Header & Sort --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Semua Produk</h2>
            <p class="text-muted small mb-0">Menampilkan {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} dari {{ $products->total() }} produk</p>
        </div>
        
        <div class="d-flex gap-2">
            {{-- Tombol Filter Mobile --}}
            <button class="btn btn-outline-dark d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas">
                <i class="bi bi-funnel"></i> Filter
            </button>

            {{-- Sort Dropdown --}}
            <select class="form-select w-auto" onchange="window.location.href=this.value">
                <option value="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" {{ request('sort') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_asc']) }}" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Harga Terendah</option>
                <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_desc']) }}" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Harga Tertinggi</option>
                <option value="{{ request()->fullUrlWithQuery(['sort' => 'oldest']) }}" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Terlama</option>
            </select>
        </div>
    </div>

    <div class="row">
        {{-- SIDEBAR FILTER (DESKTOP) --}}
        <div class="col-lg-3 d-none d-lg-block">
            @include('storefront.products.partials.filter_sidebar')
        </div>

        {{-- PRODUCT GRID --}}
        <div class="col-lg-9">
            
            {{-- Search Bar --}}
            <form action="{{ route('store.products', $website->subdomain) }}" method="GET" class="mb-4">
                {{-- Keep existing filters hidden --}}
                @foreach(request()->except(['search', 'page']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari produk..." value="{{ request('search') }}">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                </div>
            </form>

            @if($products->count() > 0)
                <div class="row g-4">
                    @foreach($products as $product)
                        <div class="col-6 col-md-4">
                            <div class="card h-100 border-0 shadow-sm product-card">
                                <div class="position-relative overflow-hidden rounded-top">
                                    <a href="{{ route('store.product', ['subdomain' => $website->subdomain, 'slug' => $product->slug]) }}">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" class="card-img-top object-fit-cover" style="aspect-ratio: 1/1;" alt="{{ $product->name }}">
                                        @else
                                            <div class="bg-light card-img-top d-flex align-items-center justify-content-center" style="aspect-ratio: 1/1;">
                                                <i class="bi bi-image text-muted fs-1"></i>
                                            </div>
                                        @endif
                                    </a>
                                    @if($product->stock <= 0)
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge bg-danger">Habis</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body p-3 text-center">
                                    @if($product->category)
                                        <small class="text-muted d-block mb-1">{{ $product->category->name }}</small>
                                    @endif
                                    <h6 class="card-title text-truncate mb-2">
                                        <a href="{{ route('store.product', ['subdomain' => $website->subdomain, 'slug' => $product->slug]) }}" class="text-decoration-none text-dark stretched-link">
                                            {{ $product->name }}
                                        </a>
                                    </h6>
                                    <p class="text-primary fw-bold mb-0">
                                        Rp {{ number_format($product->price, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- PAGINATION --}}
                <div class="mt-5 d-flex justify-content-center">
                    {{ $products->links() }} 
                </div>

            @else
                <div class="text-center py-5">
                    <img src="https://illustrations.popsy.co/gray/surr-searching.svg" alt="Empty" style="width: 200px; opacity: 0.5;">
                    <h5 class="mt-3 fw-bold">Produk Tidak Ditemukan</h5>
                    <p class="text-muted">Coba kata kunci lain atau reset filter.</p>
                    <a href="{{ route('store.products', $website->subdomain) }}" class="btn btn-outline-primary mt-2">Reset Filter</a>
                </div>
            @endif
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
        @include('storefront.products.partials.filter_sidebar')
    </div>
</div>
@endsection