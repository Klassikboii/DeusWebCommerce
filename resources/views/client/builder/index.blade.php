@extends('layouts.client')

@section('title', 'Editor Website')

@section('content')
<div class="container-fluid p-0 h-100">
    
    <div class="row g-0 h-100">
        <div class="col-md-3 bg-white border-end d-flex flex-column" style="height: calc(100vh - 65px);">
    
            <div class="p-3 border-bottom">
                <h5 class="fw-bold m-0">Website Builder</h5>
                <small class="text-muted">Kustomisasi tampilan toko.</small>
            </div>

            <ul class="nav nav-tabs nav-fill" id="builderTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active small py-3" data-bs-toggle="tab" data-bs-target="#tab-style" type="button">Style</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link small py-3" data-bs-toggle="tab" data-bs-target="#tab-content" type="button">Konten</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link small py-3" data-bs-toggle="tab" data-bs-target="#tab-assets" type="button">Aset</button>
                </li>
            </ul>

            <form action="{{ route('client.builder.update', $website->id) }}" method="POST" enctype="multipart/form-data" class="flex-grow-1 overflow-auto" id="builderForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="sections_json" id="sectionsJsonInput">

                <div class="tab-content p-4">
                    
                    <div class="tab-pane fade show active" id="tab-style">
                        
                        <h6 class="fw-bold mb-3">Warna</h6>
                        <div class="mb-3">
                            <label class="form-label small">Primary Color</label>
                            <input type="color" name="primary_color" 
                                   class="form-control form-control-color w-100 live-update-style" 
                                   data-style-var="--primary-color"
                                   value="{{ $website->primary_color }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Secondary Color</label>
                            <input type="color" name="secondary_color" 
                                   class="form-control form-control-color w-100 live-update-style" 
                                   data-style-var="--secondary-color"
                                   value="{{ $website->secondary_color }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Background Banner</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" name="hero_bg_color" 
                                       class="form-control form-control-color w-100 live-update-style" 
                                       data-style-var="--hero-bg-color"
                                       value="{{ $website->hero_bg_color ?? '#333333' }}">
                            </div>
                        </div>

                        <hr>

                        <h6 class="fw-bold mb-3">Tipografi</h6>
                        <div class="mb-3">
                            <label class="form-label small">Jenis Font</label>
                            <select name="font_family" class="form-select live-update-style" data-style-var="--font-main">
                                <option value="Inter" {{ $website->font_family == 'Inter' ? 'selected' : '' }}>Inter (Modern)</option>
                                <option value="Playfair Display" {{ $website->font_family == 'Playfair Display' ? 'selected' : '' }}>Playfair (Elegant)</option>
                                <option value="Roboto" {{ $website->font_family == 'Roboto' ? 'selected' : '' }}>Roboto (Neutral)</option>
                                <option value="Courier Prime" {{ $website->font_family == 'Courier Prime' ? 'selected' : '' }}>Courier (Retro)</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small">Rasio Gambar Produk</label>
                            <select name="product_image_ratio" class="form-select live-update-style" data-style-var="--ratio-product">
                                <option value="1/1" {{ $website->product_image_ratio == '1/1' ? 'selected' : '' }}>Kotak (1:1)</option>
                                <option value="3/4" {{ $website->product_image_ratio == '3/4' ? 'selected' : '' }}>Portrait (3:4)</option>
                                <option value="4/3" {{ $website->product_image_ratio == '4/3' ? 'selected' : '' }}>Landscape (4:3)</option>
                            </select>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab-content">

                        {{-- === LOGIKA PENCARI DATA JSON === --}}
                        @php
                            // 1. Ambil semua sections, atau array kosong jika belum ada
                            $allSections = $website->sections ?? [];

                            // 2. Cari section yang ID-nya 'hero-1'
                            // Kita gunakan helper collect() Laravel agar mudah mencari
                            $heroSection = collect($allSections)->firstWhere('id', 'hero-1');

                            // 3. Ambil 'data'-nya
                            // Jika section ketemu, ambil 'data'. Jika tidak, array kosong.
                            // Note: Kita pakai null coalescing (??) agar tidak error
                            $heroData = $heroSection['data'] ?? [];

                            // === TAMBAHAN BARU: Cari Data Produk ===
                            $prodSection = collect($allSections)->firstWhere('id', 'products');
                            $prodData = $prodSection['data'] ?? [];

                            $featSection = collect($allSections)->firstWhere('id', 'features');
                            $featData = $featSection['data'] ?? [];
                        @endphp
                        <div id="sectionListContainer" class="d-flex flex-column gap-2">
        
                            

                        </div>
                        <hr>
                        <div class="alert alert-info py-2 small">
                            <i class="bi bi-info-circle"></i> Edit bagian Banner Utama
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Judul Utama</label>
                            <input type="text" name="hero_title" 
                                   class="form-control live-update-section" 
                                   data-section-id="hero-1" 
                                   data-key="title" 
                                   value="{{ $heroData['title'] ?? $website->hero_title ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Deskripsi</label>
                            <textarea name="hero_subtitle" 
                                      class="form-control live-update-section" 
                                      data-section-id="hero-1" 
                                      data-key="subtitle" 
                                      rows="3">{{ $heroData['subtitle'] ?? $website->hero_subtitle ?? '' }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Teks Tombol</label>
                            <input type="text" name="hero_btn_text" 
                                   class="form-control live-update-section" 
                                   data-section-id="hero-1" 
                                   data-key="button_text" 
                                   value="{{$heroData['button_text'] ?? $website->hero_btn_text ?? '' }}">
                        </div>
                            <hr class="my-4">
                                <div class="alert alert-info py-2 small">
                                    <i class="bi bi-bag"></i> Edit bagian List Produk
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Judul List Produk</label>
                                    <input type="text" name="product_title" 
                                        class="form-control live-update-section" 
                                        data-section-id="products" 
                                        data-key="title" 
                                        value="{{ $prodData['title'] ?? 'Produk Pilihan' }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Jumlah Produk Tampil</label>
                                    <select name="product_limit" class="form-select live-update-section", data-section-id="products", data-key="limit">
                                        <option value="4" {{ ($prodData['limit'] ?? 8) == 4 ? 'selected' : '' }}>4 Produk</option>
                                        <option value="8" {{ ($prodData['limit'] ?? 8) == 8 ? 'selected' : '' }}>8 Produk</option>
                                        <option value="12" {{ ($prodData['limit'] ?? 8) == 12 ? 'selected' : '' }}>12 Produk</option>
                                    </select>
                                </div>
                                 <hr class="my-4">

                                <div class="alert alert-info py-2 small">
                                    <i class="bi bi-grid-3x3-gap"></i> Edit Keunggulan Toko
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Judul Section</label>
                                    <input type="text" name="feat_title" 
                                        class="form-control live-update-section" 
                                        data-section-id="features" 
                                        data-key="title" 
                                        value="{{ $featData['title'] ?? 'Kenapa Memilih Kami?' }}">
                                </div>

                                <div class="alert alert-light border small text-muted p-2 mb-3">
        Gunakan kode icon dari <a href="https://icons.getbootstrap.com/" target="_blank" class="fw-bold text-decoration-underline">Bootstrap Icons</a>. <br>
        Contoh: <code>whatsapp</code>, <code>star-fill</code>.
    </div>

                        <div class="mb-2 border-bottom pb-2">
                            <label class="small fw-bold text-muted">Fitur 1</label>
                            <div class="d-flex gap-2 mb-1">
                                <input type="text" name="feat_1_icon" class="form-control form-control-sm live-update-section" 
                                    data-section-id="features" data-key="f1_icon" placeholder="Kode Icon (ex: bi-star)"
                                    style="width: 35%;"
                                    value="{{ $featData['f1_icon'] ?? 'bi-patch-check' }}">
                                <input type="text" name="feat_1_title" class="form-control form-control-sm live-update-section" 
                                    data-section-id="features" data-key="f1_title" placeholder="Judul Fitur"
                                    value="{{ $featData['f1_title'] ?? 'Produk Asli' }}">
                            </div>
                            <textarea name="feat_1_desc" class="form-control form-control-sm live-update-section" 
                                data-section-id="features" data-key="f1_desc" rows="2">{{ $featData['f1_desc'] ?? 'Jaminan produk original.' }}</textarea>
                        </div>

                        <div class="mb-2 border-bottom pb-2">
                            <label class="small fw-bold text-muted">Fitur 2</label>
                            <div class="d-flex gap-2 mb-1">
                                <input type="text" name="feat_2_icon" class="form-control form-control-sm live-update-section" 
                                    data-section-id="features" data-key="f2_icon" placeholder="Kode Icon"
                                    style="width: 35%;"
                                    value="{{ $featData['f2_icon'] ?? 'bi-lightning' }}">
                                <input type="text" name="feat_2_title" class="form-control form-control-sm live-update-section" 
                                    data-section-id="features" data-key="f2_title" placeholder="Judul Fitur"
                                    value="{{ $featData['f2_title'] ?? 'Pengiriman Cepat' }}">
                            </div>
                            <textarea name="feat_2_desc" class="form-control form-control-sm live-update-section" 
                                data-section-id="features" data-key="f2_desc" rows="2">{{ $featData['f2_desc'] ?? 'Dikirim hari yang sama.' }}</textarea>
                        </div>

                        <div class="mb-2">
                            <label class="small fw-bold text-muted">Fitur 3</label>
                            <div class="d-flex gap-2 mb-1">
                                <input type="text" name="feat_3_icon" class="form-control form-control-sm live-update-section" 
                                    data-section-id="features" data-key="f3_icon" placeholder="Kode Icon"
                                    style="width: 35%;"
                                    value="{{ $featData['f3_icon'] ?? 'bi-shield-check' }}">
                                <input type="text" name="feat_3_title" class="form-control form-control-sm live-update-section" 
                                    data-section-id="features" data-key="f3_title" placeholder="Judul Fitur"
                                    value="{{ $featData['f3_title'] ?? 'Garansi Resmi' }}">
                            </div>
                            <textarea name="feat_3_desc" class="form-control form-control-sm live-update-section" 
                                data-section-id="features" data-key="f3_desc" rows="2">{{ $featData['f3_desc'] ?? 'Garansi uang kembali.' }}</textarea>
                        </div>
                    </div>
                           
                    <div class="tab-pane fade" id="tab-assets">
    
                        <div class="mb-4 p-3 border rounded bg-light">
                            <label class="form-label small fw-bold">Logo Website</label>
                            
                            <input type="file" name="logo" id="inputLogo" 
                                class="form-control form-control-sm mb-2" 
                                accept="image/png, image/jpeg, image/jpg, image/webp"
                                onchange="handleImageUpload(this, 'logo')">
                            
                            <small class="d-block text-muted mb-2" style="font-size: 11px;">
                                Format: PNG, JPG, WEBP. Maks: 2MB.<br>
                                Disarankan menggunakan background transparan.
                            </small>

                            @if($website->logo)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_logo" id="checkRemoveLogo" value="1"
                                        onchange="handleImageRemove('logo', this.checked)">
                                    <label class="form-check-label small text-danger" for="checkRemoveLogo">
                                        Hapus Logo (Kembali ke Teks)
                                    </label>
                                </div>
                            @endif
                        </div>

                        <div class="mb-4 p-3 border rounded bg-light">
                            <label class="form-label small fw-bold">Gambar Banner (Hero)</label>
                            
                            <input type="file" name="hero_image" id="inputHero"
                                class="form-control form-control-sm mb-2" 
                                accept="image/png, image/jpeg, image/jpg, image/webp"
                                onchange="handleImageUpload(this, 'hero')">
                            
                            <small class="d-block text-muted mb-2" style="font-size: 11px;">
                                Disarankan gambar landscape (Rasio 16:9).<br>
                                Jika dihapus, akan menggunakan warna background polos.
                            </small>

                            @if($website->hero_image)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_hero_image" id="checkRemoveHero" value="1"
                                        onchange="handleImageRemove('hero', this.checked)">
                                    <label class="form-check-label small text-danger" for="checkRemoveHero">
                                        Hapus Gambar Banner
                                    </label>
                                </div>
                            @endif
                        </div>

                        <div class="mb-4 p-3 border rounded bg-light">
                            <label class="form-label small fw-bold">Favicon (Icon Tab Browser)</label>
                            
                            <input type="file" name="favicon" 
                                class="form-control form-control-sm mb-2" 
                                accept="image/png, image/jpeg, image/ico">
                            
                            <small class="d-block text-muted mb-2" style="font-size: 11px;">
                                Format: ICO, PNG. Ukuran kecil (32x32 px).
                            </small>

                            @if($website->favicon)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_favicon" value="1">
                                    <label class="form-check-label small text-danger">
                                        Hapus Favicon
                                    </label>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
@php
    $port = request()->server('SERVER_PORT') == 8000 ? ':8000' : '';
    
    if ($website->custom_domain) {
        $previewUrl = 'http://' . $website->custom_domain . $port;
    } else {
        $previewUrl = 'http://' . $website->subdomain . '.localhost' . $port;
    }
@endphp
                <div class="p-3 border-top bg-light">
                    {{-- Kita ubah jadi type="button" agar form tidak auto-submit --}}
                    <button type="button" onclick="handleSave()" class="btn btn-primary w-100 fw-bold">
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('store.home', ['subdomain' => $website->subdomain]) }}" target="_blank" class="btn btn-link w-100 btn-sm text-muted mt-2">Lihat Live Website</a>
                </div>
            </form>
        </div>

        <div class="col-md-9 bg-light d-flex flex-column align-items-center justify-content-center p-4" style="height: calc(100vh - 65px); overflow: hidden;">
    
            <div class="bg-white rounded-pill shadow-sm px-4 py-2 mb-3 d-flex gap-4 align-items-center z-3">
                <button type="button" class="btn btn-link p-0 text-primary" onclick="setView('desktop', this)"><i class="bi bi-laptop fs-5"></i></button>
                <button type="button" class="btn btn-link p-0 text-muted" onclick="setView('tablet', this)"><i class="bi bi-tablet fs-5"></i></button>
                <button type="button" class="btn btn-link p-0 text-muted" onclick="setView('mobile', this)"><i class="bi bi-phone fs-5"></i></button>
            </div>
            
            <div class="w-100 h-100 d-flex justify-content-center overflow-hidden">
                <div id="previewContainer" class="shadow-lg bg-white overflow-hidden d-flex" style="width: 100%; height: 100%; border: 8px solid #2c3e50; border-radius: 12px; transition: all 0.5s;">
                    <iframe 
                        src="{{ route('store.home', ['subdomain' => $website->subdomain]) }}" 
                        id="previewFrame" 
                        class="w-100 h-100 border-0 shadow-sm"
                        style="min-height: 600px transition: all 0.5s;">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const iframe = document.getElementById('previewFrame');
    
    // === DATA AWAL DARI DB ===
    // Menggunakan empty array sebagai fallback jika data null
    let currentSections = @json($website->sections ?? []);
    if (!Array.isArray(currentSections)) currentSections = [];

    // === DATA GAMBAR ASLI (Untuk Restore) ===
    const originalLogoSrc = "{{ $website->logo ? asset('storage/'.$website->logo) : '' }}";
    const originalHeroSrc = "{{ $website->hero_image ? asset('storage/'.$website->hero_image) : '' }}";

    // --- 1. Fungsi Helper: Kirim Pesan ke Iframe ---
    function sendUpdate(type, payload) {
        if (iframe && iframe.contentWindow) {
            iframe.contentWindow.postMessage({ type: type, ...payload }, '*');
        }
    }

    // --- 2. Fungsi Helper: Auto Prefix 'bi-' ---
    function formatIconClass(val) {
        if (!val) return '';
        val = val.trim();
        // Jika user sudah ngetik 'bi-', biarkan. Jika belum, tambahkan.
        return val.startsWith('bi-') ? val : `bi-${val}`;
    }

    // --- 3. Event Listener: LIVE PREVIEW (Input Text/Select) ---
    document.querySelectorAll('.live-update-section').forEach(input => {
        input.addEventListener('input', function() {
            let val = this.value;
            const key = this.dataset.key;

            // KHUSUS ICON: Tambahkan prefix bi- otomatis saat preview
            if (key.includes('icon')) {
                val = formatIconClass(val);
            }

            sendUpdate('updateSection', {
                sectionId: this.dataset.sectionId,
                key: key,
                value: val
            });
        });
    });

    // --- 4. Event Listener: STYLE (Warna/Font) ---
    document.querySelectorAll('.live-update-style').forEach(input => {
        input.addEventListener('input', function() {
            sendUpdate('updateStyle', {
                variable: this.dataset.styleVar,
                value: this.value
            });
        });
    });

    // --- 5. LOGIC SAVE (DIPERBAIKI & LEBIH KUAT) ---
    function handleSave() {
        console.log("Memulai proses penyimpanan...");
        
        try {
            const form = document.getElementById('builderForm');
            const hiddenInput = document.getElementById('sectionsJsonInput');

            if (!form || !hiddenInput) {
                alert("Error fatal: Form tidak ditemukan.");
                return;
            }

            // A. Helper untuk ambil value dengan aman (biar gak error kalau input hilang)
            const getVal = (name) => {
                const el = document.querySelector(`[name="${name}"]`);
                return el ? el.value : '';
            };

            // B. Racik Data HERO (Ambil via helper)
            const newHeroData = {
                title: getVal('hero_title'),
                subtitle: getVal('hero_subtitle'),
                button_text: getVal('hero_btn_text'),
                button_link: '#products'
            };
            updateOrPushSection('hero-1', 'hero', newHeroData);

            // C. Racik Data PRODUCTS
            const newProdData = {
                title: getVal('product_title') || 'Produk Pilihan',
                limit: parseInt(getVal('product_limit')) || 8
            };
            updateOrPushSection('products', 'products', newProdData);

            // D. Racik Data FEATURES (Dengan Auto Prefix Icon)
            const newFeatData = {
                title: getVal('feat_title'),
                
                f1_title: getVal('feat_1_title'), 
                f1_desc: getVal('feat_1_desc'), 
                f1_icon: formatIconClass(getVal('feat_1_icon')), // <--- AUTO PREFIX

                f2_title: getVal('feat_2_title'), 
                f2_desc: getVal('feat_2_desc'), 
                f2_icon: formatIconClass(getVal('feat_2_icon')), // <--- AUTO PREFIX

                f3_title: getVal('feat_3_title'), 
                f3_desc: getVal('feat_3_desc'), 
                f3_icon: formatIconClass(getVal('feat_3_icon'))  // <--- AUTO PREFIX
            };
            updateOrPushSection('features', 'features', newFeatData);

            // E. Finalisasi JSON
            const jsonString = JSON.stringify(currentSections);
            hiddenInput.value = jsonString;
            
            console.log("JSON Success:", jsonString);
            
            // F. Submit Form
            form.submit();

        } catch (error) {
            console.error("Gagal Save:", error);
            alert("Terjadi kesalahan teknis saat menyimpan. Cek Console.");
        }
    }

    // Helper untuk update array sections (agar kode handleSave lebih rapi)
    function updateOrPushSection(id, type, dataPayload) {
        let index = currentSections.findIndex(s => s.id === id);
        if (index > -1) {
            // Update data saja, pertahankan status visible
            currentSections[index].data = dataPayload;
        } else {
            // Buat baru
            currentSections.push({ 
                id: id, 
                type: type, 
                visible: true, 
                data: dataPayload 
            });
        }
    }

    // --- 6. Handle Visibility & Move (Fitur Sebelumnya) ---
    // (Kode Toggle Visibility & Move Section tetap sama, tidak perlu diubah)
    // Pastikan Anda menyalin fungsi toggleVisibility dan moveSection yang lama kesini 
    // ATAU biarkan kode di bawah ini:

    // === CONFIG: Label & Icon untuk setiap Section ID ===
    const sectionConfig = {
        'hero-1':   { label: 'Banner Utama', icon: 'bi-image' },
        'products': { label: 'List Produk',  icon: 'bi-bag' },
        'features': { label: 'Keunggulan',   icon: 'bi-grid-3x3-gap' }
    };

    // === 7. FUNGSI BARU: Render Daftar Section ===
    function renderSectionList() {
        const container = document.getElementById('sectionListContainer');
        container.innerHTML = ''; // Bersihkan dulu isinya

        currentSections.forEach((section, index) => {
            // Ambil Config (Label & Icon)
            const config = sectionConfig[section.id] || { label: section.id, icon: 'bi-square' };
            
            // Cek Visibilitas
            const isVisible = (section.visible !== false); // Default true
            const eyeIcon = isVisible ? 'bi-eye' : 'bi-eye-slash';
            const eyeColor = isVisible ? '' : 'text-danger';

            // Disable tombol panah jika di ujung
            const disableUp = index === 0 ? 'disabled' : '';
            const disableDown = index === (currentSections.length - 1) ? 'disabled' : '';

            // Buat HTML Kartu
            const html = `
                <div class="d-flex align-items-center justify-content-between p-2 border rounded bg-white section-item" data-id="${section.id}">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi ${config.icon} text-muted"></i>
                        <span class="small fw-bold">${config.label}</span>
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-light border" onclick="moveSection('${section.id}', 'up')" ${disableUp}>
                            <i class="bi bi-arrow-up-short"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-light border" onclick="moveSection('${section.id}', 'down')" ${disableDown}>
                            <i class="bi bi-arrow-down-short"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-light border btn-visibility ${eyeColor}" onclick="toggleVisibility('${section.id}', this)">
                            <i class="bi ${eyeIcon}"></i>
                        </button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        });
    }

    // === 8. UPDATE: Move Section (Logic Lebih Stabil) ===
    function moveSection(sectionId, direction) {
        const index = currentSections.findIndex(s => s.id === sectionId);
        if (index === -1) return;

        // Tentukan Target Swap
        const targetIndex = direction === 'up' ? index - 1 : index + 1;

        // Validasi Batas Array
        if (targetIndex < 0 || targetIndex >= currentSections.length) return;

        // A. TUKAR DATA DI ARRAY
        [currentSections[index], currentSections[targetIndex]] = [currentSections[targetIndex], currentSections[index]];

        // B. KIRIM SINYAL KE IFRAME (Live Preview)
        sendUpdate('moveSection', {
            sectionId: sectionId,
            direction: direction
        });

        // C. RENDER ULANG SIDEBAR (Agar sinkron)
        renderSectionList();
    }

    // === 9. UPDATE: Toggle Visibility (Logic Render Ulang) ===
    function toggleVisibility(sectionId, btn) {
        let section = currentSections.find(s => s.id === sectionId);
        if (!section) return;

        // Toggle nilai
        section.visible = (section.visible === undefined) ? false : !section.visible;

        // Kirim Sinyal
        sendUpdate('toggleSection', { sectionId: sectionId, visible: section.visible });
        
        // Render Ulang (Ganti icon mata otomatis)
        renderSectionList();
    }

    // === 10. INIT SAAT LOAD ===
    // Panggil renderSectionList saat halaman pertama kali dibuka
    window.addEventListener('load', () => {
        // Pastikan currentSections punya minimal data default jika kosong
        if(currentSections.length === 0) {
             // Opsional: Isi default jika database kosong melompong (Logic fallback)
        }
        renderSectionList();
    });

    // --- 7. Handle Image Upload & Remove (Kode Sebelumnya) ---
    function handleImageUpload(input, type) {
        // (Salin fungsi handleImageUpload yang ada validasi size/type dari langkah sebelumnya)
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        
        // Validasi sederhana
        if(file.size > 2 * 1024 * 1024) { alert('File terlalu besar (Max 2MB)'); return; }

        if(type === 'logo') { let c=document.getElementById('checkRemoveLogo'); if(c) c.checked=false; }
        if(type === 'hero') { let c=document.getElementById('checkRemoveHero'); if(c) c.checked=false; }

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Kirim sinyal update gambar
                sendUpdate('updateImage', {
                    target: type, // 'logo' atau 'hero'
                    src: e.target.result,
                    action: 'upload' // Penanda aksi
                });
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function handleImageRemove(type, isChecked) {
        if (isChecked) {
            if(type==='logo') document.getElementById('inputLogo').value = '';
            if(type==='hero') document.getElementById('inputHero').value = '';
        }
        let srcToRestore = '';
        if (!isChecked) {
            if(type === 'logo') srcToRestore = originalLogoSrc;
            if(type === 'hero') srcToRestore = originalHeroSrc;
        }
        sendUpdate('updateImage', { target: type, src: srcToRestore, action: isChecked ? 'remove' : 'restore' });
    }
    // 5. Kontrol Responsive View
    function setView(mode, btn) {
        const container = document.getElementById('previewContainer');
        const buttons = btn.parentElement.querySelectorAll('button');
        
        // Reset tombol
        buttons.forEach(b => {
            b.classList.remove('text-primary');
            b.classList.add('text-muted');
        });
        btn.classList.remove('text-muted');
        btn.classList.add('text-primary');

        // Ubah Ukuran
        if (mode === 'desktop') container.style.maxWidth = '100%';
        if (mode === 'tablet') container.style.maxWidth = '768px';
        if (mode === 'mobile') container.style.maxWidth = '375px';
    }
    // Init Visibility UI saat Load
    window.addEventListener('load', () => {
        currentSections.forEach(s => {
            if(s.visible === false) {
                const item = document.querySelector(`.section-item[data-id="${s.id}"]`);
                if(item) {
                    const btn = item.querySelector('.btn-visibility');
                    if(btn) {
                        btn.querySelector('i').classList.replace('bi-eye', 'bi-eye-slash');
                        btn.classList.add('text-danger');
                    }
                }
            }
        });
    });
</script>
@endsection