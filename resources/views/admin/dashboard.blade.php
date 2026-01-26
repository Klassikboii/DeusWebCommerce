@extends('layouts.admin') @section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold text-primary">Super Admin Panel</h2>
            <p class="text-muted">Selamat datang, Bos! Ini area kendali pusat.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body">
                    <h5 class="opacity-75">Total Klien</h5>
                    <h1 class="display-4 fw-bold mb-0">{{ $totalUsers }}</h1>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white h-100">
                <div class="card-body">
                    <h5 class="opacity-75">Total Website Aktif</h5>
                    <h1 class="display-4 fw-bold mb-0">{{ $totalWebsites }}</h1>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning text-dark h-100">
                <div class="card-body">
                    <h5 class="opacity-75">Pendapatan</h5>
                    <h1 class="display-4 fw-bold mb-0">Rp 0</h1>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection