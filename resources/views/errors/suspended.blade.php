<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Tidak Aktif</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="text-center p-5 bg-white shadow-sm rounded-4" style="max-width: 500px;">
        <i class="bi bi-shop text-secondary opacity-50" style="font-size: 4rem;"></i>
        <h3 class="fw-bold mt-3 text-dark">Halaman Tidak Tersedia</h3>
        <p class="text-muted mt-3 mb-0">
            Mohon maaf, halaman toko <strong>{{ $website->site_name }}</strong> saat ini sedang dalam pemeliharaan atau tidak dapat diakses untuk sementara waktu.
        </p>
        <div class="mt-4 pt-4 border-top">
            <p class="small text-muted mb-0">
                Silakan kembali lagi nanti atau hubungi pihak toko untuk informasi lebih lanjut.
            </p>
        </div>
    </div>
</body>
</html>