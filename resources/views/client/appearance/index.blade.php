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

    {{-- 1. PERSIAPAN DATA: Tarik data section untuk dijadikan pilihan Dropdown --}}
    @php
        $sections = $website->sections ?? [];
        $sectionOptions = [];
        
        foreach($sections as $sec) {
            // Abaikan section yang sedang di-hidden oleh user
            if(($sec['visible'] ?? true) === false) continue; 
            
            $id = $sec['id'];
            $type = $sec['type'];
            // Ambil judul dari data, jika kosong pakai nama tipenya
            $title = $sec['data']['title'] ?? ucfirst(str_replace('-', ' ', $type));
            
            // Format array: ['#hero-1' => 'Banner Utama (hero-1)']
            $sectionOptions['#' . $id] = $title . ' (' . $id . ')';
        }
    @endphp

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="row fw-bold text-muted small text-uppercase px-2">
                <div class="col-3">Label Menu</div>
                <div class="col-3">Arahkan Ke (Tipe)</div>
                <div class="col-5">Target / URL</div>
                <div class="col-1 text-end"></div>
            </div>
        </div>
        
        <div class="card-body p-0">
            <form action="{{ route('client.appearance.update', $website->id) }}" method="POST" id="menuForm">
                @csrf
                @method('PUT')
                
                <div id="menu-container">
                    @foreach($menus as $index => $menu)
                        @php
                            $url = $menu['url'] ?? '';
                            
                            // Deteksi Otomatis: Ini link apa?
                            $type = 'custom';
                            if(str_starts_with($url, '#')) $type = 'section';
                            elseif($url === '/blog') $type = 'page';
                        @endphp
                        
                        <div class="menu-item border-bottom p-3 bg-white">
                            <div class="row align-items-center">
                                
                                {{-- A. Input Nama Menu --}}
                                <div class="col-3">
                                    <input type="text" name="menus[{{ $index }}][label]" class="form-control form-control-sm" value="{{ $menu['label'] }}" placeholder="Nama Menu" required>
                                </div>
                                
                                {{-- B. Dropdown Penentu Tipe --}}
                                <div class="col-3">
                                    <select class="form-select form-select-sm type-select">
                                        <option value="section" {{ $type === 'section' ? 'selected' : '' }}>Bagian Halaman</option>
                                        <option value="page" {{ $type === 'page' ? 'selected' : '' }}>Halaman Blog</option>
                                        <option value="custom" {{ $type === 'custom' ? 'selected' : '' }}>Link Luar / Kustom</option>
                                    </select>
                                </div>
                                
                                {{-- C. Target Dinamis --}}
                                <div class="col-5 position-relative">
                                    {{-- HIDDEN INPUT: Inilah yang sebenarnya dikirim ke Controller --}}
                                    <input type="hidden" name="menus[{{ $index }}][url]" class="real-url-input" value="{{ $url }}">

                                    {{-- Opsi 1: Dropdown Section --}}
                                    <select class="form-select form-select-sm target-input val-section" style="display: {{ $type === 'section' ? 'block' : 'none' }}">
                                        @foreach($sectionOptions as $val => $text)
                                            <option value="{{ $val }}" {{ $url === $val ? 'selected' : '' }}>{{ $text }}</option>
                                        @endforeach
                                    </select>

                                    {{-- Opsi 2: Dropdown Halaman (Blog) --}}
                                    <select class="form-select form-select-sm target-input val-page" style="display: {{ $type === 'page' ? 'block' : 'none' }}">
                                        <option value="/blog" selected>Halaman Blog</option>
                                    </select>

                                    {{-- Opsi 3: Teks Bebas (WA/Shopee) --}}
                                    <input type="text" class="form-control form-control-sm target-input val-custom" placeholder="https://..." value="{{ $type === 'custom' ? $url : '' }}" style="display: {{ $type === 'custom' ? 'block' : 'none' }}">
                                </div>
                                
                                {{-- D. Tombol Hapus --}}
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
                    
                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('menu-container');
        const addBtn = document.getElementById('add-row-btn');

        // Tarik data sections dari Blade untuk di-inject ke baris baru
        const availableSections = @json($sectionOptions);
        let sectionOptionsHtml = '';
        for (const [val, text] of Object.entries(availableSections)) {
            sectionOptionsHtml += `<option value="${val}">${text}</option>`;
        }
        
        // Ambil default id dari option pertama (jika ada) untuk value awal hidden input
        const firstSectionVal = Object.keys(availableSections).length > 0 ? Object.keys(availableSections)[0] : '#';

        // 1. Fungsi Tambah Baris Baru
        addBtn.addEventListener('click', function() {
            const index = Date.now(); // Gunakan timestamp agar selalu unik
            
            const template = `
                <div class="menu-item border-bottom p-3 bg-white fade-in">
                    <div class="row align-items-center">
                        <div class="col-3">
                            <input type="text" name="menus[${index}][label]" class="form-control form-control-sm" placeholder="Nama Menu" required>
                        </div>
                        <div class="col-3">
                            <select class="form-select form-select-sm type-select">
                                <option value="section" selected>Bagian Halaman</option>
                                <option value="page">Halaman Blog</option>
                                <option value="custom">Link Luar / Kustom</option>
                            </select>
                        </div>
                        <div class="col-5 position-relative">
                            <input type="hidden" name="menus[${index}][url]" class="real-url-input" value="${firstSectionVal}">

                            <select class="form-select form-select-sm target-input val-section" style="display: block">
                                ${sectionOptionsHtml}
                            </select>

                            <select class="form-select form-select-sm target-input val-page" style="display: none">
                                <option value="/blog" selected>Halaman Blog</option>
                            </select>

                            <input type="text" class="form-control form-control-sm target-input val-custom" placeholder="https://..." style="display: none">
                        </div>
                        <div class="col-1 text-end">
                            <button type="button" class="btn btn-sm text-danger delete-row"><i class="bi bi-x-lg"></i></button>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);
        });

        // 2. Fungsi Hapus Baris
        container.addEventListener('click', function(e) {
            if (e.target.closest('.delete-row')) {
                if (container.children.length > 1) {
                    e.target.closest('.menu-item').remove();
                } else {
                    alert("Minimal harus menyisakan 1 menu.");
                }
            }
        });

        // 3. FUNGSI CERDAS: Handle pergantian Tipe dan update Hidden Input
        container.addEventListener('input', function(e) {
            const row = e.target.closest('.menu-item');
            if(!row) return;

            const hiddenInput = row.querySelector('.real-url-input');

            // Jika user mengganti Tipe Dropdown (Section / Page / Custom)
            if (e.target.classList.contains('type-select')) {
                const type = e.target.value;
                
                // Sembunyikan semua target input
                row.querySelectorAll('.target-input').forEach(el => el.style.display = 'none');
                
                // Tampilkan target input yang sesuai
                const activeInput = row.querySelector('.val-' + type);
                if(activeInput) activeInput.style.display = 'block';

                // Set nilai hidden input dari input yang baru aktif
                hiddenInput.value = activeInput.value;
            }

            // Jika user mengetik/memilih di Target Input yang sedang aktif
            if (e.target.classList.contains('target-input')) {
                hiddenInput.value = e.target.value;
            }
        });
    });
</script>

<style>
    .fade-in { animation: fadeIn 0.3s ease-in; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection