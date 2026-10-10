@extends('layouts.app')

@section('title', 'Browse Products - ShopKart Multi-Vendor Marketplace')

@section('content')
<div class="container">
    <div class="row gy-4">
        <!-- 1. Filters Sidebar -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm p-3 sticky-top" style="top: 90px; z-index: 10;">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h5 class="fw-bold mb-0"><i class="bi bi-funnel"></i> Filters</h5>
                    <a href="{{ route('products.index') }}" class="btn btn-link btn-sm text-decoration-none text-danger p-0">Reset</a>
                </div>

                <form action="{{ route('products.index') }}" method="GET" id="filterForm">
                    @if(request('q'))
                        <input type="hidden" name="q" value="{{ request('q') }}">
                    @endif

                    <!-- Categories -->
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-muted">Category</label>
                        <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Brands -->
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-muted">Brand</label>
                        <select name="brand" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Brands</option>
                            @foreach($brands as $b)
                                <option value="{{ $b->slug }}" {{ request('brand') === $b->slug ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Delivery Speed -->
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-muted">Delivery Speed</label>
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="radio" name="delivery_time" id="dt_all" value="" {{ !request('delivery_time') ? 'checked' : '' }} onchange="this.form.submit()">
                            <label class="form-check-label small" for="dt_all">Anytime</label>
                        </div>
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="radio" name="delivery_time" id="dt_30" value="30min" {{ request('delivery_time') === '30min' ? 'checked' : '' }} onchange="this.form.submit()">
                            <label class="form-check-label small text-warning fw-semibold" for="dt_30"><i class="bi bi-lightning-fill"></i> Under 30 Minutes</label>
                        </div>
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="radio" name="delivery_time" id="dt_60" value="1hour" {{ request('delivery_time') === '1hour' ? 'checked' : '' }} onchange="this.form.submit()">
                            <label class="form-check-label small" for="dt_60">Under 1 Hour</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="delivery_time" id="dt_today" value="today" {{ request('delivery_time') === 'today' ? 'checked' : '' }} onchange="this.form.submit()">
                            <label class="form-check-label small" for="dt_today">Today Delivery</label>
                        </div>
                    </div>

                    <!-- Price Range -->
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-muted">Price Range (₹)</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" name="min_price" class="form-control form-control-sm" placeholder="Min ₹" value="{{ request('min_price') }}">
                            </div>
                            <div class="col-6">
                                <input type="number" name="max_price" class="form-control form-control-sm" placeholder="Max ₹" value="{{ request('max_price') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Customer Rating -->
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-muted">Minimum Rating</label>
                        <select name="rating" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Any Rating</option>
                            <option value="4.5" {{ request('rating') == '4.5' ? 'selected' : '' }}>4.5 ★ & above</option>
                            <option value="4.0" {{ request('rating') == '4.0' ? 'selected' : '' }}>4.0 ★ & above</option>
                            <option value="3.5" {{ request('rating') == '3.5' ? 'selected' : '' }}>3.5 ★ & above</option>
                        </select>
                    </div>

                    <!-- In-Stock only -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="in_stock" value="1" id="inStockCheck" {{ request('in_stock') ? 'checked' : '' }} onchange="this.form.submit()">
                        <label class="form-check-label small fw-semibold" for="inStockCheck">
                            In Stock Only
                        </label>
                    </div>

                    <button type="submit" class="btn btn-sk-primary w-100 btn-sm">Apply Filters</button>
                </form>
            </div>
        </div>

        <!-- 2. Product Catalog Results Grid -->
        <div class="col-lg-9">
            <!-- Results Header & Sorting Bar -->
            <div class="card border-0 shadow-sm p-3 mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <span class="text-muted small">Showing <strong>{{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }}</strong> of <strong>{{ $products->total() }}</strong> products</span>
                        @if(request('q'))
                            <span class="badge bg-light text-dark border ms-2">Keyword: "{{ request('q') }}"</span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label class="small text-muted text-nowrap">Sort By:</label>
                        <select class="form-select form-select-sm" style="width: auto;" onchange="location = this.value;">
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'relevance']) }}" {{ request('sort') == 'relevance' ? 'selected' : '' }}>Relevance</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'best_value']) }}" {{ request('sort') == 'best_value' ? 'selected' : '' }}>★ Best Value</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'fastest_delivery']) }}" {{ request('sort') == 'fastest_delivery' ? 'selected' : '' }}>Fastest Delivery</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_asc']) }}" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_desc']) }}" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'rating']) }}" {{ request('sort') == 'rating' ? 'selected' : '' }}>Highest Customer Rating</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest Arrivals</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Products List -->
            @if($products->isEmpty())
                <div class="card border-0 shadow-sm p-5 text-center">
                    <i class="bi bi-search text-muted display-4 mb-3"></i>
                    <h4>No products found matching your search</h4>
                    <p class="text-muted">Try clearing some filters or searching with different keywords.</p>
                    <div>
                        <a href="{{ route('products.index') }}" class="btn btn-sk-primary btn-sm">Clear All Filters</a>
                    </div>
                </div>
            @else
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3 mb-4">
                    @foreach($products as $product)
                        <div class="col">
                            @include('partials.product-card', ['product' => $product])
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
