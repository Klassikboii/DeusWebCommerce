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
            {{-- RADAR ERROR & SUCCESS --}}
            @if($errors->any())
                <div class="alert alert-danger m-3 small rounded">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if(session('success'))
                <div class="alert alert-success m-3 small rounded">
                    <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                </div>
            @endif
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
                            <input type="color" name="primary_color" class="form-control form-control-color w-100 live-update-style" data-style-var="--primary-color" value="{{ $website->theme_config['colors']['primary'] ?? $website->primary_color ?? '#0d6efd' }}">                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Secondary Color</label>
                            <input type="color" name="secondary_color" class="form-control form-control-color w-100 live-update-style" data-style-var="--secondary-color" value="{{ $website->theme_config['colors']['secondary'] ?? $website->secondary_color ?? '#6c757d' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Background Banner</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" name="hero_bg_color" class="form-control form-control-color w-100 live-update-style" data-style-var="--hero-bg-color" value="{{ $website->theme_config['colors']['bg_hero'] ??  $website->hero_bg_color ?? '#333333' }}">
                            </div>
                        </div>
                        <hr>
                        <div class="mb-3">
                              <div id="contrast-warning" class="text-danger mt-2 p-2 bg-danger bg-opacity-10 border border-danger rounded d-none" style="font-size: 11px; font-weight: 600;">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Peringatan: Kontras terlalu rendah! Teks akan sangat sulit dibaca oleh pengunjung.
                            </div>
                             <div class="mb-3">
                                <label class="form-label small">Warna Background</label>
                                <input type="color" name="bg_base_color" id="input_bg_base" class="form-control form-control-color w-100 live-update-style" data-style-var="--bg-base" value="{{ $website->theme_config['colors']['bg_base'] ?? '#ffffff' }}">
                            </div>
                            <label class="form-label small">Warna Teks Utama</label>
                            <input type="color" name="text_base_color" id="input_text_base" class="form-control form-control-color w-100 live-update-style" data-style-var="--text-base" value="{{ $website->theme_config['colors']['text_base'] ?? '#212529' }}">

                          
                        </div>
                       
                        <hr>
                        <h6 class="fw-bold mb-3">Tipografi</h6>
                        <div class="mb-3">
                            <label class="form-label small">Jenis Font</label>
                            <select name="font_family" class="form-select live-update-style" data-style-var="--font-main">
                                @php $currentFont = $website->theme_config['typography']['main'] ?? $website->font_family ?? 'Inter'; @endphp
                                <option value="Inter" {{ $currentFont == 'Inter' ? 'selected' : '' }} style="font-family: Inter;">Inter (Modern)</option>
                                <option value="Playfair Display" {{ $currentFont == 'Playfair Display' ? 'selected' : '' }} style="font-family: Playfair Display;">Playfair (Elegant)</option>
                                <option value="Roboto" {{ $currentFont == 'Roboto' ? 'selected' : '' }} style="font-family: Roboto;">Roboto (Neutral)</option>
                                <option value="Courier Prime" {{ $currentFont == 'Courier Prime' ? 'selected' : '' }} style="font-family: Courier Prime;">Courier (Retro)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Rasio Gambar Produk</label>
                            <select name="product_image_ratio" class="form-select live-update-style" data-style-var="--ratio-product">
                                @php $currentRatio = $website->theme_config['shapes']['product_ratio'] ?? $website->product_image_ratio ?? '1/1'; @endphp
                                <option value="1/1" {{ $currentRatio == '1/1' ? 'selected' : '' }}>Kotak (1:1)</option>
                                <option value="3/4" {{ $currentRatio == '3/4' ? 'selected' : '' }}>Portrait (3:4)</option>
                                <option value="4/3" {{ $currentRatio == '4/3' ? 'selected' : '' }}>Landscape (4:3)</option>
                            </select>
                        </div>
                        <hr>
                        
                        <h6 class="fw-bold mb-3">Bentuk & Dimensi</h6>
                        <div class="mb-3">
                            <label class="form-label small">Kelengkungan (Border Radius)</label>
                            <select name="border_radius" class="form-select live-update-style" data-style-var="--radius-base">
                                @php $currentRadius = $website->theme_config['shapes']['radius'] ?? '0.5rem'; @endphp
                                <option value="0px" {{ $currentRadius == '0px' ? 'selected' : '' }}>Kotak Tajam (0px)</option>
                                <option value="0.5rem" {{ $currentRadius == '0.5rem' ? 'selected' : '' }}>Membulat Halus (8px)</option>
                                <option value="1.5rem" {{ $currentRadius == '1.5rem' ? 'selected' : '' }}>Sangat Bulat (24px)</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small">Ketebalan Bayangan (Shadow)</label>
                            <select name="box_shadow" class="form-select live-update-style" data-style-var="--shadow-base">
                                @php $currentShadow = $website->theme_config['shapes']['shadow'] ?? '0 0.125rem 0.25rem rgba(0,0,0,0.075)'; @endphp
                                <option value="none" {{ $currentShadow == 'none' ? 'selected' : '' }}>Tanpa Bayangan (Flat)</option>
                                <option value="0 0.125rem 0.25rem rgba(0,0,0,0.075)" {{ $currentShadow == '0 0.125rem 0.25rem rgba(0,0,0,0.075)' ? 'selected' : '' }}>Halus (Soft)</option>
                                <option value="0 0.5rem 1rem rgba(0,0,0,0.15)" {{ $currentShadow == '0 0.5rem 1rem rgba(0,0,0,0.15)' ? 'selected' : '' }}>Kuat (Bold)</option>
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
                                    <li><a class="dropdown-item small" href="#" onclick="addNewSection('faq')"><i class="bi bi-question-circle me-2"></i>Tanya Jawab (FAQ)</a></li>
                                    <li><a class="dropdown-item small" href="#" onclick="addNewSection('testimonial')"><i class="bi bi-chat-quote me-2"></i>Testimonial</a></li>
                                    <li><a class="dropdown-item small" href="#" onclick="addNewSection('cta')"><i class="bi bi-megaphone me-2"></i>CTA / Promo Banner</a></li>
                                </ul>
                            </div>
                        </div>

                        <div id="dynamicAccordionContainer" class="accordion d-flex flex-column gap-2 mb-4">
                            </div>
                        <hr>
                        
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
                    <a href="{{ $website->store_url }}" target="_blank" class="btn btn-link w-100 btn-sm text-muted mt-2">Lihat Live Website</a>
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
                    <iframe src="{{ $website->store_url }}" id="previewFrame" class="w-100 h-100 border-0 shadow-sm" style="min-height: 600px; transition: all 0.5s;"></iframe>
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

            // 👇 TAMBAHAN: Cek kontras tiap kali warna digeser
            if (this.id === 'input_bg_base' || this.id === 'input_text_base') {
                checkContrast();
            }
        });
    });
    window.addEventListener('load', () => { 
        renderSectionList(); 
        checkContrast(); // <--- TAMBAHAN
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
        else if (type === 'products') {
            defaultData = { 
                title: 'Produk Pilihan', 
                subtitle: 'Koleksi terbaik dari toko kami', // <--- Tambahan Subjudul
                limit: 8 
            };
        }
        else if (type === 'features') defaultData = { title: 'Keunggulan Kami', 
        f1_title: 'Fitur 1', f1_desc: 'Des 1', f1_icon: 'bi-star', 
        f2_title: 'Fitur 2', f2_desc: 'Des 2', f2_icon: 'bi-lightning', 
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
        else if (type === 'faq') {
            defaultData = { 
                title: 'Pertanyaan Umum', 
                subtitle: 'Temukan jawaban untuk pertanyaan yang sering diajukan.', 
                q1_ask: 'Berapa lama estimasi pengiriman?', q1_ans: 'Pengiriman memakan waktu 2-3 hari kerja.',
                q2_ask: 'Apakah ada garansi pengembalian?', q2_ans: 'Ya, kami memberikan garansi 7 hari uang kembali jika produk cacat.',
                q3_ask: 'Bagaimana cara melacak pesanan?',  q3_ans: 'Anda dapat memasukkan nomor pesanan di halaman Cek Pesanan.'
            };
        }
        else if (type === 'testimonial') {
            defaultData = { 
                title: 'Apa Kata Mereka?', 
                subtitle: 'Ulasan asli dari pelanggan setia kami.', 
                t1_name: 'Budi Santoso', t1_role: 'Pengusaha', t1_review: 'Kualitas produk sangat luar biasa, pengiriman juga sangat cepat!',
                t2_name: 'Siti Aminah', t2_role: 'Ibu Rumah Tangga', t2_review: 'Sangat puas belanja di sini. Admin ramah dan responsif.',
                t3_name: 'Andi Wijaya', t3_role: 'Mahasiswa', t3_review: 'Barang sesuai dengan deskripsi, packing sangat aman. Bintang 5!'
            }; 
        }
        else if (type === 'cta') {
            defaultData = { 
                title: 'Dapatkan Diskon 20% Hari Ini!', 
                subtitle: 'Gunakan kode promo: DEUS20 saat checkout.', 
                button_text: 'Belanja Sekarang',
                button_link: '#products'
            };
        }
    


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
       
        if (type === 'faq') return { label: 'Tanya Jawab (FAQ)', icon: 'bi-question-circle' }; 
        if (type === 'testimonial') return { label: 'Testimonial', icon: 'bi-chat-quote' }; 
        if (type === 'cta') return { label: 'CTA / Promo Banner', icon: 'bi-megaphone' };
        return { label: id, icon: 'bi-square'};
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

            // Generate Dropdown Link Target
            let linkOptionsHtml = `<option value="">-- Pilih Tujuan --</option>`;
            linkOptionsHtml += `<option value="/blog" ${sData.button_link === '/blog' ? 'selected' : ''}>📄 Halaman Blog</option>`;
            currentSections.forEach(s => {
                if(s.visible !== false && s.id !== section.id) { 
                    const secConfig = getSectionConfig(s.type, s.id);
                    const targetUrl = `#${s.id}`;
                    const isSelected = (sData.button_link === targetUrl) ? 'selected' : '';
                    linkOptionsHtml += `<option value="${targetUrl}" ${isSelected}>⬇️ Scroll ke ${secConfig.label}</option>`;
                }
            });

            // GENERATE FORM BERDASARKAN TYPE DENGAN UI YANG LEBIH RAPI
            let formHtml = '';
            
            if(section.type === 'hero') {
                formHtml = `
                    <div class="mb-2">
                        <label class="form-label text-muted" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Teks Utama</label>
                        <input type="text" class="form-control form-control-sm mb-2 live-update-section" data-section-id="${section.id}" data-key="title" value="${sData.title || ''}" placeholder="Judul Banner">
                        <textarea class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="subtitle" rows="2" placeholder="Deskripsi/Subjudul...">${sData.subtitle || ''}</textarea>
                    </div>
                    <div class="p-2 bg-white border rounded shadow-sm mt-3">
                        <label class="form-label text-muted mb-2" style="font-size: 11px; font-weight: 600; text-transform: uppercase;"><i class="bi bi-hand-index-thumb me-1"></i>Pengaturan Tombol</label>
                        <input type="text" class="form-control form-control-sm mb-2 live-update-section" data-section-id="${section.id}" data-key="button_text" value="${sData.button_text || ''}" placeholder="Teks Tombol (mis: Beli Sekarang)">
                        <select class="form-select form-select-sm live-update-section" data-section-id="${section.id}" data-key="button_link">
                            ${linkOptionsHtml}
                        </select>
                    </div>
                `;
            } 
            
            else if(section.type === 'products') {
                const limitStr = sData.limit || 8;
                formHtml = `
                    <div class="mb-3">
                        <label class="form-label text-muted" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Pengaturan Teks</label>
                        <input type="text" class="form-control form-control-sm mb-2 live-update-section" data-section-id="${section.id}" data-key="title" value="${sData.title || 'Produk Pilihan'}" placeholder="Judul Section">
                        <textarea class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="subtitle" rows="2" placeholder="Subjudul...">${sData.subtitle || ''}</textarea>
                    </div>
                    <div class="p-2 bg-white border rounded shadow-sm">
                        <label class="form-label text-muted mb-1" style="font-size: 11px; font-weight: 600; text-transform: uppercase;"><i class="bi bi-grid me-1"></i>Jumlah Tampil</label>
                        <select class="form-select form-select-sm live-update-section" data-section-id="${section.id}" data-key="limit">
                            <option value="4" ${limitStr == 4 ? 'selected' : ''}>4 Produk (1 Baris)</option>
                            <option value="8" ${limitStr == 8 ? 'selected' : ''}>8 Produk (2 Baris)</option>
                            <option value="12" ${limitStr == 12 ? 'selected' : ''}>12 Produk (3 Baris)</option>
                        </select>
                    </div>
                `;
            } 
            
            else if(section.type === 'features') {
                formHtml = `
                    <div class="mb-3">
                        <input type="text" class="form-control form-control-sm fw-bold live-update-section" data-section-id="${section.id}" data-key="title" value="${sData.title || 'Kenapa Memilih Kami?'}" placeholder="Judul Section Fitur">
                    </div>
                    
                    <div class="p-2 bg-white border rounded shadow-sm mb-2">
                        <label class="form-label text-primary mb-1" style="font-size: 11px; font-weight: 700;"><i class="bi bi-1-circle me-1"></i>Fitur Pertama</label>
                        <div class="d-flex gap-2 mb-2">
                            <input type="text" class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="f1_icon" placeholder="Icon (bi-star)" style="width: 40%;" value="${sData.f1_icon || ''}">
                            <input type="text" class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="f1_title" placeholder="Judul Fitur" value="${sData.f1_title || ''}">
                        </div>
                        <textarea class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="f1_desc" rows="2" placeholder="Deskripsi singkat...">${sData.f1_desc || ''}</textarea>
                    </div>

                    <div class="p-2 bg-white border rounded shadow-sm mb-2">
                        <label class="form-label text-primary mb-1" style="font-size: 11px; font-weight: 700;"><i class="bi bi-2-circle me-1"></i>Fitur Kedua</label>
                        <div class="d-flex gap-2 mb-2">
                            <input type="text" class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="f2_icon" placeholder="Icon (bi-truck)" style="width: 40%;" value="${sData.f2_icon || ''}">
                            <input type="text" class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="f2_title" placeholder="Judul Fitur" value="${sData.f2_title || ''}">
                        </div>
                        <textarea class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="f2_desc" rows="2" placeholder="Deskripsi singkat...">${sData.f2_desc || ''}</textarea>
                    </div>

                    <div class="p-2 bg-white border rounded shadow-sm">
                        <label class="form-label text-primary mb-1" style="font-size: 11px; font-weight: 700;"><i class="bi bi-3-circle me-1"></i>Fitur Ketiga</label>
                        <div class="d-flex gap-2 mb-2">
                            <input type="text" class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="f3_icon" placeholder="Icon (bi-shield)" style="width: 40%;" value="${sData.f3_icon || ''}">
                            <input type="text" class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="f3_title" placeholder="Judul Fitur" value="${sData.f3_title || ''}">
                        </div>
                        <textarea class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="f3_desc" rows="2" placeholder="Deskripsi singkat...">${sData.f3_desc || ''}</textarea>
                    </div>
                `;
            } 
            
            else if(section.type === 'text-image') {
                const svgPlaceholder = `data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='150' height='150' viewBox='0 0 150 150'%3E%3Crect fill='%23eeeeee' width='150' height='150'/%3E%3Ctext fill='%23999999' x='50%25' y='50%25' text-anchor='middle' dy='.3em' font-family='Arial, sans-serif' font-size='14'%3EPilih Gambar%3C/text%3E%3C/svg%3E`;
                const imgPreview = sData.image ? `/storage/${sData.image}` : svgPlaceholder;
                
                formHtml = `
                    <div class="mb-3">
                        <label class="form-label text-muted" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Layout & Konten</label>
                        <select class="form-select form-select-sm mb-2 live-update-section" data-section-id="${section.id}" data-key="layout">
                            <option value="image_left" ${sData.layout === 'image_left' ? 'selected' : ''}>Kiri (Gambar), Kanan (Teks)</option>
                            <option value="image_right" ${sData.layout === 'image_right' ? 'selected' : ''}>Kiri (Teks), Kanan (Gambar)</option>
                        </select>
                        <input type="text" class="form-control form-control-sm mb-2 live-update-section" data-section-id="${section.id}" data-key="title" value="${sData.title || ''}" placeholder="Judul Paragraf">
                        <textarea class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="description" rows="3" placeholder="Tuliskan cerita/deskripsi Anda...">${sData.description || ''}</textarea>
                    </div>

                    <div class="p-2 bg-white border rounded shadow-sm mb-3 d-flex align-items-center gap-3">
                        <img src="${imgPreview}" id="preview-img-${section.id}" class="rounded border" style="width: 50px; height: 50px; object-fit: cover;">
                        <div class="flex-grow-1">
                            <label class="form-label text-muted mb-1" style="font-size: 10px; font-weight: 600; text-transform: uppercase;">Upload Gambar Section</label>
                            <input type="file" class="form-control form-control-sm" accept="image/png, image/jpeg, image/webp" onchange="uploadSectionImage(this, '${section.id}')">
                        </div>
                    </div>

                    <div class="p-2 bg-white border rounded shadow-sm">
                        <label class="form-label text-muted mb-2" style="font-size: 11px; font-weight: 600; text-transform: uppercase;"><i class="bi bi-hand-index-thumb me-1"></i>Tombol (Opsional)</label>
                        <input type="text" class="form-control form-control-sm mb-2 live-update-section" data-section-id="${section.id}" data-key="button_text" value="${sData.button_text || ''}" placeholder="Teks Tombol (Kosongkan jika tidak perlu)">
                        <select class="form-select form-select-sm live-update-section" data-section-id="${section.id}" data-key="button_link">
                            ${linkOptionsHtml}
                        </select>
                    </div>
                `;
            }

            else if(section.type === 'faq') {
                formHtml = `
                    <div class="mb-3">
                        <input type="text" class="form-control form-control-sm mb-2 fw-bold live-update-section" data-section-id="${section.id}" data-key="title" value="${sData.title || ''}" placeholder="Judul FAQ">
                        <textarea class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="subtitle" rows="2" placeholder="Subjudul FAQ...">${sData.subtitle || ''}</textarea>
                    </div>

                    <div class="p-2 bg-white border rounded shadow-sm mb-2">
                        <label class="form-label text-primary mb-1" style="font-size: 11px; font-weight: 700;"><i class="bi bi-patch-question me-1"></i>Tanya Jawab 1</label>
                        <input type="text" class="form-control form-control-sm mb-2 live-update-section" data-section-id="${section.id}" data-key="q1_ask" value="${sData.q1_ask || ''}" placeholder="Pertanyaan...">
                        <textarea class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="q1_ans" rows="2" placeholder="Jawaban...">${sData.q1_ans || ''}</textarea>
                    </div>

                    <div class="p-2 bg-white border rounded shadow-sm mb-2">
                        <label class="form-label text-primary mb-1" style="font-size: 11px; font-weight: 700;"><i class="bi bi-patch-question me-1"></i>Tanya Jawab 2</label>
                        <input type="text" class="form-control form-control-sm mb-2 live-update-section" data-section-id="${section.id}" data-key="q2_ask" value="${sData.q2_ask || ''}" placeholder="Pertanyaan...">
                        <textarea class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="q2_ans" rows="2" placeholder="Jawaban...">${sData.q2_ans || ''}</textarea>
                    </div>

                    <div class="p-2 bg-white border rounded shadow-sm">
                        <label class="form-label text-primary mb-1" style="font-size: 11px; font-weight: 700;"><i class="bi bi-patch-question me-1"></i>Tanya Jawab 3</label>
                        <input type="text" class="form-control form-control-sm mb-2 live-update-section" data-section-id="${section.id}" data-key="q3_ask" value="${sData.q3_ask || ''}" placeholder="Pertanyaan...">
                        <textarea class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="q3_ans" rows="2" placeholder="Jawaban...">${sData.q3_ans || ''}</textarea>
                    </div>
                `;
            }

            else if(section.type === 'testimonial') {
                formHtml = `
                    <div class="mb-3">
                        <input type="text" class="form-control form-control-sm mb-2 fw-bold live-update-section" data-section-id="${section.id}" data-key="title" value="${sData.title || ''}" placeholder="Judul Testimonial">
                        <textarea class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="subtitle" rows="2" placeholder="Subjudul...">${sData.subtitle || ''}</textarea>
                    </div>

                    <div class="p-2 bg-white border rounded shadow-sm mb-2">
                        <label class="form-label text-primary mb-1" style="font-size: 11px; font-weight: 700;"><i class="bi bi-chat-left-quote me-1"></i>Ulasan 1</label>
                        <div class="row g-2 mb-2">
                            <div class="col-6"><input type="text" class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="t1_name" value="${sData.t1_name || ''}" placeholder="Nama Pelanggan"></div>
                            <div class="col-6"><input type="text" class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="t1_role" value="${sData.t1_role || ''}" placeholder="Status/Asal"></div>
                        </div>
                        <textarea class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="t1_review" rows="2" placeholder="Isi ulasan...">${sData.t1_review || ''}</textarea>
                    </div>

                    <div class="p-2 bg-white border rounded shadow-sm mb-2">
                        <label class="form-label text-primary mb-1" style="font-size: 11px; font-weight: 700;"><i class="bi bi-chat-left-quote me-1"></i>Ulasan 2</label>
                        <div class="row g-2 mb-2">
                            <div class="col-6"><input type="text" class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="t2_name" value="${sData.t2_name || ''}" placeholder="Nama Pelanggan"></div>
                            <div class="col-6"><input type="text" class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="t2_role" value="${sData.t2_role || ''}" placeholder="Status/Asal"></div>
                        </div>
                        <textarea class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="t2_review" rows="2" placeholder="Isi ulasan...">${sData.t2_review || ''}</textarea>
                    </div>

                    <div class="p-2 bg-white border rounded shadow-sm">
                        <label class="form-label text-primary mb-1" style="font-size: 11px; font-weight: 700;"><i class="bi bi-chat-left-quote me-1"></i>Ulasan 3</label>
                        <div class="row g-2 mb-2">
                            <div class="col-6"><input type="text" class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="t3_name" value="${sData.t3_name || ''}" placeholder="Nama Pelanggan"></div>
                            <div class="col-6"><input type="text" class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="t3_role" value="${sData.t3_role || ''}" placeholder="Status/Asal"></div>
                        </div>
                        <textarea class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="t3_review" rows="2" placeholder="Isi ulasan...">${sData.t3_review || ''}</textarea>
                    </div>
                `;
            } else if(section.type === 'cta') {
                formHtml = `
                    <div class="mb-3">
                        <label class="form-label text-muted mb-1" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Konten Penawaran</label>
                        <input type="text" class="form-control form-control-sm mb-2 fw-bold live-update-section" data-section-id="${section.id}" data-key="title" value="${sData.title || ''}" placeholder="Judul Promo/CTA">
                        <textarea class="form-control form-control-sm live-update-section" data-section-id="${section.id}" data-key="subtitle" rows="2" placeholder="Subjudul/Deskripsi Promo...">${sData.subtitle || ''}</textarea>
                    </div>

                    <div class="p-2 bg-white border rounded shadow-sm">
                        <label class="form-label text-muted mb-2" style="font-size: 11px; font-weight: 600; text-transform: uppercase;"><i class="bi bi-hand-index-thumb me-1"></i>Pengaturan Tombol</label>
                        <input type="text" class="form-control form-control-sm mb-2 live-update-section" data-section-id="${section.id}" data-key="button_text" value="${sData.button_text || ''}" placeholder="Teks Tombol (mis: Ambil Promo)">
                        <select class="form-select form-select-sm live-update-section" data-section-id="${section.id}" data-key="button_link">
                            ${linkOptionsHtml}
                        </select>
                    </div>
                `;
            }

            // GABUNGKAN HEADER (SORTING) & BODY (FORM)
            // Perhatikan pergantian p-3 menjadi p-2 dan bg-white menjadi bg-light di accordion-body
            const html = `
                <div class="accordion-item section-form-block border rounded mb-2 shadow-sm" data-section-id="${section.id}">
                    <div class="accordion-header d-flex align-items-center justify-content-between p-2 bg-white border-bottom rounded-top">
                        
                        <div class="d-flex align-items-center gap-2 flex-grow-1 cursor-pointer" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#collapse-${section.id}">
                            <i class="bi bi-grip-vertical text-muted"></i>
                            <i class="bi ${config.icon} text-primary"></i>
                            <span class="small fw-bold text-dark">${config.label}</span>
                            <span class="badge bg-light text-secondary border flex-shrink-0" style="font-size: 0.6rem;">#${section.id.split('-').pop()}</span>                            
                        </div>

                        <div class="btn-group z-3">
                            <button type="button" class="btn btn-sm btn-light border py-0 text-muted" onclick="moveSection('${section.id}', 'up')" ${disableUp} title="Naik"><i class="bi bi-chevron-up"></i></button>
                            <button type="button" class="btn btn-sm btn-light border py-0 text-muted" onclick="moveSection('${section.id}', 'down')" ${disableDown} title="Turun"><i class="bi bi-chevron-down"></i></button>
                            <button type="button" class="btn btn-sm btn-light border py-0 ${eyeColor}" onclick="toggleVisibility('${section.id}', this)" title="Sembunyikan"><i class="bi ${eyeIcon}"></i></button>
                        </div>
                    </div>

                    <div id="collapse-${section.id}" class="accordion-collapse collapse" data-bs-parent="#dynamicAccordionContainer">
                        <div class="accordion-body p-2 bg-light rounded-bottom">
                            ${formHtml}
                            <div class="text-end mt-2 pt-2 border-top">
                                <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="deleteSection('${section.id}')"><i class="bi bi-trash"></i> Hapus Bagian Ini</button>
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

    // === SISTEM SENSOR KONTRAS (WCAG STANDARD) ===
    function checkContrast() {
        const bgInput = document.getElementById('input_bg_base');
        const textInput = document.getElementById('input_text_base');
        const warningBox = document.getElementById('contrast-warning');

        if (!bgInput || !textInput || !warningBox) return;

        const hexToRgb = (hex) => {
            let r = parseInt(hex.slice(1, 3), 16) / 255;
            let g = parseInt(hex.slice(3, 5), 16) / 255;
            let b = parseInt(hex.slice(5, 7), 16) / 255;
            return [r, g, b].map(v => v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4));
        };

        const bgRgb = hexToRgb(bgInput.value);
        const textRgb = hexToRgb(textInput.value);

        const lum1 = bgRgb[0] * 0.2126 + bgRgb[1] * 0.7152 + bgRgb[2] * 0.0722;
        const lum2 = textRgb[0] * 0.2126 + textRgb[1] * 0.7152 + textRgb[2] * 0.0722;

        const brightest = Math.max(lum1, lum2);
        const darkest = Math.min(lum1, lum2);
        
        // Rumus Rasio Kontras Web Accessibility
        const contrast = (brightest + 0.05) / (darkest + 0.05);

        // Jika rasio di bawah 4.5 (Standar minimum teks bisa dibaca) -> Munculkan Warning!
        if (contrast < 4.5) {
            warningBox.classList.remove('d-none');
        } else {
            warningBox.classList.add('d-none');
        }
    }
    // --- FUNGSI UPLOAD GAMBAR SECTION VIA AJAX ---
    // --- FUNGSI UPLOAD GAMBAR SECTION VIA AJAX ---
    async function uploadSectionImage(input, sectionId) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        
        if (file.size > 2 * 1024 * 1024) { alert('Ukuran maksimal 2MB!'); input.value = ''; return; }

        const svgLoading = `data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='150' height='150' viewBox='0 0 150 150'%3E%3Crect fill='%23eeeeee' width='150' height='150'/%3E%3Ctext fill='%23999999' x='50%25' y='50%25' text-anchor='middle' dy='.3em' font-family='Arial, sans-serif' font-size='12'%3EUploading...%3C/text%3E%3C/svg%3E`;
        const imgPreview = document.getElementById(`preview-img-${sectionId}`);
        const originalSrc = imgPreview.src;
        imgPreview.src = svgLoading;

        const formData = new FormData();
        formData.append('image', file);
        formData.append('_token', document.querySelector('input[name="_token"]').value);

        try {
            const response = await fetch(`{{ route('client.builder.uploadImage', $website->id) }}`, { method: 'POST', body: formData });
            const result = await response.json();

            if (result.success) {
                // 1. Amankan data input teks agar tidak hilang
                extractDataFromDOM();

                // 2. Simpan path gambar ke memori
                let sectionRef = currentSections.find(s => s.id === sectionId);
                if (sectionRef) sectionRef.data.image = result.path; 

                // 3. Update hidden input JSON
                document.getElementById('sectionsJsonInput').value = JSON.stringify(currentSections);

                // 4. Ubah thumbnail dan Live Preview Iframe!
                imgPreview.src = result.url;
                sendUpdate('updateSection', { sectionId: sectionId, key: 'image', value: result.url });

            } else {
                alert('Gagal upload.'); imgPreview.src = originalSrc;
            }
        } catch (error) {
            console.error('Error:', error); alert('Error jaringan.'); imgPreview.src = originalSrc;
        }
    }
</script>
@endsection