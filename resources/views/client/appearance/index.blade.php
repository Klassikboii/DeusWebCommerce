@extends('layouts.client')

@section('title', 'Atur Menu Navigasi')

@section('content')
<div class="container-fluid p-0" style="max-width: 800px;">
    <div class="mb-4">
        <h4 class="fw-bold mb-1">Menu & Navigasi</h4>
        <p class="text-muted m-0">Atur link yang muncul di bagian atas (Header) toko Anda.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="row fw-bold text-muted small text-uppercase px-2">
                <div class="col-4">Label Menu</div>
                <div class="col-7">Tujuan Link (URL)</div>
                <div class="col-1 text-end"></div>
            </div>
        </div>
        
        <div class="card-body p-0">
            <form action="{{ route('client.appearance.update', $website->id) }}" method="POST" id="menuForm">
                @csrf
                @method('PUT')
                
                <div id="menu-container">
                    @foreach($menus as $index => $menu)
                    <div class="menu-item border-bottom p-3 bg-white">
                        <div class="row align-items-center">
                            <div class="col-4">
                                <input type="text" name="menus[{{ $index }}][label]" class="form-control" value="{{ $menu['label'] }}" placeholder="Nama Menu" required>
                            </div>
                            <div class="col-7">
                                <input type="text" name="menus[{{ $index }}][url]" class="form-control font-monospace small text-muted" value="{{ $menu['url'] }}" placeholder="https://..." required>
                            </div>
                            <div class="col-1 text-end">
                                <button type="button" class="btn btn-sm text-danger delete-row"><i class="bi bi-x-lg"></i></button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="p-3 bg-light d-flex justify-content-between align-items-center">
                    <button type="button" id="add-row-btn" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Menu
                    </button>
                    
                    <button type="submit" class="btn btn-primary px-4">
                        Simpan Menu
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-4 alert alert-info border-0 small">
        <h6 class="fw-bold"><i class="bi bi-info-circle me-1"></i> Tips Pengisian URL:</h6>
        <ul class="mb-0 ps-3">
            <li>Gunakan <code>#shop</code> untuk scroll otomatis ke bagian Produk.</li>
            <li>Gunakan <code>/blog</code> untuk membuka halaman Blog.</li>
            <li>Gunakan <code>https://wa.me/628xxx</code> untuk link ke WhatsApp eksternal.</li>
        </ul>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('menu-container');
        const addBtn = document.getElementById('add-row-btn');

        // 1. Fungsi Tambah Baris Baru
        addBtn.addEventListener('click', function() {
            // Hitung index berdasarkan jumlah baris saat ini agar unique
            const index = container.children.length; 
            
            const template = `
                <div class="menu-item border-bottom p-3 bg-white fade-in">
                    <div class="row align-items-center">
                        <div class="col-4">
                            <input type="text" name="menus[${index}][label]" class="form-control" placeholder="Menu Baru" required>
                        </div>
                        <div class="col-7">
                            <input type="text" name="menus[${index}][url]" class="form-control font-monospace small text-muted" placeholder="#" required>
                        </div>
                        <div class="col-1 text-end">
                            <button type="button" class="btn btn-sm text-danger delete-row"><i class="bi bi-x-lg"></i></button>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);
        });

        // 2. Fungsi Hapus Baris (Event Delegation)
        container.addEventListener('click', function(e) {
            if (e.target.closest('.delete-row')) {
                // Jangan biarkan kosong sama sekali (opsional)
                if (container.children.length > 1) {
                    e.target.closest('.menu-item').remove();
                } else {
                    alert("Minimal harus menyisakan 1 menu.");
                }
            }
        });
    });
</script>

<style>
    .fade-in { animation: fadeIn 0.3s ease-in; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection