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
        <div class="mb-5">
            <h2 class="fw-bold text-dark">Dashboard</h2>
            <p class="text-muted">Kelola semua toko online Anda di satu tempat.</p>

            <div class="row g-4 mt-2">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm p-3">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted small mb-1">Total Websites</p>
                                <h4 class="fw-bold mb-0">{{ $websites->count() }}</h4>
                            </div>
                            <i class="bi bi-globe text-primary h4"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm p-3">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted small mb-1">Total Orders</p>
                                <h4 class="fw-bold mb-0">0</h4> </div>
                            <i class="bi bi-cart text-secondary h4"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm p-3">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted small mb-1">Total Revenue</p>
                                <h4 class="fw-bold mb-0">Rp 0</h4> </div>
                            <i class="bi bi-graph-up-arrow text-secondary h4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold m-0">Website Saya</h4>
            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#createWebsiteModal">
                <i class="bi bi-plus-lg"></i> Buat Website Baru
            </button>
        </div>

        @if(session('success'))
            <div class="alert alert-success mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-4">
            @forelse($websites as $website)
                <div class="col-md-6 col-lg-4">
                    <div class="card card-website h-100 p-3 bg-white rounded-3">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-bold mb-1">{{ $website->site_name }}</h5>
                                <a href="http://{{ $website->subdomain }}.localhost:8000" target="_blank" class="text-decoration-none small text-muted">
                                    {{ $website->subdomain }}.webcommerce.id <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </div>
                            <span class="status-badge bg-success text-white">
                                {{ ucfirst($website->status) }}
                            </span>
                        </div>
                        
                        <div class="mt-auto pt-3 border-top d-flex gap-2">
                            <a href="{{ route('store.home', $website->subdomain) }}" class="btn btn-light flex-fill border">
                                <i class="bi bi-eye"></i> Lihat
                            </a>
                            <a href="{{ route('client.dashboard', $website->id) }}" class="btn btn-outline-dark flex-fill">
                                <i class="bi bi-gear"></i> Kelola
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486747.png" width="100" class="mb-3 opacity-50">
                    <h5 class="text-muted">Belum ada website</h5>
                    <p class="text-muted small">Mulai perjalanan bisnis Anda dengan membuat toko pertama.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="modal fade" id="createWebsiteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('client.websites.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Buat Toko Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Toko</label>
                            <input type="text" 
                                   id="siteNameInput"
                                   name="site_name" 
                                   class="form-control" 
                                   placeholder="Contoh: Sepatu Keren Budi" 
                                   value="{{ old('site_name') }}" 
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subdomain (Alamat Web)</label>
                            <div class="input-group has-validation">
                                <input type="text" 
                                       id="subdomainInput"
                                       name="subdomain" 
                                       class="form-control @error('subdomain') is-invalid @enderror" 
                                       placeholder="sepatukerenbudi" 
                                       value="{{ old('subdomain') }}" 
                                       required>
                                <span class="input-group-text bg-light">.webcommerce.id</span>
                                
                                @error('subdomain')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <small class="text-muted">Domain akan terisi otomatis, tapi Anda bisa mengubahnya.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Buat Website</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // 1. FITUR AUTO-GENERATE DOMAIN
        const nameInput = document.getElementById('siteNameInput');
        const domainInput = document.getElementById('subdomainInput');

        nameInput.addEventListener('input', function() {
            // Ambil value nama toko
            let text = this.value;
            
            // Ubah jadi huruf kecil semua
            text = text.toLowerCase();
            
            // Hapus semua karakter SELAIN huruf dan angka (termasuk spasi dihapus)
            // Jika ingin spasi jadi strip, ganti replace di bawah
            text = text.replace(/[^a-z0-9]/g, ''); 
            
            // Isi ke kolom domain
            domainInput.value = text;
        });

        // 2. FITUR BUKA MODAL OTOMATIS JIKA ADA ERROR
        // Jika Laravel mendeteksi error validasi (misal domain kembar), 
        // halaman akan refresh dan kode ini akan membuka modalnya lagi secara otomatis.
        @if($errors->any())
            var myModal = new bootstrap.Modal(document.getElementById('createWebsiteModal'));
            myModal.show();
        @endif
    </script>
</body>
</html>