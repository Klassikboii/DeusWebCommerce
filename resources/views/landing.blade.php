<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebCommerce - Buat Toko Online Impianmu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .hero-section { background: linear-gradient(135deg, #0d6efd 0%, #0099ff 100%); color: white; padding: 100px 0; }
        .feature-icon { width: 60px; height: 60px; background: #e7f1ff; color: #0d6efd; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 24px; margin-bottom: 20px; }
        .pricing-card { transition: transform 0.3s; border: none; }
        .pricing-card:hover { transform: translateY(-5px); }
        .pricing-card.popular { border: 2px solid #0d6efd; position: relative; }
        .popular-badge { position: absolute; top: -15px; left: 50%; transform: translateX(-50%); background: #0d6efd; color: white; padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="#"><i class="bi bi-shop me-2"></i>WebCommerce</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto gap-2">
                    <li class="nav-item"><a class="nav-link" href="#features">Fitur</a></li>
                    <li class="nav-item"><a class="nav-link" href="#pricing">Harga</a></li>
                    <li class="nav-item ms-3">
                        <a href="{{ route('login') }}" class="btn btn-outline-primary px-4">Login</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('register') }}" class="btn btn-primary px-4">Daftar Sekarang</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Buat Toko Online Profesional<br>Dalam Hitungan Menit</h1>
            <p class="lead mb-5 opacity-75">Tanpa perlu coding. Tanpa biaya mahal. Kelola produk, pesanan, <br>dan pelanggan Anda di satu tempat yang mudah.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="{{ route('register') }}" class="btn btn-light btn-lg text-primary fw-bold px-5 shadow">Mulai Gratis</a>
                <a href="#features" class="btn btn-outline-light btn-lg px-5">Pelajari Dulu</a>
            </div>
            <img src="https://via.placeholder.com/800x400/ffffff/cccccc?text=Dashboard+Preview" class="img-fluid rounded shadow-lg mt-5" alt="Dashboard Preview" style="opacity: 0.9;">
        </div>
    </section>

    <section id="features" class="py-5">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Kenapa Memilih WebCommerce?</h2>
                <p class="text-muted">Semua alat yang Anda butuhkan untuk berjualan online.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-center p-4">
                        <div class="feature-icon mx-auto"><i class="bi bi-brush"></i></div>
                        <h5>Website Builder Mudah</h5>
                        <p class="text-muted small">Ubah warna, font, dan tampilan toko Anda semudah drag-and-drop. Tidak perlu skill desain.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-4">
                        <div class="feature-icon mx-auto"><i class="bi bi-phone"></i></div>
                        <h5>Mobile Friendly</h5>
                        <p class="text-muted small">Toko Anda otomatis terlihat bagus di HP, Tablet, dan Desktop. Jangkau pelanggan di mana saja.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-4">
                        <div class="feature-icon mx-auto"><i class="bi bi-google"></i></div>
                        <h5>SEO Optimized</h5>
                        <p class="text-muted small">Fitur SEO bawaan membantu toko Anda muncul di halaman pertama pencarian Google.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="pricing" class="py-5 bg-light">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Pilih Paket Sesuai Kebutuhan</h2>
                <p class="text-muted">Mulai dari gratis, upgrade kapan saja saat bisnis Anda tumbuh.</p>
            </div>
            <div class="row justify-content-center g-4">
                @foreach($packages as $pkg)
                <div class="col-md-4">
                    <div class="card pricing-card h-100 shadow-sm {{ $pkg->price > 0 ? 'popular' : '' }}">
                        @if($pkg->price > 0)
                            <div class="popular-badge">PALING LARIS</div>
                        @endif
                        <div class="card-body p-4 text-center">
                            <h5 class="fw-bold mb-3">{{ $pkg->name }}</h5>
                            <h2 class="fw-bold mb-3">
                                @if($pkg->price == 0)
                                    Gratis
                                @else
                                    Rp {{ number_format($pkg->price, 0, ',', '.') }}<small class="fs-6 text-muted">/bln</small>
                                @endif
                            </h2>
                            <ul class="list-unstyled mb-4 text-start mx-auto" style="max-width: 200px;">
                                <li class="mb-2"><i class="bi bi-check-lg text-success me-2"></i> Max <strong>{{ $pkg->max_products }}</strong> Produk</li>
                                <li class="mb-2">
                                    @if($pkg->can_custom_domain)
                                        <i class="bi bi-check-lg text-success me-2"></i> Custom Domain
                                    @else
                                        <i class="bi bi-x-lg text-muted me-2"></i> Subdomain Only
                                    @endif
                                </li>
                                <li class="mb-2"><i class="bi bi-check-lg text-success me-2"></i> 24/7 Support</li>
                            </ul>
                            <a href="{{ route('register') }}" class="btn {{ $pkg->price > 0 ? 'btn-primary' : 'btn-outline-primary' }} w-100 rounded-pill">
                                Pilih {{ $pkg->name }}
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white py-4 text-center">
        <div class="container">
            <small>&copy; {{ date('Y') }} PT WebCommerce Indonesia. All rights reserved.</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>