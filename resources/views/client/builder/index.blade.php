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
                            <input type="color" name="primary_color" class="form-control form-control-color w-100 live-update-style" data-style-var="--primary-color" value="{{ $website->primary_color }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Secondary Color</label>
                            <input type="color" name="secondary_color" class="form-control form-control-color w-100 live-update-style" data-style-var="--secondary-color" value="{{ $website->secondary_color }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Background Banner</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" name="hero_bg_color" class="form-control form-control-color w-100 live-update-style" data-style-var="--hero-bg-color" value="{{ $website->hero_bg_color ?? '#333333' }}">
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
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold m-0">Konten Halaman</h6>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-plus"></i> Tambah Section
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li><a class="dropdown-item small" href="#" onclick="addNewSection('hero')"><i class="bi bi-image me-2"></i>Hero Banner</a></li>
                                    <li><a class="dropdown-item small" href="#" onclick="addNewSection('products')"><i class="bi bi-bag me-2"></i>List Produk</a></li>
                                    <li><a class="dropdown-item small" href="#" onclick="addNewSection('features')"><i class="bi bi-grid-3x3-gap me-2"></i>Keunggulan</a></li>
                                    <li><a class="dropdown-item small" href="#" onclick="addNewSection('text-image')"><i class="bi bi-card-heading me-2"></i>Teks & Gambar</a></li>
                                </ul>
                            </div>
                        </div>

                        <div id="dynamicAccordionContainer" class="accordion d-flex flex-column gap-2 mb-4">
                            </div>
                    </div>
                           
                    <div class="tab-pane fade" id="tab-assets">
                        <div class="mb-4 p-3 border rounded bg-light">
                            <label class="form-label small fw-bold">Logo Website</label>
                            <input type="file" name="logo" id="inputLogo" class="form-control form-control-sm mb-2" accept="image/png, image/jpeg, image/jpg, image/webp" onchange="handleImageUpload(this, 'logo')">
                            <small class="d-block text-muted mb-2" style="font-size: 11px;">Format: PNG, JPG, WEBP. Maks: 2MB.<br>Disarankan menggunakan background transparan.</small>
                            @if($website->logo)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_logo" id="checkRemoveLogo" value="1" onchange="handleImageRemove('logo', this.checked)">
                                    <label class="form-check-label small text-danger" for="checkRemoveLogo">Hapus Logo (Kembali ke Teks)</label>
                                </div>
                            @endif
                        </div>

                        <div class="mb-4 p-3 border rounded bg-light">
                            <label class="form-label small fw-bold">Gambar Banner (Hero)</label>
                            <input type="file" name="hero_image" id="inputHero" class="form-control form-control-sm mb-2" accept="image/png, image/jpeg, image/jpg, image/webp" onchange="handleImageUpload(this, 'hero')">
                            <small class="d-block text-muted mb-2" style="font-size: 11px;">Disarankan gambar landscape (Rasio 16:9).<br>Jika dihapus, akan menggunakan warna background polos.</small>
                            @if($website->hero_image)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_hero_image" id="checkRemoveHero" value="1" onchange="handleImageRemove('hero', this.checked)">
                                    <label class="form-check-label small text-danger" for="checkRemoveHero">Hapus Gambar Banner</label>
                                </div>
                            @endif
                        </div>

                        <div class="mb-4 p-3 border rounded bg-light">
                            <label class="form-label small fw-bold">Favicon (Icon Tab Browser)</label>
                            <input type="file" name="favicon" class="form-control form-control-sm mb-2" accept="image/png, image/jpeg, image/ico">
                            <small class="d-block text-muted mb-2" style="font-size: 11px;">Format: ICO, PNG. Ukuran kecil (32x32 px).</small>
                            @if($website->favicon)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_favicon" value="1">
                                    <label class="form-check-label small text-danger">Hapus Favicon</label>
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
                    <button type="button" onclick="handleSave()" class="btn btn-primary w-100 fw-bold">Simpan Perubahan</button>
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
                    <iframe src="{{ route('store.home', ['subdomain' => $website->subdomain]) }}" id="previewFrame" class="w-100 h-100 border-0 shadow-sm" style="min-height: 600px; transition: all 0.5s;"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const iframe = document.getElementById('previewFrame');
    
    // === DATA AWAL DARI DB ===
    let currentSections = @json($website->sections ?? []);
    if (!Array.isArray(currentSections)) currentSections = [];

    const originalLogoSrc = "{{ $website->logo ? asset('storage/'.$website->logo) : '' }}";
    const originalHeroSrc = "{{ $website->hero_image ? asset('storage/'.$website->hero_image) : '' }}";

    function sendUpdate(type, payload) {
        if (iframe && iframe.contentWindow) {
            iframe.contentWindow.postMessage({ type: type, ...payload }, '*');
        }
    }

    function formatIconClass(val) {
        if (!val) return '';
        val = val.trim();
        return val.startsWith('bi-') ? val : `bi-${val}`;
    }

    // --- Event Listener: STYLE ---
    document.querySelectorAll('.live-update-style').forEach(input => {
        input.addEventListener('input', function() {
            sendUpdate('updateStyle', { variable: this.dataset.styleVar, value: this.value });
        });
    });

    // --- FUNGSI SAVE ---
    function handleSave() { saveToServer(false); }

    function saveToServer(isReload = false) {
        try {
            const form = document.getElementById('builderForm');
            const hiddenInput = document.getElementById('sectionsJsonInput');

            // Ekstrak data dari DOM HTML sebelum save
            document.querySelectorAll('.section-form-block').forEach(block => {
                const sId = block.dataset.sectionId;
                let sectionRef = currentSections.find(s => s.id === sId);
                if (sectionRef) {
                    const inputs = block.querySelectorAll('.live-update-section');
                    inputs.forEach(input => {
                        const key = input.dataset.key;
                        let val = input.value;
                        if (key.includes('icon')) val = formatIconClass(val);
                        sectionRef.data[key] = val; 
                    });
                }
            });

            hiddenInput.value = JSON.stringify(currentSections);
            
            if(isReload) {
                 // Untuk cegah reload berulang saat tambah data, gunakan Fetch API 
                 // Tapi karena struktur Anda Form Submit biasa, kita reload saja
                 form.submit();
            } else {
                 form.submit();
            }

        } catch (error) {
            console.error("Gagal Save:", error);
            alert("Terjadi kesalahan teknis.");
        }
    }

    function addNewSection(type) {
        const uniqueId = type + '-' + Date.now().toString().slice(-6);
        let defaultData = {};
        
        if (type === 'hero') defaultData = { title: 'Judul Baru', subtitle: 'Deskripsi...', button_text: 'Klik Disini' };
        else if (type === 'products') defaultData = { title: 'Produk Baru', limit: 8 };
        else if (type === 'features') defaultData = { title: 'Keunggulan Kami', f1_title: 'Fitur 1', 
        f1_desc: 'Des 1', f1_icon: 'bi-star', f2_title: 'Fitur 2', f2_desc: 'Des 2', f2_icon: 'bi-lightning', 
        f3_title: 'Fitur 3', f3_desc: 'Des 3', f3_icon: 'bi-shield-check' };
        else if (type === 'text-image') {
            // <--- TAMBAHAN BARU: Default data untuk Text & Image
            defaultData = { 
                title: 'Cerita Toko Kami', 
                description: 'Ceritakan sejarah singkat toko Anda di sini...', 
                button_text: 'Baca Selengkapnya',
                button_link: '/blog',
                layout: 'image_left' // Bisa 'image_left' atau 'image_right'
            };}

        currentSections.push({ id: uniqueId, type: type, visible: true, data: defaultData });
        saveToServer(true); 
    }

    function deleteSection(sectionId) {
        if(!confirm('Hapus section ini?')) return;
        currentSections = currentSections.filter(s => s.id !== sectionId);
        saveToServer(true);
    }

    function moveSection(sectionId, direction) {
        // Ambil data terbaru dari input form agar tidak hilang saat ditukar posisinya
        extractDataFromDOM();

        const index = currentSections.findIndex(s => s.id === sectionId);
        if (index === -1) return;

        const targetIndex = direction === 'up' ? index - 1 : index + 1;
        if (targetIndex < 0 || targetIndex >= currentSections.length) return;

        [currentSections[index], currentSections[targetIndex]] = [currentSections[targetIndex], currentSections[index]];

        sendUpdate('moveSection', { sectionId: sectionId, direction: direction });
        renderSectionList(); // Render ulang HTML sesuai urutan baru
    }

    function toggleVisibility(sectionId, btn) {
        extractDataFromDOM();
        let section = currentSections.find(s => s.id === sectionId);
        if (!section) return;

        section.visible = (section.visible === undefined) ? false : !section.visible;
        sendUpdate('toggleSection', { sectionId: sectionId, visible: section.visible });
        renderSectionList();
    }

    function getSectionConfig(type, id) {
        if (type === 'hero') return { label: 'Banner Utama', icon: 'bi-image' };
        if (type === 'products') return { label: 'List Produk', icon: 'bi-bag' };
        if (type === 'features') return { label: 'Keunggulan', icon: 'bi-grid-3x3-gap' };
        if (type === 'text-image') return { label: 'Teks & Gambar', icon: 'bi-card-heading' }; // <--- TAMBAHAN BARU
        return { label: id, icon: 'bi-square' };
    }

    // Helper untuk menyimpan data yang diketik user sebelum HTML di render ulang
    function extractDataFromDOM() {
         document.querySelectorAll('.section-form-block').forEach(block => {
            const sId = block.dataset.sectionId;
            let sectionRef = currentSections.find(s => s.id === sId);
            if (sectionRef) {
                block.querySelectorAll('.live-update-section').forEach(input => {
                    sectionRef.data[input.dataset.key] = input.value; 
                });
            }
        });
    }

    // === INI JANTUNGNYA! MERENDER ACCORDION VIA JS ===
    // === INI JANTUNGNYA! MERENDER ACCORDION VIA JS ===
    function renderSectionList() {
        const container = document.getElementById('dynamicAccordionContainer');
        if(!container) return;
        container.innerHTML = ''; 

        if(currentSections.length === 0) {
            container.innerHTML = `<div class="alert alert-warning small">Belum ada section. Silakan klik Tambah Section.</div>`;
            return;
        }

        currentSections.forEach((section, index) => {
            const config = getSectionConfig(section.type, section.id);
            const isVisible = (section.visible !== false); 
            const eyeIcon = isVisible ? 'bi-eye' : 'bi-eye-slash';
            const eyeColor = isVisible ? '' : 'text-danger';
            const sData = section.data || {};

            const disableUp = index === 0 ? 'disabled' : '';
            const disableDown = index === (currentSections.length - 1) ? 'disabled' : '';

            // --- TAMBAHAN LOGIKA CERDAS: GENERATE DROPDOWN LINK ---
            // Buat opsi dropdown dinamis berdasarkan section yang sedang aktif
            let linkOptionsHtml = `<option value="">-- Pilih Tujuan --</option>`;
            linkOptionsHtml += `<option value="/blog" ${sData.button_link === '/blog' ? 'selected' : ''}>📄 Halaman Blog</option>`;
            
            currentSections.forEach(s => {
                // Jangan tampilkan section yang di-hidden, dan jangan link ke section itu sendiri
                if(s.visible !== false && s.id !== section.id) { 
                    const secConfig = getSectionConfig(s.type, s.id);
                    const targetUrl = `#${s.id}`;
                    const isSelected = (sData.button_link === targetUrl) ? 'selected' : '';
                    linkOptionsHtml += `<option value="${targetUrl}" ${isSelected}>⬇️ Scroll ke ${secConfig.label} (${s.id})</option>`;
                }
            });
            // --------------------------------------------------------

            // GENERATE FORM BERDASARKAN TYPE
            let formHtml = '';
            if(section.type === 'hero') {
                formHtml = `
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Judul Utama</label>
                        <input type="text" class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="title" value="${sData.title || ''}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Deskripsi</label>
                        <textarea class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="subtitle" rows="3">${sData.subtitle || ''}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold">Teks Tombol</label>
                            <input type="text" class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="button_text" value="${sData.button_text || ''}">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold">Target Tombol</label>
                            <select class="form-select form-select-sm live-update-section" data-section-id="${section.id}" data-key="button_link">
                                ${linkOptionsHtml}
                            </select>
                        </div>
                    </div>
                `;
            } else if(section.type === 'products') {
                const limitStr = sData.limit || 8;
                formHtml = `
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Judul List Produk</label>
                        <input type="text" class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="title" value="${sData.title || 'Produk Pilihan'}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Jumlah Produk Tampil</label>
                        <select class="form-select form-select-sm live-update-section" data-section-id="${section.id}" data-key="limit">
                            <option value="4" ${limitStr == 4 ? 'selected' : ''}>4 Produk</option>
                            <option value="8" ${limitStr == 8 ? 'selected' : ''}>8 Produk</option>
                            <option value="12" ${limitStr == 12 ? 'selected' : ''}>12 Produk</option>
                        </select>
                    </div>
                `;
            } else if(section.type === 'features') {
                formHtml = `
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Judul Section</label>
                        <input type="text" class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="title" value="${sData.title || 'Kenapa Memilih Kami?'}">
                    </div>
                    <div class="alert alert-light border small text-muted p-2 mb-3">
                        Gunakan kode icon dari <a href="https://icons.getbootstrap.com/" target="_blank" class="fw-bold text-decoration-underline">Bootstrap Icons</a>. Contoh: <code>whatsapp</code>
                    </div>
                    <div class="mb-2 border-bottom pb-2">
                        <label class="small fw-bold text-muted">Fitur 1</label>
                        <div class="d-flex gap-2 mb-1">
                            <input type="text" class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="f1_icon" placeholder="Icon" style="width: 35%;" value="${sData.f1_icon || 'bi-patch-check'}">
                            <input type="text" class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="f1_title" placeholder="Judul" value="${sData.f1_title || 'Produk Asli'}">
                        </div>
                        <textarea class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="f1_desc" rows="2">${sData.f1_desc || ''}</textarea>
                    </div>
                    <div class="mb-2 border-bottom pb-2">
                        <label class="small fw-bold text-muted">Fitur 2</label>
                        <div class="d-flex gap-2 mb-1">
                            <input type="text" class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="f2_icon" placeholder="Icon" style="width: 35%;" value="${sData.f2_icon || 'bi-lightning'}">
                            <input type="text" class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="f2_title" placeholder="Judul" value="${sData.f2_title || 'Pengiriman Cepat'}">
                        </div>
                        <textarea class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="f2_desc" rows="2">${sData.f2_desc || ''}</textarea>
                    </div>
                    <div class="mb-2">
                        <label class="small fw-bold text-muted">Fitur 3</label>
                        <div class="d-flex gap-2 mb-1">
                            <input type="text" class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="f3_icon" placeholder="Icon" style="width: 35%;" value="${sData.f3_icon || 'bi-shield-check'}">
                            <input type="text" class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="f3_title" placeholder="Judul" value="${sData.f3_title || 'Garansi Resmi'}">
                        </div>
                        <textarea class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="f3_desc" rows="2">${sData.f3_desc || ''}</textarea>
                    </div>
                `;
            }  else if(section.type === 'text-image') {
                // Tentukan preview gambar (dari database atau placeholder)
                const imgPreview = sData.image ? `/storage/${sData.image}` : 'https://via.placeholder.com/150?text=Pilih+Gambar';
                
                formHtml = `
                    <div class="mb-3 border rounded p-2 bg-light">
                        <label class="form-label small fw-bold">Gambar Section</label>
                        <div class="d-flex align-items-center gap-3">
                            <img src="${imgPreview}" class="rounded border bg-white" style="width: 50px; height: 50px; object-fit: cover;">
                            <div class="flex-grow-1">
                                <input type="file" name="section_images[${section.id}]" class="form-control form-control-sm" accept="image/png, image/jpeg, image/jpg, image/webp" onchange="if(this.files[0]) { saveToServer(true); }">
                                <small class="text-muted" style="font-size: 10px;">Otomatis tersimpan saat dipilih.</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Posisi Gambar</label>
                        <select class="form-select form-select-sm live-update-section" data-section-id="${section.id}" data-key="layout">
                            <option value="image_left" ${sData.layout === 'image_left' ? 'selected' : ''}>Kiri (Gambar), Kanan (Teks)</option>
                            <option value="image_right" ${sData.layout === 'image_right' ? 'selected' : ''}>Kiri (Teks), Kanan (Gambar)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Judul</label>
                        <input type="text" class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="title" value="${sData.title || ''}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Deskripsi</label>
                        <textarea class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="description" rows="4">${sData.description || ''}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold">Teks Tombol</label>
                            <input type="text" class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="button_text" value="${sData.button_text || ''}">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold">Target Tombol</label>
                            <select class="form-select form-select-sm live-update-section" data-section-id="${section.id}" data-key="button_link">
                                ${linkOptionsHtml}
                            </select>
                        </div>
                    </div>
                `;
            }

            // GABUNGKAN HEADER (SORTING) & BODY (FORM)
            const html = `
                <div class="accordion-item section-form-block bg-white border rounded" data-section-id="${section.id}">
                    <div class="accordion-header d-flex align-items-center justify-content-between p-2 bg-light border-bottom">
                        
                        <div class="d-flex align-items-center gap-2 flex-grow-1 cursor-pointer" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#collapse-${section.id}">
                            <i class="bi bi-grip-vertical text-muted"></i>
                            <i class="bi ${config.icon} text-primary"></i>
                            <span class="small fw-bold text-dark">${config.label}</span>
                            <span class="badge bg-secondary ms-auto me-2" style="font-size: 0.6rem;">${section.id}</span>
                        </div>

                        <div class="btn-group z-3">
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0" onclick="moveSection('${section.id}', 'up')" ${disableUp} title="Naik"><i class="bi bi-arrow-up-short"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0" onclick="moveSection('${section.id}', 'down')" ${disableDown} title="Turun"><i class="bi bi-arrow-down-short"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 ${eyeColor}" onclick="toggleVisibility('${section.id}', this)" title="Sembunyikan"><i class="bi ${eyeIcon}"></i></button>
                        </div>
                    </div>

                    <div id="collapse-${section.id}" class="accordion-collapse collapse" data-bs-parent="#dynamicAccordionContainer">
                        <div class="accordion-body p-3">
                            ${formHtml}
                            <div class="text-end mt-3 border-top pt-2">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteSection('${section.id}')"><i class="bi bi-trash"></i> Hapus Section</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        });

        // RE-ATTACH EVENT LISTENER
        document.querySelectorAll('.live-update-section').forEach(input => {
            input.addEventListener('input', function() {
                let val = this.value;
                const key = this.dataset.key;
                if (key.includes('icon')) val = formatIconClass(val);
                sendUpdate('updateSection', { sectionId: this.dataset.sectionId, key: key, value: val });
            });
        });
    }

    // --- INIT ---
    window.addEventListener('load', () => { renderSectionList(); });

    // --- HANDLE ASET GAMBAR ---
    function handleImageUpload(input, type) {
        if (!input.files || !input.files[0]) return;
        if(input.files[0].size > 2 * 1024 * 1024) { alert('File terlalu besar (Max 2MB)'); return; }

        if(type === 'logo') { let c=document.getElementById('checkRemoveLogo'); if(c) c.checked=false; }
        if(type === 'hero') { let c=document.getElementById('checkRemoveHero'); if(c) c.checked=false; }

        const reader = new FileReader();
        reader.onload = function(e) { sendUpdate('updateImage', { target: type, src: e.target.result, action: 'upload' }); }
        reader.readAsDataURL(input.files[0]);
    }

    function handleImageRemove(type, isChecked) {
        if (isChecked) {
            if(type==='logo') document.getElementById('inputLogo').value = '';
            if(type==='hero') document.getElementById('inputHero').value = '';
        }
        let srcToRestore = isChecked ? '' : (type === 'logo' ? originalLogoSrc : originalHeroSrc);
        sendUpdate('updateImage', { target: type, src: srcToRestore, action: isChecked ? 'remove' : 'restore' });
    }

    function setView(mode, btn) {
        const container = document.getElementById('previewContainer');
        btn.parentElement.querySelectorAll('button').forEach(b => { b.classList.remove('text-primary'); b.classList.add('text-muted'); });
        btn.classList.remove('text-muted'); btn.classList.add('text-primary');

        if (mode === 'desktop') container.style.maxWidth = '100%';
        if (mode === 'tablet') container.style.maxWidth = '768px';
        if (mode === 'mobile') container.style.maxWidth = '375px';
    }
    // --- FUNGSI UPLOAD GAMBAR SECTION VIA AJAX ---
    async function uploadSectionImage(input, sectionId) {
        if (!input.files || !input.files[0]) return;
        
        const file = input.files[0];
        
        // Validasi ukuran di sisi Client (Max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file maksimal 2MB!');
            input.value = '';
            return;
        }

        // Tampilkan indikator loading di gambar thumbnail
        const imgPreview = document.getElementById(`preview-img-${sectionId}`);
        const originalSrc = imgPreview.src;
        imgPreview.src = 'https://via.placeholder.com/150?text=Uploading...';

        // Siapkan data untuk dikirim
        const formData = new FormData();
        formData.append('image', file);
        // Ambil CSRF token dari form utama
        formData.append('_token', document.querySelector('input[name="_token"]').value);

        try {
            // Tembak ke endpoint upload yang baru kita buat
            const response = await fetch(`{{ route('client.builder.uploadImage', $website->id) }}`, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // 1. Simpan path gambar ke dalam memory JSON kita!
                let sectionRef = currentSections.find(s => s.id === sectionId);
                if (sectionRef) {
                    sectionRef.data.image = result.path; 
                }

                // 2. Ganti thumbnail di sidebar dengan gambar yang sukses diupload
                imgPreview.src = result.url;
                
                // 3. Masukkan memory terbaru ke hidden input form
                document.getElementById('sectionsJsonInput').value = JSON.stringify(currentSections);

                // Opsional: Jika Anda ingin Iframe langsung refresh atau update (Untuk sekarang, klien bisa klik Simpan Perubahan jika ingin lihat di Iframe)
                alert('Gambar berhasil diupload! Klik "Simpan Perubahan" untuk menerapkan ke website.');

            } else {
                alert('Gagal mengupload gambar.');
                imgPreview.src = originalSrc;
            }
        } catch (error) {
            console.error('Error upload:', error);
            alert('Terjadi kesalahan jaringan.');
            imgPreview.src = originalSrc;
        }
    }
</script>
@endsection