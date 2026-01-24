@extends('layouts.client')

@section('title', 'SEO & Meta Data')

@section('content')
<div class="container-fluid p-0" style="max-width: 800px;">
    <div class="mb-4">
        <h4 class="fw-bold mb-1">SEO & Meta Data</h4>
        <p class="text-muted m-0">Atur bagaimana toko Anda muncul di pencarian Google dan Social Media.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('client.seo.update', $website->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Meta Title (Judul Halaman)</label>
                            <input type="text" name="meta_title" id="metaTitle" class="form-control" 
                                   value="{{ $website->meta_title }}" 
                                   placeholder="{{ $website->site_name }} - Toko Online Terbaik">
                            <div class="form-text">Maksimal 70 karakter.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Meta Description</label>
                            <textarea name="meta_description" id="metaDesc" class="form-control" rows="3" 
                                      placeholder="Jelaskan toko Anda dalam satu kalimat menarik...">{{ $website->meta_description }}</textarea>
                            <div class="form-text">Maksimal 160 karakter. Deskripsi ini muncul di bawah judul di Google.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Keywords (Opsional)</label>
                            <input type="text" name="meta_keywords" class="form-control" 
                                   value="{{ $website->meta_keywords }}" 
                                   placeholder="elektronik, murah, surabaya, gadget">
                            <div class="form-text">Pisahkan dengan koma.</div>
                        </div>

                        <button type="submit" class="btn btn-primary px-4">Simpan SEO</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h6 class="fw-bold mb-3 text-muted">Google Search Preview</h6>
                    
                    <div class="bg-white p-3 rounded border">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <div class="bg-light rounded-circle border d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                                <img src="{{ $website->favicon ? asset('storage/'.$website->favicon) : 'https://www.google.com/favicon.ico' }}" width="14">
                            </div>
                            <span class="small text-muted">{{ $website->custom_domain ?? $website->subdomain . '.webcommerce.id' }}</span>
                        </div>
                        <h5 class="text-primary mb-1 text-truncate" style="font-size: 18px; cursor: pointer;" id="previewTitle">
                            {{ $website->meta_title ?? $website->site_name }}
                        </h5>
                        <p class="small text-muted m-0" style="line-height: 1.4;" id="previewDesc">
                            {{ $website->meta_description ?? 'Deskripsi toko Anda akan muncul di sini...' }}
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('metaTitle').addEventListener('input', function() {
        document.getElementById('previewTitle').innerText = this.value || '{{ $website->site_name }}';
    });
    document.getElementById('metaDesc').addEventListener('input', function() {
        document.getElementById('previewDesc').innerText = this.value || 'Deskripsi toko Anda akan muncul di sini...';
    });
</script>
@endsection