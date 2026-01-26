@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold">Paket Langganan</h3>
    </div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row">
    @foreach($packages as $package)
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold m-0 {{ $package->price == 0 ? 'text-success' : 'text-primary' }}">
                    {{ $package->name }}
                </h5>
            </div>
            <div class="card-body">
                <h2 class="fw-bold mb-3">
                    Rp {{ number_format($package->price, 0, ',', '.') }}
                    <small class="text-muted fs-6 fw-normal">/ bulan</small>
                </h2>
                
                <ul class="list-unstyled mb-4">
                    <li class="mb-2">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        Max <strong>{{ $package->max_products }}</strong> Produk
                    </li>
                    <li class="mb-2">
                        @if($package->can_custom_domain)
                            <i class="bi bi-check-circle-fill text-success me-2"></i> Custom Domain
                        @else
                            <i class="bi bi-x-circle text-muted me-2"></i> No Custom Domain
                        @endif
                    </li>
                    <li class="mb-2">
                        @if($package->remove_branding)
                            <i class="bi bi-check-circle-fill text-success me-2"></i> Hapus Branding
                        @else
                            <i class="bi bi-x-circle text-muted me-2"></i> Ada Branding Toko
                        @endif
                    </li>
                </ul>

                <a href="{{ route('admin.packages.edit', $package->id) }}" class="btn btn-outline-primary w-100">
                    Edit Fitur & Harga
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection