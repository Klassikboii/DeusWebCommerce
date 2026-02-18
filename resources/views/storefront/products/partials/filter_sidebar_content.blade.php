{{-- resources/views/storefront/products/partials/filter_sidebar_content.blade.php --}}
<div class="card border-0 shadow-sm">
    <div class="card-body">
        {{-- KATEGORI --}}
        <h6 class="fw-bold mb-3">Kategori</h6>
        <div class="d-flex flex-column gap-2 mb-4">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="category" id="cat_all" value="" checked>
                <label class="form-check-label" for="cat_all">Semua</label>
            </div>
            @foreach($categories as $cat)
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="category" id="cat_{{ $cat->id }}" value="{{ $cat->slug }}">
                    <label class="form-check-label" for="cat_{{ $cat->id }}">{{ $cat->name }}</label>
                </div>
            @endforeach
        </div>

        <hr>

        {{-- HARGA --}}
        <h6 class="fw-bold mb-3 mt-4">Harga</h6>
        <div class="row g-2">
            <div class="col-6">
                <input type="number" name="min_price" class="form-control form-control-sm" placeholder="Min">
            </div>
            <div class="col-6">
                <input type="number" name="max_price" class="form-control form-control-sm" placeholder="Max">
            </div>
        </div>
    </div>
</div>