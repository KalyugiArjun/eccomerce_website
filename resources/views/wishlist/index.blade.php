@extends('layouts.app')

@section('title', 'My Wishlist - ShopKart')

@section('content')
<div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-heart text-danger me-2"></i> {{ $wishlist->name }}</h2>
            <small class="text-muted">{{ $items->count() }} items saved &bull; Instant price drop alerts active</small>
        </div>
        <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#socialWishlistModal">
            <i class="bi bi-share me-1"></i> Social Wishlist Share Settings
        </button>
    </div>

    <!-- Social Wishlist Share Box (Section 30) -->
    @if($wishlist->is_public)
        <div class="alert alert-light border shadow-sm p-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <strong><i class="bi bi-link-45deg text-danger"></i> Shareable Wishlist Link:</strong>
                <span class="text-muted small ms-2 font-monospace">{{ route('wishlist.shared', $wishlist->share_token) }}</span>
            </div>
            <button class="btn btn-danger btn-sm" onclick="navigator.clipboard.writeText('{{ route('wishlist.shared', $wishlist->share_token) }}'); alert('Wishlist link copied to clipboard! Share it with friends or family on WhatsApp/social media.');">
                <i class="bi bi-clipboard me-1"></i> Copy Link
            </button>
        </div>
    @endif

    @if($items->isEmpty())
        <div class="card border-0 shadow-sm p-5 text-center">
            <i class="bi bi-heart text-muted display-4 mb-3"></i>
            <h4>Your Wishlist is Empty</h4>
            <p class="text-muted">Save your favorite products to track price drops and share with friends.</p>
            <div>
                <a href="{{ route('products.index') }}" class="btn btn-sk-primary">Explore Products</a>
            </div>
        </div>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3 mb-5">
            @foreach($items as $item)
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm p-3 d-flex flex-column">
                        <div class="position-relative mb-2">
                            <img src="{{ $item->product->thumbnail }}" class="card-img-top rounded" alt="{{ $item->product->name }}" style="height: 160px; object-fit: cover;">
                            
                            <!-- Price Drop Badge (Section 29) -->
                            @if($item->hasPriceDropped())
                                <span class="badge bg-success position-absolute top-0 start-0 m-2">
                                    <i class="bi bi-arrow-down"></i> Dropped ₹{{ number_format($item->price_drop_amount) }}
                                </span>
                            @endif

                            <form action="{{ route('wishlist.toggle', $item->product_id) }}" method="POST">
                                @csrf
                                <button type="submit" class="sk-wishlist-btn active" title="Remove">
                                    <i class="bi bi-heart-fill text-danger"></i>
                                </button>
                            </form>
                        </div>

                        <h6 class="fw-bold mb-1 fs-6">
                            <a href="{{ route('products.show', $item->product->slug) }}" class="text-dark text-decoration-none">
                                {{ Str::limit($item->product->name, 40) }}
                            </a>
                        </h6>
                        <small class="text-muted mb-2">{{ $item->product->seller->store_name }}</small>

                        <div class="d-flex align-items-baseline gap-2 mb-3 mt-auto">
                            <span class="fs-5 fw-bold text-dark">₹{{ number_format($item->product->effective_price) }}</span>
                            @if($item->product->discount_price && $item->product->discount_price < $item->product->price)
                                <span class="text-muted text-decoration-line-through small">₹{{ number_format($item->product->price) }}</span>
                            @endif
                        </div>

                        <form action="{{ route('wishlist.move-to-cart', $item->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sk-primary btn-sm w-100 fw-semibold">
                                <i class="bi bi-cart-plus me-1"></i> Move to Cart
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Social Wishlist Modal (Section 30) -->
<div class="modal fade" id="socialWishlistModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="bi bi-share text-danger me-2"></i> Social Wishlist Sharing</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('wishlist.update-settings') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-muted small">Create a shareable birthday, wedding, or festive wishlist link without leaking any private account information.</p>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Wishlist Title</label>
                        <input type="text" name="name" class="form-control form-control-sm" value="{{ $wishlist->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Event Occasion (Optional)</label>
                        <input type="text" name="event_name" class="form-control form-control-sm" placeholder="e.g. Birthday, Housewarming, Wedding" value="{{ $wishlist->event_name }}">
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_public" id="isPublicWishlist" value="1" {{ $wishlist->is_public ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold small" for="isPublicWishlist">Make this wishlist shareable via link</label>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sk-primary btn-sm px-4">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
