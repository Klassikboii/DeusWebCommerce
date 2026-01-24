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
    </style>
</head>
<body>

    <nav class="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-shop me-2"></i> CMS Admin
        </div>

        <div class="d-flex flex-column py-3">
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
            
            <div class="mt-4 px-3">
                <a href="{{ route('client.websites') }}" class="btn btn-outline-secondary w-100 btn-sm">
                    <i class="bi bi-arrow-left"></i> Ganti Website
                </a>
            </div>
        </div>
    </nav>

    <header class="top-navbar">
        <div>
            <h6 class="m-0 fw-bold">{{ $website->site_name }}</h6>
            <small class="text-muted text-uppercase" style="font-size: 11px;">{{ $website->subdomain }}.webcommerce.id</small>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="text-secondary small">Halo, {{ Auth::user()->name }}</span>
            <div class="vr"></div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm text-danger fw-bold">Logout</button>
            </form>
        </div>
    </header>

    <main class="main-content">
        <div class="p-4">
            @yield('content')
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>