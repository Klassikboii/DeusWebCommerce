<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Password Baru - WebCommerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #fff; height: 100vh; overflow: hidden; }
        .auth-container { height: 100vh; }
        .auth-sidebar {
            background: linear-gradient(135deg, #6610f2 0%, #6f42c1 100%); /* Ungu */
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 50px;
            position: relative;
        }
        .auth-form { display: flex; align-items: center; justify-content: center; padding: 40px; }
        .form-wrapper { width: 100%; max-width: 400px; }
    </style>
</head>
<body>
    <div class="row g-0 auth-container">
        <div class="col-lg-6 d-none d-lg-flex auth-sidebar">
            <h1 class="display-4 fw-bold mb-4">Mulai Lembaran Baru.</h1>
            <p class="lead opacity-75">Buat password yang kuat untuk mengamankan aset bisnis Anda.</p>
        </div>

        <div class="col-lg-6 auth-form">
            <div class="form-wrapper">
                <div class="mb-4">
                    <h4 class="fw-bold">Buat Password Baru</h4>
                </div>

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Email Address</label>
                        <input type="email" name="email" class="form-control form-control-lg" value="{{ $email ?? old('email') }}" required readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Password Baru</label>
                        <input type="password" name="password" class="form-control form-control-lg @error('password') is-invalid @enderror" required autofocus>
                        @error('password')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="form-control form-control-lg" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">Simpan Password Baru</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>