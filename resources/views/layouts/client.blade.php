//Ini adalah kode sidebar client.blade.php 

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - {{ $website->site_name ?? 'WebCommerce' }}</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #6366f1; /* Warna Ungu sesuai desain */
        }
        
        body {
            background-color: #f3f4f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: #ffffff;
            border-right: 1px solid #e5e7eb;
            z-index: 1030;
            overflow-y: auto;
        }

        .sidebar-brand {
            height: 64px;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--primary-color);
            border-bottom: 1px solid #f3f4f6;
        }

        .nav-link {
            color: #4b5563;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .nav-link:hover {
            background-color: #f9fafb;
            color: var(--primary-color);
        }

        .nav-link.active {
            background-color: #eef2ff;
            color: var(--primary-color);
            border-left-color: var(--primary-color);
        }

        .nav-group-label {
            padding: 1.5rem 1.5rem 0.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #9ca3af;
            font-weight: 700;
        }
     

        /* Main Content Styling */
        .main-content {
            margin-left: var(--sidebar-width);
            padding-left: 0.5%;
            padding-top: 64px; /* Tinggi navbar */
        }

        /* Navbar Atas */
        .top-navbar {
            height: 64px;
            background: white;
            border-bottom: 1px solid #e5e7eb;
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            z-index: 1020;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        @keyframes pulse-red {
            0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
            70% { box-shadow: 0 0 0 6px rgba(220, 53, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
        }
        .animate-pulse {
            animation: pulse-red 2s infinite;
        }

    

    /* Kondisi Sidebar Tertutup (Toggled) */
    /* GANTI BAGIAN INI */
    .toggled #sidebar {
        margin-left: calc(var(--sidebar-width) * -1); /* Otomatis jadi -260px */
    }
    
    /* Kondisi Konten saat Sidebar Tertutup */
    .toggled .main-content {
        margin-left: 0 !important; /* Konten jadi full width */
        padding-left: 0.5%;
    }
    /* 2. Saat Toggled, paksa Navbar menempel ke kiri (left: 0) */
    .toggled .top-navbar {
        left: 0 !important;
    }
    #sidebar, .main-content, .top-navbar {
        transition: all 0.3s;
    }

    /* (Opsional) Jika ingin sidebar jadi ikon saja (Mini Sidebar),
       logikanya beda sedikit (width jadi 70px), tapi cara di atas 
       adalah cara paling aman untuk layout responsif */
    </style>
</head>
<body>
  <div class="d-flex" id="wrapper">
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <i class="bi bi-shop me-2"></i> CMS Admin
            </div>

            <div class="d-flex flex-column py-3">
                @php
                    // 1. Ambil Subscription Aktif
                    $subscription = isset($website) ? $website->activeSubscription : null;
                    $daysLeft = 0;
                    $isExpiringSoon = false;

                    if ($subscription && $subscription->ends_at) {
                        // Hitung sisa hari (Carbon instance)
                        // false = agar hasilnya negatif jika sudah lewat (meski middleware sudah memblokir)
                        $daysLeft = now()->diffInDays($subscription->ends_at, false);
                        
                        // Tentukan batas "Segera Habis" (misal: 3 hari terakhir)
                        if ($daysLeft >= 0 && $daysLeft <= 3) {
                            $isExpiringSoon = true;
                        }
                    }
                    $daysLeftvisual = ceil($daysLeft);
                @endphp

                @if($isExpiringSoon)
                    <div class="alert alert-warning border-warning shadow-sm d-flex align-items-center mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                        <div>
                            <h6 class="fw-bold mb-0">Masa Aktif Segera Berakhir!</h6>
                            <div class="small">
                                Paket <strong>{{ $subscription->package->name }}</strong> Anda tersisa 
                                <span class="badge bg-dark">{{ $daysLeftvisual }} Hari</span> lagi. 
                                <a href="{{ route('client.billing.index', $website->id) }}" class="fw-bold text-dark text-decoration-underline">Perpanjang Sekarang</a> agar layanan tidak terputus.
                            </div>
                        </div>
                    </div>
                @endif
                <a href="{{ route('client.dashboard', $website->id) }}" class="nav-link {{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2"></i> Dashboard
                </a>

                <div class="nav-group-label">Website Builder</div>
                <a href="{{ route('client.builder.index', $website->id) }}" 
                class="nav-link {{ request()->routeIs('client.builder.*') ? 'active' : '' }}">
                    <i class="bi bi-laptop"></i> Editor Website
                </a>
                <a href="{{ route('client.templates.index', $website->id) }}" 
                class="nav-link {{ request()->routeIs('client.templates.*') ? 'active' : '' }}">
                    <i class="bi bi-layout-text-window-reverse"></i> Template
                </a>
                <a href="{{ route('client.appearance.index', $website->id) }}" 
                class="nav-link {{ request()->routeIs('client.appearance.*') ? 'active' : '' }}">
                    <i class="bi bi-menu-button-wide"></i> Menu / Navigasi
                </a> 
                <a href="{{ route('client.seo.index', $website->id) }}" 
                class="nav-link {{ request()->routeIs('client.seo.*') ? 'active' : '' }}">
                    <i class="bi bi-search"></i> SEO & Meta
                </a>
                <a href="{{ route('client.domains.index', $website->id) }}" 
                class="nav-link {{ request()->routeIs('client.domains.*') ? 'active' : '' }}">
                    <i class="bi bi-globe"></i> Domain
                </a>
            

                <div class="nav-group-label">Produk & Konten</div>
                <a href="{{ route('client.products.index', $website->id) }}" 
                class="nav-link {{ request()->routeIs('client.products.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i> Semua Produk
                </a>
                <a href="{{ route('client.categories.index', $website->id) }}" 
                class="nav-link {{ request()->routeIs('client.categories.*') ? 'active' : '' }}">
                    <i class="bi bi-tags"></i> Kategori
                </a>
                <a href="{{ route('client.posts.index', $website->id) }}" 
                class="nav-link {{ request()->routeIs('client.posts.*') ? 'active' : '' }}">
                    <i class="bi bi-journal-text"></i> Blog / Artikel
                </a>

                <div class="nav-group-label">Penjualan</div>
                <a href="{{ route('client.orders.index', $website->id) }}" 
                class="nav-link {{ request()->routeIs('client.orders.*') ? 'active' : '' }}">
                    <i class="bi bi-cart"></i> Order Masuk
                </a>
                <a href="{{ route('client.customers.index', $website->id) }}" 
                class="nav-link {{ request()->routeIs('client.customers.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Pelanggan
                </a>
                <a href="{{ route('client.reports.index', $website->id) }}" 
                class="nav-link {{ request()->routeIs('client.reports.*') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart"></i> Laporan
                </a>

                <div class="nav-group-label">Pengaturan</div>
                <a href="{{ route('client.settings.index', $website->id) }}" 
                class="nav-link {{ request()->routeIs('client.settings.*') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i> Settings
                </a>
                <a href="{{ route('client.billing.index', $website->id) }}" 
                class="nav-link {{ request()->routeIs('client.billing.*') ? 'active' : '' }}">
                    <i class="bi bi-credit-card"></i> Langganan (Billing)
                </a>
                @if($subscription && $subscription->ends_at)
            <div class="mt-auto p-3">
                <div class="card border-0 bg-dark text-white shadow-sm" style="background: linear-gradient(45deg, #212529, #343a40);">
                    <div class="card-body p-3">
                        <small class="text-white-50 text-uppercase fw-bold" style="font-size: 10px;">Masa Aktif</small>
                        
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="fw-bold">{{ $subscription->package->name }}</span>
                            @if($daysLeft > 3)
                                <span class="badge bg-success rounded-pill">{{ $daysLeftvisual }} Hari</span>
                            @elseif($daysLeft >= 0)
                                <span class="badge bg-danger rounded-pill animate-pulse">{{ $daysLeftvisual }} Hari</span>
                            @else
                                <span class="badge bg-secondary">Expired</span>
                            @endif
                        </div>

                        @php
                            // Hitung persentase durasi terpakai
                            // Total durasi paket (misal 14 hari)
                            $totalDays = $subscription->package->duration_days ?? 30; 
                            $percentLeft = ($daysLeft / $totalDays) * 100;
                            // Batasi min 0 max 100
                            $percentLeft = max(0, min(100, $percentLeft));
                        @endphp

                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar {{ $daysLeft <= 3 ? 'bg-danger' : 'bg-primary' }}" 
                                role="progressbar" 
                                style="width: {{ $percentLeft }}%">
                            </div>
                        </div>

                        <div class="mt-2 text-center">
                            <a href="{{ route('client.billing.index', $website->id) }}" class="btn btn-sm btn-light w-100 py-1" style="font-size: 11px;">
                                Upgrade / Perpanjang
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
                
                <div class="mt-4 px-3">
                    <a href="{{ route('client.websites') }}" class="btn btn-outline-secondary w-100 btn-sm">
                        <i class="bi bi-arrow-left"></i> Ganti Website
                    </a>
                </div>
            </div>
        </nav>
        </div>

   <header class="top-navbar navbar-expand-lg navbar-light bg-light border-bottom px-4" id="header">
    
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-light border btn-sm shadow-sm" id="sidebarToggle" style="width: 32px; height: 32px; padding: 0;">
                <i class="bi bi-list fs-6"></i>
            </button>

            <div class="d-flex flex-column justify-content-center" style="line-height: 1.2;">
                <h6 class="m-0 fw-bold text-dark">{{ $website->site_name }}</h6>
                <small class="text-muted text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">
                    {{ $website->custom_domain ?? $website->subdomain . ".webcommerce.id"}}
                </small>
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            
            <div class="d-none d-md-flex align-items-center gap-2 text-secondary">
                <div class="bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <i class="bi bi-person-fill"></i>
                </div>
                <small class="fw-medium">{{ Auth::user()->name }}</small>
            </div>

            <div class="vr d-none d-md-block mx-1" style="height: 20px;"></div>
            @php
    // Deteksi URL admin (localhost:8000) secara manual
    $adminUrl = env('APP_URL'); 
    
    // Tambah port 8000 jika di local (karena env biasanya cuma localhost tanpa port)
    if (request()->server('SERVER_PORT') == '8000' && !str_contains($adminUrl, ':8000')) {
        $adminUrl .= ':8000';
    }
    
    // Pastikan ada http
    if (!str_starts_with($adminUrl, 'http')) {
        $adminUrl = 'http://' . $adminUrl;
    }
@endphp
            
            <form action="{{ route('logout') }}" method="POST" class="m-0 p-0 d-flex align-items-center">
                @csrf
                <button type="submit" class="btn btn-sm btn-light text-danger fw-bold border-0 d-flex align-items-center gap-2 px-2">
                    <i class="bi bi-box-arrow-right"></i> 
                    <span>Logout</span>
                </button>
            </form>
        </div>

    </header>

    <div class="main-content me-2 mt-2 ">
                @yield('content')
            </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function(event) {
        const toggleButton = document.getElementById('sidebarToggle');
        const body = document.body;

        // 1. Cek apakah user pernah menutup sidebar sebelumnya?
        const isClosed = localStorage.getItem('sidebar-closed') === 'true';
        if (isClosed) {
            body.classList.add('toggled');
        }

        // 2. Fungsi Klik Tombol
        toggleButton.addEventListener('click', function(e) {
            e.preventDefault();
            body.classList.toggle('toggled');

            // 3. Simpan status ke memori browser
            if (body.classList.contains('toggled')) {
                localStorage.setItem('sidebar-closed', 'true');
            } else {
                localStorage.setItem('sidebar-closed', 'false');
            }
        });
    });
</script>
</body>
</html>