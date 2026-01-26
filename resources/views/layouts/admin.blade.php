<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Super Admin - WebCommerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .admin-sidebar { min-height: 100vh; width: 250px; background: #2c3e50; color: white; position: fixed; }
        .admin-content { margin-left: 250px; padding: 20px; }
        .nav-link { color: rgba(255,255,255,0.8); padding: 12px 20px; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.1); color: white; }
        .nav-link i { margin-right: 10px; }
    </style>
</head>
<body class="bg-light">

    <div class="admin-sidebar d-flex flex-column">
        <div class="p-3 text-center border-bottom border-secondary">
            <h5 class="fw-bold m-0">SUPER ADMIN</h5>
            <small class="text-white-50">WebCommerce Panel</small>
        </div>
        
        <nav class="nav flex-column mt-3 flex-grow-1">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            
            <a href="{{ route('admin.packages.index') }}" class="nav-link {{ request()->routeIs('admin.packages.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i> Paket Harga
            </a>

            <a href="#" class="nav-link">
                <i class="bi bi-people"></i> Users & Website
            </a>

            <a href="#" class="nav-link">
                <i class="bi bi-cash-stack"></i> Transaksi
            </a>
        </nav>

        <div class="p-3 border-top border-secondary">
            <div class="d-flex align-items-center gap-2 mb-3 px-2">
                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div class="small">
                    <div class="fw-bold">{{ Auth::user()->name }}</div>
                    <div class="text-white-50" style="font-size: 11px;">Super Admin</div>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="btn btn-danger btn-sm w-100">Logout</button>
            </form>
        </div>
    </div>

    <div class="admin-content">
        @yield('content')
    </div>

</body>
</html>