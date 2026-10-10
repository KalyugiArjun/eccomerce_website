@extends('layouts.app')

@section('title', $seller->store_name . ' - Verified Store on ShopKart')

@section('content')
<div class="container">
    <!-- Store Header Card & Banner -->
    <div class="card border-0 shadow-sm overflow-hidden mb-4">
        <div style="height: 180px; background: url('{{ $seller->store_banner ?: 'https://images.unsplash.com/photo-1472851294608-062f824d29cc?w=1200&h=300&fit=crop' }}') center/cover no-repeat; position: relative;">
            <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);"></div>
        </div>

        <div class="card-body p-4 pt-0 position-relative" style="margin-top: -50px;">
            <div class="row align-items-end gy-3">
                <div class="col-auto">
                    <img src="{{ $seller->store_logo ?: 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=120&h=120&fit=crop' }}" class="rounded-circle border border-4 border-white shadow" alt="{{ $seller->store_name }}" style="width: 100px; height: 100px; object-fit: cover;">
                </div>
                <div class="col">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <h2 class="fw-bold mb-0 text-dark">{{ $seller->store_name }}</h2>
                        <span class="badge bg-success"><i class="bi bi-patch-check-fill"></i> Verified Merchant</span>
                    </div>
                    <p class="text-muted mb-2">{{ $seller->store_tagline }}</p>
                    <div class="d-flex flex-wrap align-items-center gap-3 small text-muted">
                        <span><i class="bi bi-geo-alt-fill text-danger"></i> {{ $seller->city }}, {{ $seller->state }}</span>
                        <span>&bull;</span>
                        <span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> {{ $seller->rating }} ★ ({{ $seller->total_reviews }} ratings)</span>
                        <span>&bull;</span>
                        <span class="sk-trust-badge"><i class="bi bi-shield-check"></i> {{ $seller->trust_score }}% Positive Score</span>
                        <span>&bull;</span>
                        <span class="text-success fw-bold"><i class="bi bi-lightning-fill"></i> {{ $seller->estimated_delivery_minutes }} min local delivery</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Store Info & Products Tabs -->
    <div class="row gy-4">
        <!-- Products Grid -->
        <div class="col-lg-8">
            <h4 class="fw-bold mb-3"><i class="bi bi-grid text-primary me-2"></i> Products by {{ $seller->store_name }} ({{ $products->total() }})</h4>

            @if($products->isEmpty())
                <div class="card border-0 shadow-sm p-5 text-center">
                    <p class="text-muted">No products currently in stock from this store.</p>
                </div>
            @else
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3 mb-4">
                    @foreach($products as $product)
                        <div class="col">
                            @include('partials.product-card', ['product' => $product])
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-center">
                    {{ $products->links() }}
                </div>
            @endif
        </div>

        <!-- Store Details & Policies Sidebar -->
        <div class="col-lg-4">
            <!-- About Store -->
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h5 class="fw-bold mb-3">About Store</h5>
                <p class="text-muted small" style="line-height: 1.8;">
                    {{ $seller->about_store ?: 'Verified local Indian merchant offering genuine products with fast doorstep fulfillment and personalized customer service.' }}
                </p>
                <hr>
                <div class="small text-muted">
                    <div class="mb-2"><strong>Address:</strong> {{ $seller->business_address }}, {{ $seller->city }} - {{ $seller->pincode }}</div>
                    <div class="mb-2"><strong>Delivery Radius:</strong> Up to {{ $seller->delivery_radius_km }} km around {{ $seller->city }}</div>
                    <div class="mb-2"><strong>GST Status:</strong> {{ $seller->gst_number ? 'GST Verified (' . $seller->gst_number . ')' : 'Registered Merchant' }}</div>
                    <div><strong>Return Policy:</strong> {{ $seller->return_policy }}</div>
                </div>
            </div>

            <!-- Customer Reviews for this Seller -->
            <div class="card border-0 shadow-sm p-4">
                <h5 class="fw-bold mb-3">Store Customer Reviews</h5>
                @if($reviews->isEmpty())
                    <p class="text-muted small">No customer reviews yet for this store.</p>
                @else
                    <div class="d-flex flex-column gap-3">
                        @foreach($reviews as $rev)
                            <div class="p-2 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="small">{{ $rev->user->name }}</strong>
                                    <span class="badge bg-warning text-dark">{{ $rev->rating }} ★</span>
                                </div>
                                <p class="text-muted small mb-0">{{ Str::limit($rev->comment, 90) }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
