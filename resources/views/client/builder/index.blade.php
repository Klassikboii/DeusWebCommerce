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

            <form action="{{ route('client.builder.update', $website->id) }}" method="POST" enctype="multipart/form-data" class="flex-grow-1 overflow-auto">
                @csrf
                @method('PUT')

                <div class="tab-content p-4">
                    
                    <div class="tab-pane fade show active" id="tab-style">
                        
                        <h6 class="fw-bold mb-3">Warna</h6>
                        <div class="mb-3">
                            <label class="form-label small">Primary Color</label>
                            <input type="color" name="primary_color" id="primaryColorInput" class="form-control form-control-color w-100" value="{{ $website->primary_color }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Secondary Color</label>
                            <input type="color" name="secondary_color" id="secondaryColorInput" class="form-control form-control-color w-100" value="{{ $website->secondary_color }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Warna Background Banner</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" name="hero_bg_color" id="heroBgColorInput" 
                                    class="form-control form-control-color w-100" 
                                    value="{{ $website->hero_bg_color ?? '#333333' }}">
                                <small class="text-muted" style="font-size: 10px; line-height: 1.2;">Dipakai jika tidak ada gambar banner.</small>
                            </div>
                        </div>

                        <hr>

                        <h6 class="fw-bold mb-3">Tipografi</h6>
                        <div class="mb-3">
                            <label class="form-label small">Jenis Font</label>
                            <select name="font_family" id="fontFamilyInput" class="form-select">
                                <option value="Inter" {{ $website->font_family == 'Inter' ? 'selected' : '' }}>Inter (Modern)</option>
                                <option value="Playfair Display" {{ $website->font_family == 'Playfair Display' ? 'selected' : '' }}>Playfair (Elegant)</option>
                                <option value="Roboto" {{ $website->font_family == 'Roboto' ? 'selected' : '' }}>Roboto (Neutral)</option>
                                <option value="Courier Prime" {{ $website->font_family == 'Courier Prime' ? 'selected' : '' }}>Courier (Retro)</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small">Ukuran Teks (Base)</label>
                            <div class="d-flex gap-2 align-items-center">
                                <input type="range" name="base_font_size" id="fontSizeInput" class="form-range" min="12" max="18" value="{{ $website->base_font_size }}">
                                <span id="fontSizeVal" class="badge bg-light text-dark border">{{ $website->base_font_size }}px</span>
                            </div>
                        </div>

                        <hr>

                        <h6 class="fw-bold mb-3">Layout Produk</h6>
                        <div class="mb-3">
                            <label class="form-label small">Rasio Gambar</label>
                            <select name="product_image_ratio" id="ratioInput" class="form-select">
                                <option value="1/1" {{ $website->product_image_ratio == '1/1' ? 'selected' : '' }}>Kotak (1:1)</option>
                                <option value="3/4" {{ $website->product_image_ratio == '3/4' ? 'selected' : '' }}>Portrait (3:4)</option>
                                <option value="4/3" {{ $website->product_image_ratio == '4/3' ? 'selected' : '' }}>Landscape (4:3)</option>
                                <option value="16/9" {{ $website->product_image_ratio == '16/9' ? 'selected' : '' }}>Wide (16:9)</option>
                            </select>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab-content">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Judul Utama</label>
                            <input type="text" name="hero_title" id="inputHeroTitle" class="form-control" value="{{ $website->hero_title }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Deskripsi</label>
                            <textarea name="hero_subtitle" id="inputHeroSubtitle" class="form-control" rows="3">{{ $website->hero_subtitle }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Teks Tombol</label>
                            <input type="text" name="hero_btn_text" id="inputHeroBtn" class="form-control" value="{{ $website->hero_btn_text }}">
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab-assets">
                        
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Logo Website</label>
                            <input type="file" name="logo" id="logoInput" class="form-control form-control-sm mb-1" accept="image/*">
                            
                            @if($website->logo)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_logo" value="1" id="removeLogoCheck">
                                    <label class="form-check-label small text-danger" for="removeLogoCheck">
                                        Hapus Logo (Kembali ke Teks)
                                    </label>
                                </div>
                            @endif
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">Gambar Banner (Hero)</label>
                            <input type="file" name="hero_image" id="heroImageInput" class="form-control form-control-sm mb-1" accept="image/*">
                            
                            @if($website->hero_image)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_hero_image" value="1" id="removeHeroCheck">
                                    <label class="form-check-label small text-danger" for="removeHeroCheck">
                                        Hapus Gambar (Gunakan Warna Polos)
                                    </label>
                                </div>
                            @endif
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">Favicon Website</label>
                            <input type="file" name="favicon" id="faviconInput" class="form-control form-control-sm mb-1" accept="image/*">
                            
                            @if($website->favicon)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_favicon" value="1" id="removeFaviconCheck">
                                    <label class="form-check-label small text-danger" for="removeFaviconCheck">
                                        Hapus Favicon (Kembali ke Teks)
                                    </label>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="p-3 border-top bg-light">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Simpan Perubahan</button>
                    <a href="{{ route('store.home', $website->subdomain) }}" target="_blank" class="btn btn-link w-100 btn-sm text-muted mt-2">Lihat Live Website</a>
                </div>
            </form>
        </div>

        <div class="col-md-9 bg-light d-flex flex-column align-items-center justify-content-center p-4" style="height: calc(100vh - 65px); overflow: hidden;">
    
    <div class="bg-white rounded-pill shadow-sm px-4 py-2 mb-3 d-flex gap-4 align-items-center z-3">
        <button type="button" class="btn btn-link p-0 text-primary transition-icon" id="btnDesktop" title="Tampilan Desktop" onclick="setView('desktop')">
            <i class="bi bi-laptop fs-5"></i>
        </button>
        <button type="button" class="btn btn-link p-0 text-muted transition-icon" id="btnTablet" title="Tampilan Tablet" onclick="setView('tablet')">
            <i class="bi bi-tablet fs-5"></i>
        </button>
        <button type="button" class="btn btn-link p-0 text-muted transition-icon" id="btnMobile" title="Tampilan Mobile" onclick="setView('mobile')">
            <i class="bi bi-phone fs-5"></i>
        </button>
    </div>

    <div id="screenLabel" class="text-muted small mb-2 fw-bold opacity-75">
        Desktop View (Full Width)
    </div>

    <div class="w-100 h-100 d-flex justify-content-center overflow-hidden">
        
        <div id="previewContainer" 
             class="shadow-lg bg-white overflow-hidden d-flex"
             style="width: 100%; height: 100%; border: 8px solid #2c3e50; border-radius: 12px; transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);">
            
            <iframe src="{{ route('store.home', $website->subdomain) }}" 
                    id="previewFrame"
                    class="w-100 h-100 border-0"
                    style="background-color: white;">
            </iframe>
        </div>

    </div>
</div>
        </div>
    </div>
</div>

<script>
    const previewFrame = document.getElementById('previewFrame');

    // --- 1. UPDATE CSS VARIABLES (Warna, Font, Size, Ratio) ---
    function updateStyles() {

        
        if (!previewFrame.contentWindow) return;
        const root = previewFrame.contentWindow.document.documentElement;

        // Warna
        root.style.setProperty('--primary-color', document.getElementById('primaryColorInput').value);
        root.style.setProperty('--secondary-color', document.getElementById('secondaryColorInput').value);
        
        // Font Size (Kita ubah di elemen 'html' agar rem units ikut berubah)
        // OPTIMASI FONT SIZE
        const size = document.getElementById('fontSizeInput').value;
        
        // 1. Ubah font dasar HTML (agar rem units bereaksi)
        root.style.fontSize = size + 'px';
        document.getElementById('fontSizeVal').innerText = size + 'px';

        // 2. PAKSA Footer & Body agar ikut berubah (Override class bootstrap)
        const doc = previewFrame.contentWindow.document;
        doc.body.style.fontSize = size + 'px';
        
        // Cari footer dan paksa ukurannya
        const footers = doc.querySelectorAll('footer, .small, small');
        footers.forEach(el => {
            // Kita set font size relatif, misal 0.9 dari ukuran base
            // Agar footer tetap lebih kecil dari body, tapi ikut membesar saat base membesar
            el.style.fontSize = '0.9em'; 

        const heroBgInput = document.getElementById('heroBgColorInput');
        if(heroBgInput) {
            root.style.setProperty('--hero-bg-color', heroBgInput.value);
        }
        });

        // Font Family (Hanya ganti nama font, loading CDN diurus di template)
        const font = document.getElementById('fontFamilyInput').value;
        root.style.setProperty('--font-main', font);

        // Rasio Gambar Produk
        const ratio = document.getElementById('ratioInput').value; // ex: "4/3"
        root.style.setProperty('--ratio-product', ratio);
    }

    // --- 2. UPDATE TEXT CONTENT ---
    function updateText() {
        if (!previewFrame.contentWindow) return;
        const doc = previewFrame.contentWindow.document;
        
        const setTxt = (id, val) => { if(doc.getElementById(id)) doc.getElementById(id).innerText = val; };

        setTxt('hero-title-text', document.getElementById('inputHeroTitle').value);
        setTxt('hero-subtitle-text', document.getElementById('inputHeroSubtitle').value);
        setTxt('hero-btn-text', document.getElementById('inputHeroBtn').value);
    }

    // --- 3. PREVIEW GAMBAR UPLOAD (Tanpa Simpan Dulu) ---
    function previewImage(input, targetIdInIframe, isBackground = false) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (!previewFrame.contentWindow) return;
                const doc = previewFrame.contentWindow.document;
                const el = doc.getElementById(targetIdInIframe);
                
                if (el) {
                    if (isBackground) {
                        el.style.backgroundImage = `url('${e.target.result}')`;
                        // Pastikan style cover tetap ada
                        el.style.backgroundSize = 'cover';
                    } else {
                        // Kalau image tag (Logo)
                        el.src = e.target.result;
                        el.style.display = 'block'; // Tampilkan jika sebelumnya hidden
                        
                        // Sembunyikan teks nama toko jika logo ada
                        const textLogo = doc.getElementById('site-name-text');
                        if(textLogo) textLogo.style.display = 'none';
                    }
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // --- EVENT LISTENERS (UPDATE LENGKAP) ---
    
    // 1. Daftar semua ID input yang mempengaruhi Style CSS
    const styleInputs = [
        'primaryColorInput', 
        'secondaryColorInput', 
        'fontSizeInput', 
        'fontFamilyInput', 
        'ratioInput',
        'heroBgColorInput' // <--- PASTIKAN INI ADA!
    ];

    // 2. Pasang 'telinga' (listener) ke semua input tersebut
    styleInputs.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', updateStyles);
        } else {
            console.warn('Elemen JS tidak ditemukan:', id); // Debugging jika ada typo
        }
    });

    // 3. Listener untuk Teks (Konten)
    ['inputHeroTitle', 'inputHeroSubtitle', 'inputHeroBtn'].forEach(id => {
        const element = document.getElementById(id);
        if(element) element.addEventListener('input', updateText);
    });

    // 4. Listener untuk Gambar (Preview Upload)
    const logoInput = document.getElementById('logoInput');
    if(logoInput) {
        logoInput.addEventListener('change', function() {
            previewImage(this, 'logo-img-preview', false);
        });
    }
    
    const heroImageInput = document.getElementById('heroImageInput');
    if(heroImageInput) {
        heroImageInput.addEventListener('change', function() {
            previewImage(this, 'hero-section-bg', true);
        });
    }

    // Init saat load
    previewFrame.onload = function() {
        // Optional: Sync values
    };

    // ... kode event listener styleInputs yang sudah ada ...

    // --- FITUR RESPONSIVE PREVIEW ---
    const btnDesktop = document.getElementById('btnDesktop');
    const btnTablet = document.getElementById('btnTablet');
    const btnMobile = document.getElementById('btnMobile');
    const container = document.getElementById('previewContainer');
    const screenLabel = document.getElementById('screenLabel');

    function setView(mode) {
        // 1. Reset Warna Tombol (Semua jadi abu-abu dulu)
        [btnDesktop, btnTablet, btnMobile].forEach(btn => {
            btn.classList.remove('text-primary');
            btn.classList.add('text-muted');
        });

        // 2. Ubah Ukuran Iframe sesuai Mode
        if (mode === 'desktop') {
            container.style.maxWidth = '100%'; // Full Lebar
            btnDesktop.classList.replace('text-muted', 'text-primary'); // Aktifkan tombol laptop
            screenLabel.innerText = "Desktop View (Full Width)";
            
        } else if (mode === 'tablet') {
            container.style.maxWidth = '768px'; // Lebar standar iPad
            btnTablet.classList.replace('text-muted', 'text-primary');
            screenLabel.innerText = "Tablet View (768px)";
            
        } else if (mode === 'mobile') {
            container.style.maxWidth = '375px'; // Lebar standar iPhone
            btnMobile.classList.replace('text-muted', 'text-primary');
            screenLabel.innerText = "Mobile View (375px)";
        }
    }

    // 3. Pasang Listener Klik
    btnDesktop.addEventListener('click', () => setView('desktop'));
    btnTablet.addEventListener('click', () => setView('tablet'));
    btnMobile.addEventListener('click', () => setView('mobile'));
</script>

<script>

    // --- LOGIKA TEKS (BARU) ---
    const titleInput = document.getElementById('inputHeroTitle');
    const subtitleInput = document.getElementById('inputHeroSubtitle');
    const btnInput = document.getElementById('inputHeroBtn');

    function updateText() {
        // Pastikan iframe sudah siap
        if (previewFrame.contentWindow) {
            const doc = previewFrame.contentWindow.document;
            
            // Cari elemen di dalam iframe berdasarkan ID yang kita pasang di Langkah 3
            const titleEl = doc.getElementById('hero-title-text');
            const subEl = doc.getElementById('hero-subtitle-text');
            const btnEl = doc.getElementById('hero-btn-text');

            // Update teksnya (Jika input kosong, pakai default fallback)
            if(titleEl) titleEl.innerText = titleInput.value || 'Selamat Datang';
            if(subEl) subEl.innerText = subtitleInput.value || 'Deskripsi toko Anda...';
            if(btnEl) btnEl.innerText = btnInput.value || 'Klik Disini';
        }
    }

    // Jalankan fungsi saat user mengetik (event 'input')
    titleInput.addEventListener('input', updateText);
    subtitleInput.addEventListener('input', updateText);
    btnInput.addEventListener('input', updateText);

    // Jalankan saat iframe selesai loading agar sinkron
    previewFrame.onload = function() {
        // updateColors(); // Opsional
    };
</script>
@endsection