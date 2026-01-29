<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar - WebCommerce Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #fff; height: 100vh; overflow: hidden; }
        .auth-container { height: 100vh; }
        .auth-sidebar {
            background: linear-gradient(135deg, #198754 0%, #157347 100%); /* Warna beda dikit biar fresh (Hijau) */
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 50px;
            position: relative;
            overflow: hidden;
        }
        .auth-sidebar::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: url('https://source.unsplash.com/1600x900/?startup,meeting') center/cover;
            opacity: 0.1;
        }
        .auth-form {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            overflow-y: auto; /* Scroll kalau form kepanjangan di layar kecil */
        }
        .form-wrapper { width: 100%; max-width: 400px; }
    </style>
</head>
<body>

    <div class="row g-0 auth-container">
        <div class="col-lg-6 d-none d-lg-flex auth-sidebar">
            <div style="position: relative; z-index: 2;">
                <h1 class="display-4 fw-bold mb-4">Mulai Perjalanan Bisnis Anda.</h1>
                <p class="lead opacity-75">Hanya butuh 2 menit untuk membuat toko online profesional Anda sendiri.</p>
                <ul class="list-unstyled mt-4 fs-5">
                    <li class="mb-2"><i class="bi bi-check-circle-fill me-2"></i> Free Trial 14 Hari</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill me-2"></i> Tanpa Kartu Kredit</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill me-2"></i> Setup Instan</li>
                </ul>
            </div>
        </div>

        <div class="col-lg-6 auth-form">
            <div class="form-wrapper">
                <div class="mb-4">
                    <h3 class="fw-bold text-success"><i class="bi bi-shop me-2"></i>WebCommerce</h3>
                    <h4 class="fw-bold mt-4">Buat Akun Baru</h4>
                    <p class="text-muted">Lengkapi data diri Anda untuk memulai.</p>
                </div>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control form-control-lg @error('name') is-invalid @enderror" value="{{ old('name') }}" required autofocus placeholder="John Doe">
                        @error('name')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Email Address</label>
                        <input type="email" name="email" class="form-control form-control-lg @error('email') is-invalid @enderror" value="{{ old('email') }}" required placeholder="nama@email.com">
                        @error('email')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Password</label>
                        <input type="password" name="password" class="form-control form-control-lg @error('password') is-invalid @enderror" required placeholder="Minimal 8 karakter">
                        @error('password')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase text-muted">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="form-control form-control-lg" required placeholder="Ulangi password">
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100 mb-3">Daftar Sekarang</button>

                    <div class="text-center mt-4">
                        <p class="text-muted">Sudah punya akun? 
                            <a href="{{ route('login') }}" class="fw-bold text-success text-decoration-none">Login Disini</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>