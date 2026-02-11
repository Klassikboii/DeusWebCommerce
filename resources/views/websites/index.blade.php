<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Klien - WebCommerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body { background-color: #f8f9fa; }
        .card-website { transition: transform 0.2s; border: 1px solid #e5e7eb; }
        .card-website:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .status-badge { font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 9999px; }
        
        /* Style untuk Pilihan Template */
        .template-option input[type="radio"] { display: none; }
        .template-card { 
            border: 2px solid #eee; 
            cursor: pointer; 
            transition: all 0.2s;
            border-radius: 8px;
            overflow: hidden;
        }
        .template-option input:checked + .template-card {
            border-color: #0d6efd;
            background-color: #f0f7ff;
        }
        .template-img {
            height: 100px;
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-white bg-white border-bottom py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="#">
                <i class="bi bi-shop"></i> WebCommerce Platform
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted d-none d-md-block">Selamat datang, <strong>{{ Auth::user()->name }}</strong></span>
                
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-1"></i> Akun
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="bi bi-gear me-2"></i> Pengaturan Akun
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold m-0">Website Saya</h2>
                <p class="text-muted small">Kelola toko online Anda atau buat yang baru.</p>
            </div>
            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#createWebsiteModal">
                <i class="bi bi-plus-lg"></i> Buat Website Baru
            </button>
        </div>

        @if(session('success'))
            <div class="alert alert-success mb-4 border-0 shadow-sm">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            </div>
        @endif
        
        <div class="row g-4">
            @forelse($websites as $website)
                <div class="col-md-6 col-lg-4">
                    <div class="card card-website h-100 p-3 bg-white rounded-3 shadow-sm">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            @php
                                // LOGIKA PINTAR PEMBUAT URL
                                $port = request()->server('SERVER_PORT') == 8000 ? ':8000' : ''; // Deteksi Port otomatis
                                $protocol = 'http://'; // Localhost biasanya http

                                if ($website->custom_domain) {
                                    // Jika punya domain sendiri (elecjos.com)
                                    $storeUrl = $protocol . $website->custom_domain . $port;
                                } else {
                                    // Jika pakai subdomain bawaan (elecjos.localhost)
                                    // Kita paksa pakai .localhost agar terbaca di sistem host
                                    $storeUrl = $protocol . $website->subdomain . '.localhost' . $port;
                                }
                            @endphp
                            <div>
                                <h5 class="fw-bold mb-1 text-truncate" style="max-width: 200px;">{{ $website->site_name }}</h5>
                                <a href="{{ route('store.home', ['subdomain' => $website->subdomain]) }}" target="_blank" class="text-decoration-none small text-muted">
                                    {{ $website->subdomain }}.webcommerce.id <i class="bi bi-box-arrow-up-right ms-1"></i>
                                </a>
                            </div>
                            <span class="badge bg-light text-dark border">
                                {{ ucfirst($website->active_template) }}
                            </span>
                        </div>
                    
                        <div class="mt-auto pt-3 border-top d-flex gap-2">
                            <a href="{{ route('store.home', ['subdomain' => $website->subdomain]) }}" target="_blank" class="btn btn-light flex-fill border btn-sm py-2">
                                <i class="bi bi-eye"></i> Lihat Toko
                            </a>
                            <a href="{{ route('client.dashboard', $website->id) }}" class="btn btn-primary flex-fill btn-sm py-2">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5 bg-white rounded shadow-sm border">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486747.png" width="80" class="mb-3 opacity-25">
                    <h5 class="text-dark fw-bold">Belum ada website</h5>
                    <p class="text-muted small mb-4">Mulai perjalanan bisnis Anda dengan membuat toko pertama.</p>
                    <button type="button" class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#createWebsiteModal">
                        Buat Toko Sekarang
                    </button>
                </div>
            @endforelse
        </div>
    </div>

    <div class="modal fade" id="createWebsiteModal" tabindex="-1">
        <div class="modal-dialog modal-lg"> {{-- Modal Lebih Lebar (LG) --}}
            <div class="modal-content">
                <form action="{{ route('client.websites.store') }}" method="POST">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">Buat Toko Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-muted">NAMA TOKO</label>
                                    <input type="text" id="siteNameInput" name="site_name" 
                                           class="form-control" placeholder="Contoh: Sepatu Keren Budi" 
                                           value="{{ old('site_name') }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-muted">DOMAIN (ALAMAT WEB)</label>
                                    <div class="input-group has-validation">
                                        <input type="text" id="subdomainInput" name="subdomain" 
                                               class="form-control @error('subdomain') is-invalid @enderror" 
                                               placeholder="sepatukerenbudi" value="{{ old('subdomain') }}" required>
                                        <span class="input-group-text bg-light text-muted small">.webcommerce.id</span>
                                        @error('subdomain')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="text-muted" style="font-size: 11px;">Hanya huruf kecil, angka, dan strip (-).</small>
                                </div>
                            </div>

                            <div class="col-md-6 border-start ps-md-4">
                                <label class="form-label fw-bold small text-muted mb-3">PILIH TAMPILAN (TEMPLATE)</label>
                                
                                <label class="template-option w-100 mb-3">
                                    <input type="radio" name="template" value="simple" checked>
                                    <div class="template-card p-2 d-flex align-items-center gap-3">
                                        <div class="template-img rounded" style="width: 60px; height: 40px; background: #eee;">
                                            <i class="bi bi-layout-text-window-reverse"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">Simple Clean</div>
                                            <div class="small text-muted" style="font-size: 11px;">Minimalis, fokus pada produk.</div>
                                        </div>
                                        <div class="ms-auto"><i class="bi bi-check-circle-fill text-primary d-none checked-icon"></i></div>
                                    </div>
                                </label>

                                <label class="template-option w-100">
                                    <input type="radio" name="template" value="modern">
                                    <div class="template-card p-2 d-flex align-items-center gap-3">
                                        <div class="template-img rounded" style="width: 60px; height: 40px; background: #333; color: white;">
                                            <i class="bi bi-grid-1x2-fill"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">Modern Dark</div>
                                            <div class="small text-muted" style="font-size: 11px;">Elegan, warna kontras tinggi.</div>
                                        </div>
                                    </div>
                                </label>

                            </div>
                        </div>

                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">Buat Toko Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // 1. AUTO-GENERATE DOMAIN
        const nameInput = document.getElementById('siteNameInput');
        const domainInput = document.getElementById('subdomainInput');

        nameInput.addEventListener('input', function() {
            let text = this.value.toLowerCase();
            text = text.replace(/[^a-z0-9]/g, ''); // Hapus spasi & simbol
            domainInput.value = text;
        });

        // 2. RE-OPEN MODAL IF ERROR
        @if($errors->any())
            var myModal = new bootstrap.Modal(document.getElementById('createWebsiteModal'));
            myModal.show();
        @endif
    </script>
</body>
</html>