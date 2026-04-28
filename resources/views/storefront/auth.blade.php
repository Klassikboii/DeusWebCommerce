@extends('layouts.' . ($website->active_template ?? 'modern'))

@section('title', 'Masuk atau Daftar - ' . $website->site_name)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">

            {{-- 1. AREA NOTIFIKASI ERROR/SUCCESS --}}
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger shadow-sm mb-4">
                    <ul class="mb-0 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- 2. KOTAK FORM --}}
            <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                <div class="card-header bg-white p-0 border-bottom">
                    <ul class="nav nav-tabs nav-justified" id="authTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold py-3 text-dark border-0 border-bottom border-primary border-3" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab" aria-selected="true">Masuk Akun</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold py-3 text-muted border-0" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab" aria-selected="false">Daftar Baru</button>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body p-4 p-md-5">
                    <div class="tab-content" id="authTabContent">
                        
                        {{-- ================= TAB LOGIN ================= --}}
                        <div class="tab-pane fade show active" id="login" role="tabpanel">
                            <div class="text-center mb-4">
                                <h5 class="fw-bold">Selamat Datang Kembali!</h5>
                                <p class="text-muted small">Silakan masuk untuk melacak status pesanan Anda.</p>
                            </div>
                            <form action="{{ route('store.login.submit') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Alamat Email</label>
                                    <input type="email" name="email" class="form-control bg-light" value="{{ old('email') }}" placeholder="email@contoh.com" required>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label small fw-bold">Kata Sandi</label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="password" class="form-control" required placeholder="••••••••">
                                        {{-- Tombol Toggle --}}
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#password" style="border-radius: 0 var(--radius-base) var(--radius-base) 0;">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
                                </button>
                            </form>
                        </div>

                        {{-- ================= TAB DAFTAR ================= --}}
                        <div class="tab-pane fade" id="register" role="tabpanel">
                            <div class="text-center mb-4">
                                <h5 class="fw-bold">Buat Akun Pelanggan</h5>
                                <p class="text-muted small">Daftar untuk kemudahan belanja dan riwayat transaksi.</p>
                            </div>
                            <form action="{{ route('store.register.submit') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Nama Lengkap</label>
                                    <input type="text" name="name" class="form-control bg-light" value="{{ old('name') }}" placeholder="Contoh: Budi Santoso" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Alamat Email</label>
                                    <input type="email" name="email" class="form-control bg-light" value="{{ old('email') }}" placeholder="email@contoh.com" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Nomor WhatsApp</label>
                                    <input type="number" name="whatsapp" class="form-control bg-light" value="{{ old('whatsapp') }}" placeholder="Contoh: 08123456789" required>
                                    {{-- Info Sihir Skema A --}}
                                    <div class="form-text mt-2 text-primary" style="font-size: 0.75rem; background: #e9ecef; padding: 6px; border-radius: 4px;">
                                        <i class="bi bi-magic me-1"></i> <strong>Penting:</strong> Gunakan nomor WhatsApp yang sama dengan pesanan lama Anda agar riwayat otomatis tersambung!
                                    </div>
                                </div>
                                <div class="mb-3">
                                        <label class="form-label small fw-bold">Kata Sandi Baru</label>
                                        <div class="input-group">
                                            {{-- Ubah ID jadi register_password --}}
                                            <input type="password" name="password" id="register_password" class="form-control" required placeholder="••••••••">
                                            {{-- Ubah data-target ke #register_password --}}
                                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#register_password" style="border-radius: 0 var(--radius-base) var(--radius-base) 0;">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                <div class="mb-4">
                                        <label class="form-label small fw-bold">Ulangi Kata Sandi</label>
                                        <div class="input-group">
                                            {{-- Ubah ID jadi register_password_confirmation --}}
                                            <input type="password" name="password_confirmation" id="register_password_confirmation" class="form-control" required placeholder="Ketik Ulang Kata Sandi...">
                                            {{-- Ubah data-target ke #register_password_confirmation --}}
                                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#register_password_confirmation" style="border-radius: 0 var(--radius-base) var(--radius-base) 0;">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                <button type="submit" class="btn btn-dark w-100 py-2 fw-bold shadow-sm">
                                    <i class="bi bi-person-plus me-1"></i> Daftar Sekarang
                                </button>
                            </form>
                        </div>
                        
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Cari semua tombol yang punya class 'toggle-password'
    const toggleButtons = document.querySelectorAll('.toggle-password');

    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Ambil elemen input target berdasarkan data-target
            const targetId = this.getAttribute('data-target');
            const inputField = document.querySelector(targetId);
            const icon = this.querySelector('i');

            if (!inputField) return;

            // Logika Toggle Type & Icon
            if (inputField.type === 'password') {
                inputField.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                inputField.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    });
});
</script>
{{-- Script ringan agar garis biru navigasi tab ikut bergeser saat diklik --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    var triggerTabList = [].slice.call(document.querySelectorAll('#authTab button'))
    triggerTabList.forEach(function (triggerEl) {
        triggerEl.addEventListener('click', function () {
            // Hapus style border bawah dari semua tab
            triggerTabList.forEach(btn => {
                btn.classList.remove('border-bottom', 'border-primary', 'border-3', 'text-dark');
                btn.classList.add('text-muted');
            });
            // Tambahkan style ke tab yang aktif
            this.classList.remove('text-muted');
            this.classList.add('border-bottom', 'border-primary', 'border-3', 'text-dark');
        })
    })
});
</script>


@endsection