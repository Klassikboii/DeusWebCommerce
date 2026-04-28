@extends('layouts.' . ($website->active_template ?? 'modern'))

@section('title', 'Edit Profil - ' . $website->site_name)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mb-4">
                <a href="{{ route('store.account') }}" class="text-decoration-none text-muted">
                    <i class="bi bi-arrow-left"></i> Kembali ke Riwayat Pesanan
                </a>
                <h2 class="fw-bold mt-2">Pengaturan Profil</h2>
            </div>

            @if(session('success'))
                <div class="alert alert-success shadow-sm mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                </div>
            @endif

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('store.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Nama Lengkap</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $customer->name) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Alamat Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nomor WhatsApp</label>
                            <input type="number" name="whatsapp" class="form-control" value="{{ old('whatsapp', $customer->whatsapp) }}" required>
                        </div>

                        <hr class="my-4">
                        <h6 class="fw-bold mb-3">Ganti Kata Sandi (Kosongkan jika tidak ingin mengubah)</h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Kata Sandi Baru</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="new_password" class="form-control" placeholder="••••••••">
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#new_password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Konfirmasi Kata Sandi</label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" id="confirm_password" class="form-control" placeholder="••••••••">
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#confirm_password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Gunakan script toggle password yang sudah Anda miliki sebelumnya --}}
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
@endsection