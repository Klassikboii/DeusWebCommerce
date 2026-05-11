<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Sedang Tutup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="text-center p-5 bg-white shadow-sm rounded-4" style="max-width: 500px;">
        <i class="bi bi-door-closed text-secondary" style="font-size: 4rem;"></i>
        <h3 class="fw-bold mt-3 text-dark">Toko Sedang Tutup</h3>
        <p class="text-muted mt-3 mb-0">
            Mohon maaf, <strong>{{ $website->site_name }}</strong> saat ini sedang tidak menerima pesanan. Silakan kembali lagi nanti!
        </p>
    </div>
</body>
</html>