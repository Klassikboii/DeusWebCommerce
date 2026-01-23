@extends('layouts.client')

@section('title', 'Editor Website')

@section('content')
<div class="container-fluid p-0 h-100">
    
    <div class="row g-0 h-100">
        <div class="col-md-3 bg-white border-end p-4" style="height: calc(100vh - 65px); overflow-y: auto;">
            <div class="mb-4">
                <h5 class="fw-bold">Pengaturan Desain</h5>
                <p class="text-muted small">Sesuaikan tampilan toko Anda.</p>
            </div>

            <form action="{{ route('client.builder.update', $website->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase">Warna Utama</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="color" 
                               name="primary_color" 
                               id="primaryColorInput" 
                               class="form-control form-control-color" 
                               value="{{ $website->primary_color }}" 
                               title="Pilih warna">
                        <small class="text-muted">Tombol & Header</small>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase">Warna Sekunder</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="color" 
                               name="secondary_color" 
                               id="secondaryColorInput" 
                               class="form-control form-control-color" 
                               value="{{ $website->secondary_color }}" 
                               title="Pilih warna">
                        <small class="text-muted">Aksen & Ikon</small>
                    </div>
                </div>

                <hr class="my-4">
                <div class="mb-3">
                    <h5 class="fw-bold">Konten Banner</h5>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Judul Utama</label>
                    <input type="text" 
                           name="hero_title" 
                           id="inputHeroTitle"
                           class="form-control" 
                           value="{{ $website->hero_title }}" 
                           placeholder="Selamat Datang...">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Deskripsi Singkat</label>
                    <textarea name="hero_subtitle" 
                              id="inputHeroSubtitle"
                              class="form-control" 
                              rows="2"
                              placeholder="Temukan produk terbaik...">{{ $website->hero_subtitle }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Teks Tombol</label>
                    <input type="text" 
                           name="hero_btn_text" 
                           id="inputHeroBtn"
                           class="form-control" 
                           value="{{ $website->hero_btn_text }}" 
                           placeholder="Belanja Sekarang">
                </div>

                <hr class="my-4">
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                    </button>
                    <a href="{{ route('store.home', $website->subdomain) }}" target="_blank" class="btn btn-outline-secondary">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Lihat Live Website
                    </a>
                </div>
            </form>
            
            @if(session('success'))
                <div class="alert alert-success mt-3 small">
                    {{ session('success') }}
                </div>
            @endif
        </div>

        <div class="col-md-9 bg-light d-flex flex-column align-items-center justify-content-center p-4">
            
            <div class="bg-white rounded-pill shadow-sm px-3 py-2 mb-3 d-flex gap-3">
                <i class="bi bi-laptop text-primary"></i>
                <i class="bi bi-tablet text-muted"></i>
                <i class="bi bi-phone text-muted"></i>
            </div>

            <div class="shadow-lg rounded overflow-hidden bg-white" style="width: 100%; height: 100%; max-width: 1200px; border: 8px solid #333; border-radius: 12px;">
                <iframe src="{{ route('store.home', $website->subdomain) }}" 
                        id="previewFrame"
                        style="width: 100%; height: 100%; border: none;">
                </iframe>
            </div>
        </div>
    </div>
</div>

<script>
    const primaryInput = document.getElementById('primaryColorInput');
    const secondaryInput = document.getElementById('secondaryColorInput');
    const previewFrame = document.getElementById('previewFrame');

    // Fungsi untuk mengirim warna ke dalam Iframe
    function updateColors() {
        // Cek apakah iframe sudah loading
        if (previewFrame.contentWindow) {
            const root = previewFrame.contentWindow.document.documentElement;
            
            // Ubah variabel CSS di dalam iframe secara langsung
            root.style.setProperty('--primary-color', primaryInput.value);
            root.style.setProperty('--secondary-color', secondaryInput.value);
        }
    }

    // Jalankan fungsi saat warna digeser
    primaryInput.addEventListener('input', updateColors);
    secondaryInput.addEventListener('input', updateColors);

    // Jalankan juga saat iframe selesai loading pertama kali
    previewFrame.onload = function() {
        // Opsional: Pastikan iframe sinkron dengan nilai awal
    };
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