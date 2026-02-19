<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - WebCommerce Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #fff; height: 100vh; overflow: hidden; }
        .auth-container { height: 100vh; }
        .auth-sidebar {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
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
            background: url('https://source.unsplash.com/1600x900/?office,technology') center/cover;
            opacity: 0.1;
        }
        .auth-form {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }
        .form-wrapper { width: 100%; max-width: 400px; }
        .btn-google { border: 1px solid #ddd; background: white; color: #333; }
        .btn-google:hover { background: #f8f9fa; }
    </style>
</head>
<body>

    <div class="row g-0 auth-container">
        <div class="col-lg-6 d-none d-lg-flex auth-sidebar">
            <div style="position: relative; z-index: 2;">
                <h1 class="display-4 fw-bold mb-4">Kelola Bisnis Online Anda dengan Mudah.</h1>
                <p class="lead opacity-75">Bergabung dengan ribuan pengusaha yang telah sukses membangun toko online mereka bersama WebCommerce.</p>
            </div>
        </div>

        <div class="col-lg-6 auth-form">
            <div class="form-wrapper">
                <div class="mb-5">
                    <h3 class="fw-bold text-primary"><i class="bi bi-shop me-2"></i>WebCommerce</h3>
                    <h4 class="fw-bold mt-4">Selamat Datang Kembali!</h4>
                    <p class="text-muted">Silakan masuk untuk mengelola toko Anda.</p>
                </div>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Email Address</label>
                        <input type="email" name="email" class="form-control form-control-lg @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus placeholder="nama@email.com">
                        @error('email')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <label class="form-label small fw-bold text-uppercase text-muted">Password</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="small text-decoration-none">Lupa Password?</a>
                            @endif
                        </div>
                        <input type="password" name="password" class="form-control form-control-lg @error('password') is-invalid @enderror" required placeholder="••••••••">
                        @error('password')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    

                    <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">Masuk ke Dashboard</button>

                    <div class="text-center mt-4">
                        <p class="text-muted">Belum punya akun? 
                            <a href="{{ route('register') }}" class="fw-bold text-primary text-decoration-none">Daftar Sekarang</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>