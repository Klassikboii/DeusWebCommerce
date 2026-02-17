<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('store.products', $website->subdomain) }}" method="GET">
            {{-- Keep search & sort hidden --}}
            @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
            @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif

            {{-- 1. KATEGORI --}}
            <h6 class="fw-bold mb-3">Kategori</h6>
            <div class="d-flex flex-column gap-2 mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="category" id="cat_all" value="" {{ !request('category') ? 'checked' : '' }} onchange="this.form.submit()">
                    <label class="form-check-label" for="cat_all">Semua Kategori</label>
                </div>
                @foreach($categories as $cat)
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="category" id="cat_{{ $cat->id }}" value="{{ $cat->slug }}" {{ request('category') == $cat->slug ? 'checked' : '' }} onchange="this.form.submit()">
                        <label class="form-check-label" for="cat_{{ $cat->id }}">{{ $cat->name }}</label>
                    </div>
                @endforeach
            </div>

            <hr>

            {{-- 2. HARGA --}}
            <h6 class="fw-bold mb-3 mt-4">Range Harga</h6>
            <div class="row g-2 mb-2">
                <div class="col-6">
                    <input type="number" name="min_price" class="form-control form-control-sm" placeholder="Min" value="{{ request('min_price') }}">
                </div>
                <div class="col-6">
                    <input type="number" name="max_price" class="form-control form-control-sm" placeholder="Max" value="{{ request('max_price') }}">
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm w-100">Terapkan</button>
            
            @if(request()->anyFilled(['search', 'category', 'min_price', 'max_price']))
                <a href="{{ route('store.products', $website->subdomain) }}" class="btn btn-outline-secondary btn-sm w-100 mt-2">Reset</a>
            @endif
        </form>
    </div>
</div>