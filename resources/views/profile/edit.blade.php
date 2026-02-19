@extends(auth()->user()->role == 'admin' ? 'layouts.admin' : 'layouts.app')

@section('title', 'Pengaturan Akun')

@section('content')
<div class="container-fluid p-0" style="max-width: 600px;">
    <h3 class="fw-bold mb-4">Pengaturan Akun</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                <h6 class="fw-bold text-muted mb-3">Informasi Dasar</h6>
                
                <div class="mb-3">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                </div>

                <hr class="my-4">

                <h6 class="fw-bold text-muted mb-3">Ganti Password</h6>
                <div class="alert alert-info small border-0">
                    <i class="bi bi-info-circle me-1"></i> Kosongkan jika tidak ingin mengubah password.
                </div>

                <div class="mb-3">
                    <label class="form-label">Password Baru</label>
                    <input type="password" name="password" class="form-control" placeholder="Minimal 8 karakter">
                </div>

                <div class="mb-4">
                    <label class="form-label">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password baru">
                </div>
                {{-- resources/views/profile/edit.blade.php --}}

                    @if(auth()->user()->role == 'admin')
                    <div class="card mb-4 shadow-sm border-0">
                        <div class="card-header bg-white">
                            <h5 class="mb-0 fw-bold">Pengaturan Rekening Pembayaran (Khusus Super Admin)</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label">Nama Bank</label>
                                <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', auth()->user()->bank_name) }}" placeholder="Contoh: BCA / Mandiri / BNI">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nomor Rekening</label>
                                <input type="text" name="bank_account_number" class="form-control" value="{{ old('bank_account_number', auth()->user()->bank_account_number) }}" placeholder="Contoh: 1234567890">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Atas Nama</label>
                                <input type="text" name="bank_account_name" class="form-control" value="{{ old('bank_account_name', auth()->user()->bank_account_name) }}" placeholder="Contoh: PT. Deus Web Commerce">
                            </div>
                        </div>
                    </div>
                    @endif

                <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
            </form>
        </div>
    </div>
</div>
@endsection